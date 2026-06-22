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
            const checkLiffInterval = setInterval(() => {
                if (typeof liff !== "undefined") {
                    clearInterval(checkLiffInterval);
                    console.log("พบ LINE LIFF SDK พร้อมใช้งานแล้ว");

                    liff.init({ liffId: myLiffId })
                        .then(() => {
                            liffReady = true;
                            console.log("LIFF initialized successfully");

                            const imageUrl = document.getElementById("imageUrl").value.trim();
                            const targetUrl = document.getElementById("targetUrl").value.trim();
                            if (imageUrl && targetUrl) {
                                generatePreview();
                            }
                        })
                        .catch(err => {
                            console.error("LIFF initialization failed", err);
                        });
                } else {
                    console.log("กำลังรอโหลด LINE LIFF SDK...");
                }
            }, 300);

            // หยุดรอหลัง 10 วินาที
            setTimeout(() => clearInterval(checkLiffInterval), 10000);
        });

        // ==========================
        // Generate Preview
        // ==========================
        function generatePreview() {
            const imageUrl = document.getElementById("imageUrl").value.trim();
            const targetUrl = document.getElementById("targetUrl").value.trim();
            const ratio = document.getElementById("aspectRatio").value.trim() || "30:25";

            if (!imageUrl || !targetUrl) {
                alert("กรุณากรอกลิงก์รูปภาพและ Target Link");
                return;
            }

            // แสดงรูปภาพ Preview
            const imgElement = document.getElementById("imagePreview");
            const anchorElement = document.getElementById("previewAnchor");
            const placeholder = document.getElementById("noImagePlaceholder");

            imgElement.src = imageUrl;
            anchorElement.href = targetUrl;

            // ✅ แก้ไข: ซ่อน placeholder แล้วแสดงรูป
            placeholder.style.display = "none";
            anchorElement.style.display = "block";

            // ตั้งค่า Aspect Ratio
            const ratioParts = ratio.split(":");
            if (ratioParts.length === 2) {
                const widthRatio = parseFloat(ratioParts[0]);
                const heightRatio = parseFloat(ratioParts[1]);
                if (!isNaN(widthRatio) && !isNaN(heightRatio) && widthRatio > 0 && heightRatio > 0) {
                    imgElement.style.aspectRatio = `${widthRatio} / ${heightRatio}`;
                } else {
                    imgElement.style.aspectRatio = "30 / 25";
                }
            } else {
                imgElement.style.aspectRatio = "30 / 25";
            }

            // สร้าง Flex Message JSON
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
                        action: {
                            type: "uri",
                            uri: targetUrl
                        }
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

            // ✅ แก้ไข: ซ่อนรูปและแสดง placeholder กลับมา
            document.getElementById("previewAnchor").style.display = "none";
            document.getElementById("imagePreview").src = "";
            document.getElementById("noImagePlaceholder").style.display = "flex";
            document.getElementById("previewAnchor").href = "#";

            dynamicFlexJson = null;
        }

async function shareFlex() {
    generatePreview();

    if (!dynamicFlexJson) {
        alert("กรุณาสร้างข้อความพรีวิวก่อนกดแชร์ครับ");
        return;
    }

    try {
        // ✅ กรณีที่ 1: เปิดในแอป LINE บนมือถือ → ส่ง Flex Message ได้จริง
        if (liffReady && liff.isLoggedIn() && liff.isApiAvailable("shareTargetPicker")) {
            const result = await liff.shareTargetPicker([dynamicFlexJson]);
            if (result && result.status === 'success') {
                alert("แชร์ Flex Message สำเร็จเรียบร้อยแล้ว!");
            }

        // ✅ กรณีที่ 2: เปิดจาก Mobile Browser (ไม่ใช่แอป LINE) → ดึงเปิดในแอป LINE
        } else if (/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
            const liffUrl = "https://liff.line.me/" + myLiffId;
            if (confirm("กรุณาเปิดหน้านี้ในแอป LINE เพื่อส่ง Flex Message\nกด OK เพื่อเปิดแอป LINE")) {
                window.location.href = liffUrl;
            }

        // ✅ กรณีที่ 3: PC Browser → แสดง QR Code ให้แสกนเปิดใน LINE มือถือ
        } else {
            showQRModal();
        }

    } catch (error) {
        console.error(error);
        alert("เกิดข้อผิดพลาด: " + error.message);
    }
}

// ==========================
// แสดง QR Code สำหรับ PC
// ==========================
function showQRModal() {
    const liffUrl = "https://liff.line.me/" + myLiffId;
    const qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" 
                     + encodeURIComponent(liffUrl);

    // สร้าง Modal แบบ Bootstrap
    const modalHtml = `
        <div class="modal fade" id="qrModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
              <div class="modal-header">
                <h5 class="modal-title">แสกน QR เพื่อส่ง Flex Message ผ่านแอป LINE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="text-muted small">Flex Message ส่งได้เฉพาะในแอป LINE บนมือถือเท่านั้น</p>
                <img src="${qrApiUrl}" alt="QR Code" width="200" height="200" class="mb-3">
                <p class="small">หรือคัดลอกลิงก์นี้ไปเปิดในมือถือ:<br>
                  <a href="${liffUrl}" target="_blank" class="text-break">${liffUrl}</a>
                </p>
              </div>
            </div>
          </div>
        </div>`;

    // ลบ Modal เก่าก่อนถ้ามี
    const oldModal = document.getElementById('qrModal');
    if (oldModal) oldModal.remove();

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
}
    </script>
</body>
</html>