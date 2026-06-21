<?php
session_start();
session_destroy(); // ล้างเซสชันเก่า

//  ล้างค่าคุ้กกี้ฝั่งบราวเซอร์ให้สะอาด
setcookie('user_id', '', time() - 3600, '/');
setcookie('user_fullname', '', time() - 3600, '/');

header("Location: index.php?v=" . time());
exit();
?>
