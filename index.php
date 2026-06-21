<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

include('functions.php');

$userdata = new DB_con();

if (isset($_POST['submit_register'])) { // เปลี่ยนชื่อปุ่มเพื่อไม่ให้ตีกับฟอร์ม Login
    $fname = $_POST['fullname'];
    $uname = $_POST['username'];
    $uemail = $_POST['email'];
    $password = md5($_POST['password']);

    $username_check = $userdata->usernameavailable($uname); 
    $email_check = $userdata->emailavailable($uemail);       

    if (mysqli_num_rows($username_check) > 0) {
        echo "<script>alert('Username นี้ถูกใช้งานไปแล้ว กรุณาใช้ชื่ออื่น!');</script>";
        echo "<script>window.history.back();</script>";
    } else if (mysqli_num_rows($email_check) > 0) {
        echo "<script>alert('Email นี้ถูกลงทะเบียนไว้แล้ว กรุณาใช้เมลอื่น!');</script>";
        echo "<script>window.history.back();</script>";
    } else {
        $sql = $userdata->registration($fname, $uname, $uemail, $password);

        if ($sql) {
            echo "<script>alert('Registration Success!');</script>";
            echo "<script>window.location.href='signin.php'</script>";
        } else {
            echo "<script>alert('Something went wrong! Please try again.');</script>";
            echo "<script>window.location.href='index.php'</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDRIS APP - Welcome</title>
    <!-- Google Fonts Link For Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    
    <!-- แทรกสไตล์พิเศษเพิ่มเติมเพื่อปรับแต่งตัวอักษรแจ้งเตือน AJAX ให้เข้ากับธีมใหม่ -->
    <style>
        .ajax-msg {
            display: block;
            font-size: 0.8rem;
            margin-top: -5px;
            margin-bottom: 10px;
            text-align: left;
            padding-left: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <span class="hamburger-btn material-symbols-rounded">menu</span>
            <a href="#" class="logo">
                <!-- 💡 อย่าลืมเปลี่ยนโลโก้เป็นภาพของคุณ เช่น IDRISLOGO.png -->
                <img src="IDRISLOGO.png" alt="logo">
                <h2>IDRIS</h2>
            </a>
            <ul class="links">
                <span class="close-btn material-symbols-rounded">close</span>
                <li><a href="#">Home</a></li>
                <li><a href="#">Portfolio</a></li>
                <li><a href="#">Courses</a></li>
                <li><a href="#">About us</a></li>
                <li><a href="#">Contact us</a></li>
            </ul>
            <button class="login-btn">LOG IN</button>
        </nav>
    </header>
    <div class="blur-bg-overlay"></div>
    <div class="form-popup">
        <span class="close-btn material-symbols-rounded">close</span>
        
        <!-- ==================== โซนฟอร์ม LOGIN (ฝั่งซ้าย/ดั้งเดิม) ==================== -->
        <div class="form-box login">
            <div class="form-details">
                <h2>Welcome Back</h2>
                <p>Please log in using your personal information to stay connected with us.</p>
            </div>
            <div class="form-content">
                <h2>LOGIN</h2>
                <!-- 💡 ปรับให้ชี้ไปที่ signin.php ของคุณ หรือจะรวมระบบโพสต์ไว้ที่นี่ก็ได้ -->
                <form action="signin.php" method="POST">
                    <div class="input-field">
                        <input type="text" name="username" required>
                        <label>Username</label>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" required>
                        <label>Password</label>
                    </div>
                    <a href="#" class="forgot-pass-link">Forgot password?</a>
                    <button type="submit" name="login">Log In</button>
                </form>
                <div class="bottom-link">
                    Don't have an account?
                    <a href="#" id="signup-link">Signup</a>
                </div>
            </div>
        </div>

        <!-- ==================== โซนฟอร์ม SIGNUP / REGISTER (ฝั่งขวา) ==================== -->
        <div class="form-box signup">
            <div class="form-details">
                <h2>Create Account</h2>
                <p>To become a part of our community, please sign up using your personal information.</p>
            </div>
            <div class="form-content">
                <h2>SIGNUP</h2>
                
                <!-- 💡 ส่งค่ากลับมาประมวลผลที่ตัวเองด้วยวิธี POST -->
                <form method="POST">
                    
                    <div class="input-field">
                        <input type="text" name="fullname" required>
                        <label>Full Name</label>
                    </div>
                    
                    <div class="input-field">
                        <!-- ผูกระบบ AJAX ตรวจสอบ Username ซ้ำแบบเรียลไทม์ -->
                        <input type="text" id="reg_username" name="username" onblur="checkusername(this.value)" required>
                        <label>User Name</label>
                    </div>
                    <!-- กล่องข้อความแจ้งเตือนสถานะของ Username -->
                    <span id="usernameavailable" class="ajax-msg"></span>

                    <div class="input-field">
                        <!-- ผูกระบบ AJAX ตรวจสอบ Email ซ้ำแบบเรียลไทม์ -->
                        <input type="email" id="reg_email" name="email" onblur="checkemail(this.value)" required>
                        <label>Email</label>
                    </div>
                    <!-- กล่องข้อความแจ้งเตือนสถานะของ Email -->
                    <span id="emailavailable" class="ajax-msg"></span>

                    <div class="input-field">
                        <input type="password" name="password" required>
                        <label>Password</label>
                    </div>
                    
                    <div class="policy-text">
                        <input type="checkbox" id="policy" required>
                        <label for="policy">
                            I agree the
                            <a href="#" class="option">Terms & Conditions</a>
                        </label>
                    </div>
                    
                    <!-- เปลี่ยนชื่อเนมปุ่มเป็น submit_register เพื่อสอดรับกับ PHP บรรทัดที่ 6 -->
                    <button type="submit" name="submit_register" id="submit">Sign Up</button>
                </form>
                
                <div class="bottom-link">
                    Already have an account? 
                    <a href="#" id="login-link">Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 🛠️ สคริปต์ระเบียบ AJAX ในการสื่อสารข้อมูลเช็คความซ้ำซ้อน 🛠️ -->
    <script src="https://jquery.com"></script>
    <script>
    function checkusername(username) {
        if(username.trim() == '') {
            $("#usernameavailable").html("");
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: "checkuser_availability.php",
            data: 'username=' + username,
            success: function(data) {
                $("#usernameavailable").html(data);
            }
        });
    }

    function checkemail(email) {
        if(email.trim() == '') {
            $("#emailavailable").html("");
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: "checkemail_availability.php",
            data: 'email=' + email,
            success: function(data) {
                $("#emailavailable").html(data);
            }
        });
    }
    </script>
</body>
</html>
