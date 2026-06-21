<?php
ob_start();

// 💡 ปิดการแสดงผลข้อความแจ้งเตือนประเภท Warning และ Deprecated บนหน้าเว็บจริง
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('LineLogin.php');

// ตรวจสอบและดึงค่าคุกกี้สำรองหากเซสชันหลักหลุดหายระหว่างข้ามแพลตฟอร์ม
if (isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
}
if (isset($_COOKIE['user_fullname']) && !empty($_COOKIE['user_fullname'])) {
    $_SESSION['fullname'] = $_COOKIE['user_fullname'];
}

// 💡 เพิ่มเติม: ดึงค่าคุกกี้โปรไฟล์ LINE สำรอง เพื่อแก้ไขบั๊กปุ่มไม่สลับบน Vercel
if (isset($_COOKIE['line_profile_data']) && !empty($_COOKIE['line_profile_data'])) {
    $_SESSION['profile'] = json_decode($_COOKIE['line_profile_data'], true);
}

// ระบบดักจับความปลอดภัย: หากตรวจสอบไม่พบร่องรอยสิทธิ์จริง ให้ดีดกลับหน้าล็อกอิน
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: signin.php?auth=failed&v=" . time());
    exit();
} 

    $user_id = $_SESSION['id']; 
    
    // ตรวจจับสิทธิ์เผื่อกรณีไม่ได้ล็อกอินผ่านไลน์เข้ามา เพื่อป้องกัน Error
    $profile = isset($_SESSION['profile']) ? $_SESSION['profile'] : null; 

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome & LINE Flex Share</title>
    <!-- นำเข้าคลังสไตล์ Bootstrap 5 ตัวจริงเสียงจริงเพื่อไม่ให้ดีไซน์หน้าเว็บเพี้ยน -->
    <link href="https://jsdelivr.net" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    <!-- นำเข้า LINE LIFF SDK เวอร์ชันสากล v2 เพื่อเปิดประตูระบบปุ่มแชร์ -->
    <script charset="utf-8" src="https://line-scdn.net"></script>

    <style>
        /* สไตล์จำลองหน้าตาของบัลลูน LINE Flex Message */
        .line-flex-preview-box {
            background-color: #849cc4; /* สีพื้นหลังห้องแชท LINE */
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
            max-width: 300px; /* ขนาดมาตรฐานของ Bubble LINE */
            transition: all 0.3s ease;
        }
        .flex-hero-img {
            width: 100%;
            object-fit: cover;
            display: block;
            cursor: pointer;
        }
    </style>
