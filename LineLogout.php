<?php
session_start();
require_once('LineLogin.php');

// 1. ตรวจสอบว่ามีเซสชันของ LINE อยู่หรือไม่
if (isset($_SESSION['profile'])) {
    $profile = $_SESSION['profile'];
    $line = new LineLogin();
    
    // ดึง access_token ออกมาเพื่อสั่งยกเลิกสิทธิ์กับทาง LINE Server (Revoke)
    $token = is_object($profile) ? $profile->access_token : ($profile['access_token'] ?? null);
    if ($token) {
        $line->revoke($token);
    }
    
    // 💡 จุดสำคัญที่สุด: ลบเฉพาะเซสชันโปรไฟล์ LINE ออก
    // ห้ามใช้ session_destroy(); เพราะจะทำให้ $_SESSION['id'] ของระบบหลักพังไปด้วย
    unset($_SESSION['profile']); 
}

// 2. ส่งผู้ใช้งานดีดกลับมาที่หน้า welcome.php ตามต้องการ
header("Location: welcome.php");
exit(); // ตัดการทำงานทันทีหลังจากส่งย้ายหน้า
?>