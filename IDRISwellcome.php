<?php
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('LineLogin.php');

if (isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
}
if (isset($_COOKIE['user_fullname']) && !empty($_COOKIE['user_fullname'])) {
    $_SESSION['fullname'] = $_COOKIE['user_fullname'];
}
if (isset($_COOKIE['line_profile_data']) && !empty($_COOKIE['line_profile_data'])) {
    $_SESSION['profile'] = json_decode($_COOKIE['line_profile_data'], true);
}

if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: signin.php?auth=failed&v=" . time());
    exit();
}

$user_id = $_SESSION['id'];
$profile = isset($_SESSION['profile']) ? $_SESSION['profile'] : null;

$db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: "localhost";
$db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: "root";
$db_pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: "";
$db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: "defaultdb";
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
    <title>PROJECT IDRIS - LINE Flex Message Creator & Share</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <style>
        :root {
            --bg-dark:       #0b1622;
            --bg-card:       #112035;
            --bg-card-inner: #0d1b2e;
            --border-color:  #1e3a5f;
            --accent-green:  #00c853;
            --accent-blue:   #1a73e8;
            --accent-red:    #e53935;
            --text-main:     #e0eaf8;
            --text-muted:    #7a9abf;
            --chat-bg:       #1a2f4a;
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .idris-navbar {
            background: linear-gradient(135deg, #0a1628 0%, #0f2340 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .idris-navbar .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .idris-navbar .shield-icon {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, #1a3a6e, #0d2550);
            border: 2px solid #2a5298;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .idris-navbar .brand-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .idris-navbar .brand-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            letter-spacing: 0.3px;
        }
        .idris-navbar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .idris-navbar .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid var(--accent-green);
            object-fit: cover;
        }
        .idris-navbar .user-avatar-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid var(--accent-green);
            background: #1a3a6e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
        }
        .idris-navbar .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #fff;
        }
        .idris-navbar .user-status {
            font-size: 11px;
            color: var(--accent-green);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .idris-navbar .user-status::before {
            content: '';
            width: 7px;
            height: 7px;
            background: var(--accent-green);
            border-radius: 50%;
            display: inline-block;
        }
        .btn-logout {
            background: transparent;
            border: 1px solid var(--text-muted);
            color: var(--text-main);
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            border-color: var(--accent-red);
            color: var(--accent-red);
        }

        /* ===== WELCOME BANNER ===== */
        .welcome-banner {
            background: linear-gradient(135deg, #1a6b3a 0%, #0d8c3e 60%, #00c853 100%);
            border: 1px solid #00c853;
            border-radius: 10px;
            padding: 18px 24px;
            margin: 20px 20px 0;
        }
        .welcome-banner h2 {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 6px;
        }
        .welcome-banner p {
            font-size: 13px;
            color: rgba(255,255,255,0.9);
            margin: 2px 0;
        }

        /* ===== MAIN LAYOUT ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        /* ===== PANEL CARDS ===== */
        .panel-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
        }
        .panel-header {
            background: #0d2040;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: 1px;
        }
        .panel-body {
            padding: 20px;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-label-idris {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
        }
        .input-idris {
            width: 100%;
            background: var(--bg-card-inner);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-main);
            padding: 10px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 16px;
        }
        .input-idris:focus {
            border-color: var(--accent-blue);
        }
        select.input-idris {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%237a9abf' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
            cursor: pointer;
        }

        .btn-idris-primary {
            background: var(--accent-blue);
            border: none;
            color: #fff;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-idris-primary:hover { opacity: 0.85; }

        .btn-idris-danger {
            background: var(--accent-red);
            border: none;
            color: #fff;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-idris-danger:hover { opacity: 0.85; }

        /* ===== LIVE PREVIEW ===== */
        .live-preview-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        .chat-mockup {
            background: var(--chat-bg);
            border-radius: 8px;
            padding: 14px;
            min-height: 220px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
        }
        .chat-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #1a3a6e;
            flex-shrink: 0;
            object-fit: cover;
        }
        .chat-avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #1a3a6e;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
        }
        .chat-bubble-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex: 1;
        }
        .flex-bubble-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            max-width: 240px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .flex-hero-img {
            width: 100%;
            display: block;
            object-fit: cover;
        }
        .no-image-placeholder {
            width: 100%;
            height: 140px;
            background: #2a3f5a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 13px;
        }
        .chat-timestamp {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 4px;
            padding-left: 4px;
        }

        /* ===== JSON CODE BOX ===== */
        .json-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .json-box {
            background: #0a1520;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: #ffd54f;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 12px;
            width: 100%;
            height: 160px;
            resize: none;
            outline: none;
        }

        /* ===== SHARE BUTTON ===== */
        .btn-share-line {
            width: 100%;
            background: linear-gradient(135deg, #00c853, #00e676);
            border: none;
            color: #fff;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
            transition: opacity 0.2s;
            letter-spacing: 0.5px;
        }
        .btn-share-line:hover { opacity: 0.88; }
        .btn-share-line:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ===== LIFF BANNER ===== */
        .liff-banner {
            background: #1a3a0d;
            border: 1px solid var(--accent-green);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 12px;
            display: none;
        }
        .liff-banner a {
            color: var(--accent-green);
        }

        /* scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="idris-navbar">
    <div class="brand-logo">
        <div class="shield-icon">&#x1F6E1;</div>
        <div>
            <div class="brand-title">PROJECT IDRIS &nbsp;-&nbsp; LINE Flex Message Creator &amp; Share</div>
            <div class="brand-subtitle">Intelligent Digital Response &amp; Investigation System</div>
        </div>
    </div>
    <div class="user-info">
        <?php if (isset($profile['pictureUrl']) && !empty($profile['pictureUrl'])): ?>
            <img src="<?php echo htmlspecialchars($profile['pictureUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                 alt="Profile" class="user-avatar">
        <?php else: ?>
            <div class="user-avatar-placeholder">&#128100;</div>
        <?php endif; ?>
        <div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['fullname'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="user-status">Agent Online</div>
        </div>
        <button class="btn-logout" onclick="handleLogout()">&#x2192; Logout</button>
    </div>
</nav>

<!-- ===== WELCOME BANNER ===== -->
<div class="welcome-banner">
    <h2>&#x2705; Welcome, <?php echo htmlspecialchars($_SESSION['fullname'], ENT_QUOTES, 'UTF-8'); ?> to IDRIS</h2>
    <p>Authenticated System Email: <?php echo htmlspecialchars($db_email, ENT_QUOTES, 'UTF-8'); ?></p>
    <p>LINE Name: <?php echo htmlspecialchars($profile['displayName'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> &nbsp;|&nbsp; UID: <?php echo htmlspecialchars($profile['userId'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<!-- ===== MAIN GRID ===== -->
<div class="main-grid">

    <!-- LEFT: Configuration -->
    <div class="panel-card">
        <div class="panel-header">1. CONFIGURATION COMMAND INTERFACE</div>
        <div class="panel-body">
            <div id="liffBanner" class="liff-banner"></div>

            <label class="form-label-idris">Image URL</label>
            <input type="url" class="input-idris" id="imageUrl" placeholder="https://example.com/image.jpg">

            <label class="form-label-idris">Target Link</label>
            <input type="url" class="input-idris" id="targetUrl" placeholder="https://example.com">

            <label class="form-label-idris">Aspect Ratio</label>
            <select class="input-idris" id="aspectRatio">
                <option value="30:25">30:25</option>
                <option value="1:1">1:1</option>
                <option value="4:3">4:3</option>
                <option value="16:9">16:9</option>
                <option value="20:13">20:13</option>
                <option value="2:1">2:1</option>
            </select>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <!-- ใส่สไตล์ flex: 1; เพิ่มเข้าไปในปุ่มทุกตัวเพื่อบีบให้ขนาดเท่ากันทั้งหมด -->
                <button class="btn-idris-primary" style="flex: 1;" onclick="generatePreview()">Preview Code</button>
                <button class="btn-idris-danger" style="flex: 1;" onclick="clearFields()">Clear ค่า</button>
                
                <!-- ปุ่มไลน์เพิ่มจัดระเบียบไอคอนให้อยู่ตรงกลางร่วมด้วย -->
                <button class="btn-share-line" id="shareBtnEl" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;" onclick="shareFlex()">
                    <span>✨</span> ส่งและแชร์ไปที่ LINE
                </button>
            </div>
        </div>
    </div>

    <!-- RIGHT: Live Monitor -->
    <div class="panel-card">
        <div class="panel-header">2. LIVE MONITOR &amp; TRANSMISSION PREVIEW</div>
        <div class="panel-body">
            <div class="live-preview-label">Live Preview</div>
            <div class="chat-mockup">
                <?php if (isset($profile['pictureUrl']) && !empty($profile['pictureUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($profile['pictureUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="avatar" class="chat-avatar">
                <?php else: ?>
                    <div class="chat-avatar-placeholder">&#128100;</div>
                <?php endif; ?>
                <div class="chat-bubble-wrap">
                    <div class="flex-bubble-card">
                        <div class="no-image-placeholder" id="noImagePlaceholder">ยังไม่มีรูปภาพ</div>
                        <a id="previewAnchor" href="#" target="_blank" style="display:none;">
                            <img id="imagePreview" src="" class="flex-hero-img" alt="Preview">
                        </a>
                    </div>
                    <div class="chat-timestamp" id="chatTimestamp"></div>
                </div>
            </div>

            <div class="json-label">4. กล่อง Preview (โค้ด JSON ที่พร้อมส่ง)</div>
            <textarea class="json-box" id="FlexCode" readonly
                      placeholder="กดปุ่ม Preview Code เพื่อสร้างข้อความ..."></textarea>


        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const myLiffId = "2010383431-NwcATXJE";
    let liffReady = false;
    let dynamicFlexJson = null;

    // แสดงเวลาปัจจุบัน
    function updateTimestamp() {
        const now = new Date();
        const h = now.getHours().toString().padStart(2,'0');
        const m = now.getMinutes().toString().padStart(2,'0');
        document.getElementById('chatTimestamp').textContent = h + ':' + m + ' ' + (now.getHours() >= 12 ? 'PM' : 'AM');
    }

    document.addEventListener("DOMContentLoaded", function () {
        updateTimestamp();

        const checkLiffInterval = setInterval(() => {
            if (typeof liff !== "undefined") {
                clearInterval(checkLiffInterval);
                liff.init({ liffId: myLiffId })
                    .then(() => {
                        liffReady = true;
                        const alreadyRedirected = sessionStorage.getItem('liff_login_attempted');
                        if (!liff.isLoggedIn()) {
                            if (!alreadyRedirected) {
                                sessionStorage.setItem('liff_login_attempted', '1');
                                liff.login({ redirectUri: window.location.href });
                            } else {
                                showLiffBanner();
                            }
                        } else {
                            sessionStorage.removeItem('liff_login_attempted');
                        }
                    })
                    .catch(err => {
                        console.error("LIFF init failed:", err);
                        showLiffBanner();
                    });
            }
        }, 300);
        setTimeout(() => clearInterval(checkLiffInterval), 10000);
    });

    function generatePreview() {
        const imageUrl = document.getElementById("imageUrl").value.trim();
        const targetUrl = document.getElementById("targetUrl").value.trim();
        const ratio = document.getElementById("aspectRatio").value || "30:25";

        if (!imageUrl || !targetUrl) {
            alert("กรุณากรอกลิงก์รูปภาพและ Target Link");
            return;
        }

        const img = document.getElementById("imagePreview");
        const anchor = document.getElementById("previewAnchor");
        const placeholder = document.getElementById("noImagePlaceholder");

        img.src = imageUrl;
        anchor.href = targetUrl;
        placeholder.style.display = "none";
        anchor.style.display = "block";

        const parts = ratio.split(":");
        if (parts.length === 2) {
            const w = parseFloat(parts[0]), h = parseFloat(parts[1]);
            img.style.aspectRatio = (w > 0 && h > 0) ? `${w}/${h}` : "30/25";
        }

        updateTimestamp();

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

    function clearFields() {
        document.getElementById("imageUrl").value = "";
        document.getElementById("targetUrl").value = "";
        document.getElementById("aspectRatio").value = "30:25";
        document.getElementById("FlexCode").value = "";
        document.getElementById("previewAnchor").style.display = "none";
        document.getElementById("imagePreview").src = "";
        document.getElementById("noImagePlaceholder").style.display = "flex";
        document.getElementById("previewAnchor").href = "#";
        document.getElementById("chatTimestamp").textContent = "";
        dynamicFlexJson = null;
    }

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
            showLiffBanner();
            return;
        }

        const btn = document.getElementById("shareBtnEl");
        btn.innerHTML = "&#x23F3; กำลังเปิด Share Target Picker...";
        btn.disabled = true;

        try {
            const result = await liff.shareTargetPicker([dynamicFlexJson]);
            if (result && result.status === 'success') {
                alert("แชร์ Flex Message สำเร็จเรียบร้อยแล้ว!");
            }
        } catch (error) {
            console.error(error);
            if (error.code === 'FORBIDDEN' || error.message.includes('not supported')) {
                showLiffBanner();
            } else {
                alert("เกิดข้อผิดพลาด: " + error.message);
            }
        } finally {
            btn.innerHTML = "<span>&#10024;</span> ส่งและแชร์ไปที่ LINE";
            btn.disabled = false;
        }
    }

    function showLiffBanner() {
        const liffUrl = "https://liff.line.me/" + myLiffId;
        const banner = document.getElementById('liffBanner');
        banner.style.display = 'block';
        banner.innerHTML = `
            <strong style="color:#00e676;">&#x26A0; แชร์ผ่าน PC ต้องเข้าผ่านลิงก์ LIFF</strong><br>
            <span style="color:#a0c4e0;">กรุณาเปิดลิงก์นี้เพื่อยืนยันตัวตนกับ LINE:</span><br>
            <a href="${liffUrl}" target="_blank"
               style="display:inline-block; margin-top:8px; padding:7px 16px; background:#00c853;
                      color:#fff; border-radius:6px; text-decoration:none; font-size:13px;">
                เปิดหน้านี้ผ่าน LINE (LIFF)
            </a>
        `;
    }

    function handleLogout() {
        try {
            if (typeof liff !== "undefined" && liff.isInitialized && liff.isLoggedIn()) {
                liff.logout();
            }
        } catch (e) {
            console.warn("LIFF logout skipped:", e.message);
        }
        window.location.href = "logout.php";
    }
</script>
</body>
</html>