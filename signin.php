<?php
session_start();
include_once('functions.php');

$userdata = new DB_con();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $uname = $_POST['username'];
    $password = md5($_POST['password']);

    $result = $userdata->signin($uname, $password);
    
    // ตรวจสอบและดึงข้อมูลออกมาในรูปแบบสากลเพื่อไม่ให้เซสชันหลุดหายบน Vercel
    if ($result) {
        $num = mysqli_fetch_assoc($result); // เปลี่ยนมาใช้ mysqli_fetch_assoc เพื่อความแม่นยำในการระบุชื่อฟิลด์
        
        if ($num) {
            $_SESSION['id'] = $num['id'];
            $_SESSION['fullname'] = $num['fullname'];
            
            // ใช้ JavaScript บังคับล้างแคชบราวเซอร์เพื่อทะลุเข้าหน้า welcome.php
            echo "<script>
                alert('Login Success!');
                window.location.href = 'welcome.php?v=" . time() . "';
            </script>";
            exit();
        } else {
            echo "<script>
                alert('รหัสผ่านไม่ถูกต้อง หรือไม่พบชื่อผู้ใช้นี้ในระบบ!');
                window.location.href = 'signin.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อคลาวด์ฐานข้อมูล!');
            window.location.href = 'signin.php';
        </script>";
        exit();
    }
}
?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IDRIS APP LOGIN</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  </head>
  <body class="text-center">

    <div class="container">
        <img class="mb-4" src="https://img2.pic.in.th/de114711-2369-4e5e-8b8c-10c6ab4dfb77.png" alt="" height="250">
        <h1 class="h3 mb-3 fw-normal"><b>IDRIS</b> - Login</h1>
        <hr>
        <form method="post" action="signin.php">
            <div class="mb-3">
                <label for="username" class="form-label">User Name</label>
                <input type="text" class="form-control" id="username" name="username">
                <span id="usernameavailable" class="form-text"></span>                
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" name="login" class="btn btn-success">Login</button>
            <a href="index.php" class="btn btn-primary">Go to Register</a>
        </form>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>