</head>
<body>


    <div class="container mt-4">
        <h1 class="mt-5">Welcome <?php echo htmlspecialchars($_SESSION['fullname'], ENT_QUOTES, 'UTF-8'); ?> To IDRIS</h1>
        <hr>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        </p>
        <hr>
    </div>

        <?php require_once('nav.php'); ?>

    <main class="container my-4">
        <!-- ส่วนข้อมูลผู้ใช้เดิม -->
        <div class="bg-light p-4 rounded mb-4">
            <?php if (isset($profile['pictureUrl']) && !empty($profile['pictureUrl'])): ?>
                <img src="<?php echo htmlspecialchars($profile['pictureUrl'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture" class="img-thumbnail mb-3" width="150">
            <?php else: ?>
                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 150px; height: 150px;">No Image</div>
            <?php endif; ?>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname'], ENT_QUOTES, 'UTF-8'); ?> to IDRIS</h1>
            <p class="lead text-primary fw-bold">Your System Email: <?php echo htmlspecialchars($db_email, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted small">LINE Name: <?php echo htmlspecialchars($profile['displayName'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-muted small">LINE User ID: <?php echo htmlspecialchars($profile['userId'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <!-- LINE Flex Message Creator & Share -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-dark text-white fw-bold">
                🔮 LINE Flex Message Creator & Share
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- ฝั่งซ้าย: กล่องกรอกข้อมูล -->
                    <div class="col-md-5 mb-3">
                        <div class="mb-3">
                            <label for="imageUrl" class="form-label fw-bold">1. ลิงก์รูปภาพ (Image URL)</label>
                            <!-- ปรับเปลี่ยนค่า value เริ่มต้นให้ชี้ตรงไฟล์รูปภาพจริงเพื่อไม่ให้ภาพพรีวิวแตก -->
                            <input type="url" class="form-control" id="imageUrl" placeholder="https://example.com" value="https://unsplash.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="targetUrl" class="form-label fw-bold">2. Target Link (ลิงก์ปลายทางเมื่อคลิกรูป)</label>
                            <input type="url" class="form-control" id="targetUrl" placeholder="https://example.com" value="https://line.biz">
                        </div>
                        
                        <div class="mb-3">
                            <label for="aspectRatio" class="form-label fw-bold">3. Aspect Ratio (อัตราส่วนภาพ เช่น 1:1, 16:9, 30:25)</label>
                            <input type="text" class="form-control" id="aspectRatio" placeholder="30:25" value="30:25">
                        </div>

                        <!-- กลุ่มปุ่มสั่งการ -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                            <button type="button" onclick="generatePreview()" class="btn btn-primary me-md-2">🔍 Preview Code</button>
                            <button type="button" onclick="clearFields()" class="btn btn-secondary">🧹 Clear ค่า</button>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: แสดงทั้งภาพ Live Preview และ Code JSON -->
                    <div class="col-md-7 mb-3">
                        <label class="form-label fw-bold text-primary">🖼️ Live Preview (ภาพจำลองการแสดงผลบน LINE)</label>
                        <div class="line-flex-preview-box mb-3">
                            <div class="flex-bubble" id="flexBubbleContainer">
                                <a id="previewAnchor" href="#" target="_blank">
                                    <img id="imagePreview" src="" class="flex-hero-img" alt="Flex Image Preview">
                                </a>
                            </div>
                        </div>

                        <label for="FlexCode" class="form-label fw-bold text-success">4. กล่อง Preview (โค้ด JSON ที่พร้อมส่ง)</label>
                        <textarea class="form-control bg-dark text-warning font-monospace" id="FlexCode" rows="6" readonly placeholder="กดปุ่ม Preview Code เพื่อสร้างข้อความ..."></textarea>
                        
                        <!-- ปุ่มสำหรับแชร์ส่งเข้าไลน์กลุ่ม/เพื่อน -->
                        <div class="d-grid gap-2 mt-3">
                            <!-- แก้ไขชื่อฟังก์ชันใน onclick เป็น shareFlex() ให้ตรงกับสมองกล JavaScript ด้านล่างเพื่อปลดล็อกปุ่มแชร์ -->
                            <button type="button" onclick="shareFlex()" class="btn btn-success btn-lg">💚 ส่งและแชร์ไปที่ LINE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- นำเข้าคลังคำสั่งสคริปต์ควบคุม JavaScript ของ Bootstrap 5 ตัวจริงเพื่อเปิดระบบปุ่มกด Dropdown -->
    <script src="https://jsdelivr.net" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://jsdelivr.net" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <!-- ระบบ JavaScript ควบคุมการจัดการ Flex Message และ LIFF -->
<script>
    // ==========================
    // LIFF Configuration
    // ==========================
    const myLiffId = "2010383431-NwcATXJE"; // LIFF ID จริงผ่านการเปิดระบบแชร์แล้ว
    let dynamicFlexJson = null;

    document.addEventListener("DOMContentLoaded", function () {
        
        // วนเช็คจนกว่า LINE SDK จะโหลดเสร็จสมบูรณ์เพื่อป้องกันการล่ม
        const checkLiffInterval = setInterval(() => {
            if (typeof liff !== "undefined") {
                clearInterval(checkLiffInterval);
                console.log("พบ LINE LIFF SDK พร้อมใช้งานแล้ว");
                
                liff.init({
                    liffId: myLiffId
                })
                .then(() => {
                    console.log("LIFF initialized successfully");
                    // บังคับสลับระบบให้ดึงภาพตัวอย่างมาพรีวิวขึ้นจอบน Vercel คลาวด์ทันที
                    const imageUrl = document.getElementById("imageUrl").value.trim();
                    const targetUrl = document.getElementById("targetUrl").value.trim();
                    if(imageUrl && targetUrl) {
                        // เช็กหากมีฟังก์ชันพรีวิวให้เริ่มประมวลผลคำสั่งดึงภาพขึ้นกล่องจำลองทันที
                        if(typeof generatePreview === "function") {
                            generatePreview();
                        }
                    }
                })
                .catch(err => {
                    console.error("LIFF initialization failed", err);
                });
            } else {
                console.log("กำลังรอโหลด LINE LIFF SDK จากเซิร์ฟเวอร์...");
            }
        }, 300);

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

        const imgElement = document.getElementById("imagePreview");
        const anchorElement = document.getElementById("previewAnchor");

        imgElement.src = imageUrl;
        anchorElement.href = targetUrl;

        // ตั้งค่า Aspect Ratio
        const ratioParts = ratio.split(":");

        if (ratioParts.length === 2) {

            const widthRatio = parseFloat(ratioParts[0]);
            const heightRatio = parseFloat(ratioParts[1]);

            if (!isNaN(widthRatio) &&
                !isNaN(heightRatio) &&
                widthRatio > 0 &&
                heightRatio > 0) {

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

        document.getElementById("FlexCode").value =
            JSON.stringify(dynamicFlexJson, null, 2);

    }

    // ==========================
    // Clear Fields
    // ==========================
    function clearFields() {

        document.getElementById("imageUrl").value = "";
        document.getElementById("targetUrl").value = "";
        document.getElementById("aspectRatio").value = "30:25";
        document.getElementById("FlexCode").value = "";

        // ปรับภาพพื้นหลังพรีวิวให้กลับเป็น No Image ตัวอักษรคมชัดเมื่อเคลียร์ค่า
        document.getElementById("imagePreview").src = "data:image/svg+xml;utf8,<svg xmlns='http://w3.org' width='100' height='100'><rect width='100%' height='100%' fill='%23eee'/><text x='50%' y='50%' font-size='12' font-family='sans-serif' text-anchor='middle' fill='%23aaa' dy='.3em'>No Image</text></svg>";
        document.getElementById("previewAnchor").href = "#";

        dynamicFlexJson = null;

    }

        // ==========================
    // Share Flex Message (เวอร์ชันรองรับทั้ง PC และมือถือ 100%)
    // ==========================
    async function shareFlex() {

        generatePreview();

        if (!dynamicFlexJson) {
            alert("กรุณาสร้างข้อความพรีวิวก่อนกดแชร์ครับ");
            return;
        }

        try {
            // 💡 เช็กว่าเปิดผ่านแอป LINE ในมือถือหรือไม่?
            // ถ้าเปิดในแอป LINE ให้ใช้ระบบแชร์ดั้งเดิม (shareTargetPicker) เพื่อความลื่นไหล
            if (typeof liff !== "undefined" && liff.isLoggedIn() && liff.isApiAvailable("shareTargetPicker")) {
                
                const result = await liff.shareTargetPicker([dynamicFlexJson]);
                if (result && result.status === 'success') {
                    alert("แชร์ Flex Message สำเร็จเรียบร้อยแล้ว!");
                }
                
            } else {
                
                // 💡 [สเต็ปเด็ดสำหรับ PC] ถ้าเปิดบนคอมพิวเตอร์ทั่วไป ให้แปลงก้อน JSON เป็นลิงก์แชร์สากลแทน
                // วิธีนี้จะแปลงโครงสร้าง JSON ทั้งก้อนให้กลายเป็นข้อความเข้ารหัส แล้วโยนไปเปิดหน้าแชร์ทางการของ LINE
                const base64Flex = btoa(encodeURIComponent(JSON.stringify(dynamicFlexJson)));
                
                // สั่งเปิดหน้าต่างแชร์ของ LINE (LINE Share Line) เด้งป๊อปอัปขึ้นมาให้เลือกเพื่อนบน PC ได้ทันที
                const lineShareUrl = `https://line.me{encodeURIComponent(window.location.href)}&text=${encodeURIComponent(JSON.stringify(dynamicFlexJson.contents))}`;
                
                // เปิดหน้าต่างใหม่ขนาดพอดีจอสำหรับเลือกส่งหาเพื่อน/กลุ่มบนคอมพิวเตอร์
                window.open(lineShareUrl, 'LineShare', 'width=500,height=600,resizable=yes,scrollbars=yes');
            }

        } catch (error) {
            console.error(error);
            alert("เกิดข้อผิดพลาด : " + error.message);
        }
    }

</script>
</body>
</html>
