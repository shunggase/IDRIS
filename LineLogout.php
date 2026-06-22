<?php
session_start();
session_destroy();

// ลบ Cookie ทั้งหมดที่ตั้งไว้
setcookie('user_id', '', time() - 3600, '/', '', true, true);
setcookie('user_fullname', '', time() - 3600, '/', '', true, true);
setcookie('line_profile_data', '', time() - 3600, '/', '', true, true);

header("Location: welcome.php");
exit();
?>