<?php
ob_start();
session_start();
require_once('LineLogin.php');

$line = new LineLogin();
$get = $_GET;

$code = isset($get['code']) ? $get['code'] : null;
$state = isset($get['state']) ? $get['state'] : null;

if (!$code) {
    header('location: signin.php');
    exit();
}

// 1. เรียกขอ Token จาก LINE
$token = $line->token($code, $state);

// 2. แปลงผลลัพธ์ให้อยู่ในรูปของ Array เพื่อให้เช็คค่าได้แม่นยำ 100% ไม่ว่าคลาสจะส่งมาแบบไหน
$token_array = json_decode(json_encode($token), true);

// 3. ตรวจสอบกรณีเกิดข้อผิดพลาดจาก LINE
if (isset($token_array['error'])) {
    $err = $token_array['error'];
    $desc = isset($token_array['error_description']) ? urlencode($token_array['error_description']) : '';
    header('location: welcome.php?error=' . $err . '&error_description=' . $desc);
    exit();
}

// 4. ตรวจสอบเมื่อได้รับ id_token หรือ access_token เรียบร้อยแล้ว
if (isset($token_array['id_token']) || isset($token_array['access_token'])) {

    $access_token = isset($token_array['access_token']) ? $token_array['access_token'] : null;

    if ($access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/profile");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $access_token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $line_raw_profile = curl_exec($ch);
        curl_close($ch);

        // แปลงผลลัพธ์เป็น Array
        $profile_array = json_decode($line_raw_profile, true);
    } else {
        // หากไม่มี access_token ให้ใช้ฟังก์ชันเดิมสำรอง
        $token_obj = (object)$token_array;
        $profile = $line->profileFormIdToken($token_obj);
        $profile_array = json_decode(json_encode($profile), true);
    }
    
    // บันทึกข้อมูลลงเซสชัน
    $_SESSION['profile'] = $profile_array;
    
    // 💡 ย้ายผู้ใช้ไปยังหน้าปลายทางสำเร็จ (ระวังเรื่องตัวพิมพ์เล็ก-ใหญ่ของชื่อไฟล์ด้วยนะครับ)
    header('location: IDRISwellcome.php');
    exit(); 
} else {
    // 🔍 หากระบบยังค้างอยู่ที่หน้านี้ ให้เปิดระบบตรวจจับค่าด้านล่างนี้ดูครับ:
    echo "<h3>ระบบดึง Token ไม่สำเร็จ หรือโครงสร้างข้อมูลไม่ตรงล็อก</h3>";
    echo "ข้อมูลที่ LINE ส่งกลับมาจริงคือ: <br>";
    echo "<pre>";
    print_r($token_array);
    echo "</pre>";
    exit();
}
?>