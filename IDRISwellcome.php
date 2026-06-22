<?php
ob_start();

// ปิดการแสดง Warning/Deprecated บนหน้าเว็บ
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('LineLogin.php');

// ดึงค่าคุกกี้สำรองหากเซสชันหลุด
if (isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
}
if (isset($_COOKIE['user_fullname']) && !empty($_COOKIE['user_fullname'])) {
    $_SESSION['fullname'] = $_COOKIE['user_fullname'];
}
if (isset($_COOKIE['line_profile_data']) && !empty($_COOKIE['line_profile_data'])) {
    $_SESSION['profile'] = json_decode($_COOKIE['line_profile_data'], true);
}

// ระบบดักจับความปลอดภัย
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: signin.php?auth=failed&v=" . time());
    exit();
}

$user_id = $_SESSION['id'];
$profile = isset($_SESSION['profile']) ? $_SESSION['profile'] : null;

// การเชื่อมต่อฐานข้อมูล
$db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: "localhost";
$db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: "root";
$db_pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "";
$db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: "register_idris";
$db_port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: "16494";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT useremail FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$db_email = isset($user_data['useremail']) ? $user_data['useremail'] : 'ไม่มีข้อมูลอีเมลในระบบ';
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome & LINE Flex Share</title>

    <!-- ✅ แก้ไข: Bootstrap 5 CSS URL ถูกต้อง -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- ✅ แก้ไข: LINE LIFF SDK URL ถูกต้อง -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

    <style>
        .line-flex-preview-box {
            background-color: #849cc4;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 250px;
        }
        .flex-bubble {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 300px;
            transition: all 0.3s ease;
        }
        .flex-hero-img {
            width: 100%;
            object-fit: cover;
            display: block;
            cursor: pointer;
        }
        .no-image-placeholder {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php require_once('nav.php'); ?>

    <main class="container my-4">
        <!-- ส่วนข้อมูลผู้ใช้ -->
        <div class="bg-light p-4 rounded mb-4">
            <?php if (isset($profile['pictureUrl']) && !empty($profile['pictureUrl'])): ?>
                <img src="<?php echo htmlspecialchars($profile['pictureUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="Profile Picture" class="img-thumbnail mb-3" width="150">
            <?php else: ?>
                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto"
                     style="width: 150px; height: 150px;">No Image</div>
            <?php endif; ?>

            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname'], ENT_QUOTES, 'UTF-8'); ?> to IDRIS</h1>
            <p class="lead text-primary fw-bold">Your System Email: <?php echo htmlspecialchars($db_email, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted small">LINE Name: <?php echo htmlspecialchars($profile['displayName'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted small">LINE User ID: <?php echo htmlspecialchars($profile['userId'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <!-- LINE Flex Message Creator & Share -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white fw-bold">
                LINE Flex Message Creator &amp; Share
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- ฝั่งซ้าย: กล่องกรอกข้อมูล -->
                    <div class="col-md-5 mb-3">
                        <div class="mb-3">
                            <label for="imageUrl" class="form-label fw-bold">1. ลิงก์รูปภาพ (Image URL)</label>
                            <input type="url" class="form-control" id="imageUrl"
                                   placeholder="https://example.com/image.jpg"
                                   value="https://unsplash.com/photos/example.jpg">
                        </div>

                        <div class="mb-3">
                            <label for="targetUrl" class="form-label fw-bold">2. Target Link (ลิงก์ปลายทางเมื่อคลิกรูป)</label>
                            <input type="url" class="form-control" id="targetUrl"
                                   placeholder="https://example.com"
                                   value="https://line.biz">
                        </div>

                        <div class="mb-3">
                            <label for="aspectRatio" class="form-label fw-bold">3. Aspect Ratio (เช่น 1:1, 16:9, 30:25)</label>
                            <input type="text" class="form-control" id="aspectRatio"
                                   placeholder="30:25" value="30:25">
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                            <button type="button" onclick="generatePreview()" class="btn btn-primary me-md-2">Preview Code</button>
                            <button type="button" onclick="clearFields()" class="btn btn-secondary">Clear ค่า</button>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: Live Preview และ JSON Code -->
                    <div class="col-md-7 mb-3">
                        <label class="form-label fw-bold text-primary">Live Preview (ภาพจำลองการแสดงผลบน LINE)</label>
                        <div class="line-flex-preview-box mb-3">
                            <div class="flex-bubble" id="flexBubbleContainer">
                                <!-- ✅ แก้ไข: แสดง placeholder แทนการใส่ SVG ผิด -->
                                <div class="no-image-placeholder" id="noImagePlaceholder">ยังไม่มีรูปภาพ</div>
                                <a id="previewAnchor" href="#" target="_blank" style="display:none;">
                                    <img id="imagePreview" src="" class="flex-hero-img" alt="Flex Image Preview">
                                </a>
                            </div>
                        </div>

                        <label for="FlexCode" class="form-label fw-bold text-success">4. กล่อง Preview (โค้ด JSON ที่พร้อมส่ง)</label>
                        <textarea class="form-control bg-dark text-warning font-monospace"
                                  id="FlexCode" rows="6" readonly
                                  placeholder="กดปุ่ม Preview Code เพื่อสร้างข้อความ..."></textarea>

                        <div class="d-grid gap-2 mt-3">
                            <button type="button" onclick="shareFlex()" class="btn btn-success btn-lg w-100">ส่งและแชร์ไปที่ LINE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ✅ แก้ไข: ใช้ bootstrap.bundle.min.js ไฟล์เดียว (รวม Popper.js แล้ว) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
            crossorigin="anonymous"></script>

<script>
    // ==========================
    // LIFF Configuration
    // ==========================
    const myLiffId = "2010383431-NwcATXJE";
    let liffReady = false;
    let dynamicFlexJson = null;

    document.addEventListener("DOMContentLoaded", function () {

        // ✅ Fix 1: เรียก generatePreview() ทันทีที่โหลดหน้า ไม่รอ LIFF
        const imageUrl = document.getElementById("imageUrl").value.trim();
        const targetUrl = document.getElementById("targetUrl").value.trim();
        if (imageUrl && targetUrl) {
            generatePreview();
        }

        // ✅ Fix 2: ป้องกัน LIFF Login Loop — เช็กก่อนว่าเคย redirect มาแล้วหรือยัง
        const urlParams = new URLSearchParams(window.location.search);
        const alreadyRedirected = sessionStorage.getItem('liff_login_attempted');

        const checkLiffInterval = setInterval(() => {
            if (typeof liff !== "undefined") {
                clearInterval(checkLiffInterval);

                liff.init({ liffId: myLiffId })
                    .then(() => {
                        liffReady = true;
                        console.log("LIFF initialized");

                        if (!liff.isLoggedIn()) {
                            // ✅ Fix 3: เช็กว่าเคย redirect แล้วหรือยัง ป้องกัน Loop
                            if (!alreadyRedirected) {
                                sessionStorage.setItem('liff_login_attempted', '1');
                                liff.login({ redirectUri: window.location.href });
                            } else {
                                // ลอง redirect ไปแล้วแต่ยังไม่ login ได้ → แสดงปุ่มเปิดผ่าน LIFF URL
                                console.warn("LIFF login failed after redirect");
                                showLiffLoginBanner();
                            }
                        } else {
                            // Login สำเร็จ → ล้าง flag
                            sessionStorage.removeItem('liff_login_attempted');
                            console.log("LIFF logged in successfully");
                        }
                    })
                    .catch(err => {
                        console.error("LIFF init failed:", err);
                        showLiffLoginBanner();
                    });
            }
        }, 300);

        setTimeout(() => clearInterval(checkLiffInterval), 10000);
    });

    // ==========================
    // Generate Preview (ทำงานอิสระจาก LIFF)
    // ==========================
    function generatePreview() {
        const imageUrl = document.getElementById("imageUrl").value.trim();
        const targetUrl = document.getElementById("targetUrl").value.trim();
        const ratio = document.getElementById("aspectRatio").value.trim() || "30:25";

        if (!imageUrl || !targetUrl) {
            alert("กรุณากรอกลิงก์รูปภาพและ Target Link");
            return;
        }

        const imgElement = document.getElementById("imagePreview");
        const anchorElement = document.getElementById("previewAnchor");
        const placeholder = document.getElementById("noImagePlaceholder");

        imgElement.src = imageUrl;
        anchorElement.href = targetUrl;
        placeholder.style.display = "none";
        anchorElement.style.display = "block";

        const ratioParts = ratio.split(":");
        if (ratioParts.length === 2) {
            const w = parseFloat(ratioParts[0]);
            const h = parseFloat(ratioParts[1]);
            imgElement.style.aspectRatio = (!isNaN(w) && !isNaN(h) && w > 0 && h > 0)
                ? `${w} / ${h}` : "30 / 25";
        } else {
            imgElement.style.aspectRatio = "30 / 25";
        }

        dynamicFlexJson = {
            type: "flex",
            altText: "sent a photo",
            contents: {
                type: "bubble",
                hero: {
                    type: "image",
                    url: imageUrl,
                    size: "full",
                    aspectRatio: ratio,
                    aspectMode: "cover",
                    action: { type: "uri", uri: targetUrl }
                }
            }
        };

        document.getElementById("FlexCode").value = JSON.stringify(dynamicFlexJson, null, 2);
    }

    // ==========================
    // Clear Fields
    // ==========================
    function clearFields() {
        document.getElementById("imageUrl").value = "";
        document.getElementById("targetUrl").value = "";
        document.getElementById("aspectRatio").value = "30:25";
        document.getElementById("FlexCode").value = "";
        document.getElementById("previewAnchor").style.display = "none";
        document.getElementById("imagePreview").src = "";
        document.getElementById("noImagePlaceholder").style.display = "flex";
        document.getElementById("previewAnchor").href = "#";
        dynamicFlexJson = null;
    }

    // ==========================
    // Share Flex Message
    // ==========================
    async function shareFlex() {
        generatePreview();

        if (!dynamicFlexJson) {
            alert("กรุณาสร้างข้อความพรีวิวก่อนกดแชร์ครับ");
            return;
        }

        if (!liffReady) {
            alert("ระบบ LINE กำลังโหลด กรุณารอสักครู่แล้วลองใหม่");
            return;
        }

        if (!liff.isLoggedIn()) {
            showLiffLoginBanner();
            return;
        }

        const shareBtn = document.querySelector("button[onclick='shareFlex()']");
        const originalText = shareBtn.innerHTML;
        shareBtn.innerHTML = "กำลังเปิด Share Target Picker...";
        shareBtn.disabled = true;

        try {
            const result = await liff.shareTargetPicker([dynamicFlexJson]);
            if (result && result.status === 'success') {
                alert("แชร์ Flex Message สำเร็จเรียบร้อยแล้ว!");
            }
        } catch (error) {
            console.error(error);
            // ✅ Fix 4: ถ้า shareTargetPicker ล้มเหลวบน PC → แสดง LIFF URL แทน
            if (error.code === 'FORBIDDEN' || error.message.includes('not supported')) {
                showLiffLoginBanner();
            } else {
                alert("เกิดข้อผิดพลาด: " + error.message);
            }
        } finally {
            shareBtn.innerHTML = originalText;
            shareBtn.disabled = false;
        }
    }

    // ==========================
    // ✅ Fix 5: แสดง Banner แนะนำเปิดผ่าน LIFF URL สำหรับ PC
    // ==========================
    function showLiffLoginBanner() {
        const liffUrl = "https://liff.line.me/" + myLiffId;

        // ลบ banner เก่าก่อน
        const old = document.getElementById('liffBanner');
        if (old) old.remove();

        const banner = document.createElement('div');
        banner.id = 'liffBanner';
        banner.className = 'alert alert-warning alert-dismissible fade show mt-3';
        banner.innerHTML = `
            <strong>แชร์ผ่าน PC ต้องเข้าผ่านลิงก์ LIFF</strong><br>
            กรุณาเปิดลิงก์นี้ในเบราว์เซอร์เพื่อยืนยันตัวตนกับ LINE แล้วแชร์ได้ทันที:<br>
            <a href="${liffUrl}" target="_blank" class="btn btn-warning btn-sm mt-2">
                เปิดหน้านี้ผ่าน LINE (LIFF)
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.querySelector('.card-body').prepend(banner);
    }
</script>
</body>
</html>