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
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <img src="IDRISLOGO.png" class="brand-mark me-2" alt="IDRIS Logo" style="height: 40px; width: auto;">
    <a class="navbar-brand" href="#"><strong>IDRIS</strong></a>
    <div class="text-white d-flex flex-column">          
      <span class="brand-subtitle text-white-50 small">LINE Flex Image Preview</span>
      <span class="brand-title fw-bold">Intelligent Digital Response & Investigation System</span>
    </div>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDarkDropdown" aria-controls="navbarNavDarkDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNavDarkDropdown">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($session_fullname, ENT_QUOTES, 'UTF-8'); ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<body>

    <div class="container">
        <h1 class="mt-5">Welcome <?php echo htmlspecialchars($session_fullname, ENT_QUOTES, 'UTF-8'); ?> To IDRIS</h1>
        <hr>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        </p>
        <hr>
        <a href="logout.php" class="btn btn-danger">LineLogout</a>
    </div>

    <?php require_once('nav.php'); ?>

    <main class="container">
        <div class="bg-light p-5 rounded mt-3">
            <h1 class="mt-2">Welcome <?php echo htmlspecialchars($session_fullname, ENT_QUOTES, 'UTF-8'); ?> To IDRIS</h1>
            <p class="lead">Let's login to the IDRIS by using line login</p>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
