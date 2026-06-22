<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. เคลียร์ข้อมูลสิทธิ์และโปรไฟล์ฝั่ง LINE ออกจากระบบเซสชัน
if (isset($_SESSION['profile'])) {
    unset($_SESSION['profile']);
}

// 2. เคลียร์ค่าคุกกี้ที่เกี่ยวข้องกับ LINE Login
if (isset($_COOKIE['line_state'])) {
    setcookie('line_state', '', time() - 3600, '/');
}

// 3. ใช้การสั่งย้ายหน้าผ่าน HTTP Header แบบระบุสถานะ 302 (Found/Redirect) ที่ชัดเจน
// ลดพารามิเตอร์สุ่มตัวเลข เพื่อเลี่ยงระบบสแกนบล็อกความปลอดภัยของระบบ Cloud
header("HTTP/1.1 302 Found");
header("Location: welcome.php?logout=linesuccess");
exit();
?>
