<?php
//  บรรทัดที่ 1: บังคับเปิดระบบบัฟเฟอร์ ห้ามมีตัวอักษรหรือช่องว่างก่อนคำนี้เด็ดขาด
ob_start(); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ใช้งานคุ้กกี้สำรองหาก Vercel เคลียร์ค่าเซสชันหลักทิ้งระหว่างย้ายหน้า
if (isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    $_SESSION['id'] = $_COOKIE['user_id'];
    $_SESSION['fullname'] = $_COOKIE['user_fullname'];
}

// ระบบความปลอดภัยเช็กสิทธิ์: หากไร้ร่องรอยคุ้กกี้และเซสชันจริงให้ดีดกลับหน้าล็อกอิน
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: signin.php?auth=failed&v=" . time());
    exit();
}

$session_fullname = $_SESSION['fullname'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - IDRIS</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #040814;
            --card-bg: rgba(14, 22, 45, 0.6);
            --primary-neon: #a3ff00;
            --text-muted: #8492a6;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ตกแต่ง Navbar ให้โปร่งแสงเข้ากับธีม */
        .idris-nav {
            background: rgba(4, 8, 20, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* โซนเนื้อหาหลักแบ่งสัดส่วนขวา-ซ้ายแบบโมเดิร์น */
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 60px 0;
        }

        /* ดีไซน์กรอบรูปภาพ Hacker ฝั่งซ้าย */
        .cyber-graphic-wrap {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .cyber-bg-glow {
            width: 85%;
            aspect-ratio: 1/1;
            background: radial-gradient(circle, rgba(163,255,0,0.2) 0%, rgba(4,8,20,0) 70%);
            position: absolute;
            z-index: 1;
        }

        .cyber-img {
            max-width: 100%;
            height: auto;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 0 30px rgba(163, 255, 0, 0.15));
        }

        /* ตกแต่งข้อความพาดหัว */
        .tag-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            color: #ffffff;
        }

        .tag-icon {
            color: var(--primary-neon);
            font-size: 0.9rem;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* ตกแต่งกล่องฟีเจอร์ย่อย 4 กล่อง */
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 2.5rem;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 14px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(8px);
        }

        .feature-icon-box {
            width: 32px;
            height: 32px;
            background: rgba(163, 255, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-neon);
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .feature-text {
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
        }

        /* 🟢 ปุ่ม Line Login สีเขียวสะท้อนแสงทรงพลังตามแบบ */
        .btn-cyber-login {
            background-color: var(--primary-neon);
            color: #040814;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 100px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(163, 255, 0, 0.25);
        }

        .btn-cyber-login:hover {
            background-color: #ffffff;
            color: #040814;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>

    <!-- แถบเมนูด้านบน (คงโครงสร้าง PHP เดิมของคุณไว้ครบถ้วน) -->
    <nav class="navbar navbar-expand-lg navbar-dark idris-nav">
      <div class="container">
        <div class="d-flex align-items-center">
            <img src="IDRISLOGO.png" class="brand-mark me-2" alt="IDRIS Logo" style="height: 40px; width: auto;">
            <div class="text-white d-flex flex-column">          
              <span class="brand-title fw-bold" style="letter-spacing: 0.5px;">PROJECT IDRIS</span>
              <span class="brand-subtitle text-white-50 small" style="font-size: 0.75rem;">Intelligent Digital Response & Investigation System</span>
            </div>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDarkDropdown" aria-controls="navbarNavDarkDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNavDarkDropdown">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
              <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                👤 <?php echo htmlspecialchars($session_fullname, ENT_QUOTES, 'UTF-8'); ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">System Status: Online</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- โซนจัดการสไตล์เลย์เอาต์หน้าจอหลัก (แบ่งสัดส่วนโครงสร้าง ซ้าย-ขวา) -->
    <main class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                
                <!-- 👈 ฝั่งซ้าย: รูปกราฟิกสไตล์ Cyber Hacker พร้อมวงแสงออร่าเรืองแสงด้านหลัง -->
                <div class="col-lg-6 order-lg-1 order-2 text-center">
                    <div class="cyber-graphic-wrap">
                        <div class="cyber-bg-glow"></div>
                        <!-- ⚠️ แนะนำ: คัดลอกรูปภาพกราฟิก Hacker ของคุณมาตั้งชื่อว่า cyber-vector.png ไว้ในโปรเจกต์ครับ -->
                        <img src="cyber-vector.png" alt="Cyber Defense" class="cyber-img" onerror="this.src='https://flaticon.com'">
                    </div>
                </div>

                <!-- 👉 ฝั่งขวา: กล่องข้อความพาดหัว และกลุ่มกล่องคุณสมบัติฟีเจอร์ความปลอดภัย -->
                <div class="col-lg-6 order-lg-2 order-1">
                    
                    <div class="tag-label">
                        <span class="tag-icon">🛡️</span> TAILORED CYBERSECURITY
                    </div>
                    
                    <h1 class="hero-title">
                        Leading Charge In Global<br>
                        <span style="color: var(--primary-neon);">Cyber Defense</span> Innovation
                    </h1>
                    
                    <p class="hero-desc">
                        Welcome back, <strong class="text-white"><?php echo htmlspecialchars($session_fullname, ENT_QUOTES, 'UTF-8'); ?></strong>. 
                        At the core of our cybersecurity approach commitment to excellence and trust. 
                        Let's synchronize your transmission using securely automated token protocols.
                    </p>
                    
                    <!-- ตารางการ์ดข้อมูลฟีเจอร์ย่อย 4 ด้านตามภาพตัวอย่างเป๊ะๆ -->
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon-box">🛡️</div>
                            <div class="feature-text">Maximize Security</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-box">📊</div>
