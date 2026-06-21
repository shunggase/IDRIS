<?php

include('functions.php');

$userdata = new DB_con();

// getting post values
$uemail = $_POST['email'];

$sql = $userdata->emailavailable($uemail);

$num = mysqli_num_rows($sql);

if ($num > 0) {
    echo "<span class='text-danger'>Email already registered.</span>";
    echo "<script>$('#submit').prop('disabled', true);</script>";
} else {
    echo "<span class='text-success'>Email available for registration.</span>";
    echo "<script>$('#submit').prop('disabled', false);</script>";
}

?>