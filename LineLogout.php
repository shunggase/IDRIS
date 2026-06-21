<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. เคลียร์เฉพาะข้อมูลโปรไฟล์ฝั่ง LINE ออกจากระบบเซสชัน
if (isset($_SESSION['profile'])) {
    unset($_SESSION['profile']);
}

// 2. เคลียร์ค่าคุกกี้ที่เกี่ยวข้องกับรหัสความปลอดภัยของ LINE (ถ้ามี)
if (isset($_COOKIE['line_state'])) {
    setcookie('line_state', '', time() - 3600, '/');
}

// 3. สั่งเปลี่ยนเส้นทางดีดพาร่างของคุณส่งกลับไปสู่หน้า welcome.php พร้อมรหัสป้องกันแคช
header("Location: welcome.php?logout=linesuccess&v=" . time());
exit();
?>
