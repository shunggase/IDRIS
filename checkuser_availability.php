<?php

include('functions.php');

$userdata = new DB_con();

// getting post values
$uname = $_POST['username'];

$sql = $userdata->usernameavailablity($uname);

$num = mysqli_num_rows($sql);

if ($num > 0) {
    echo "<span class='text-danger'>Username already taken.</span>";
    echo "<script>$('#submit').prop('disabled', true);</script>";
} else {
    echo "<span class='text-success'>Username available for registration.</span>";
    echo "<script>$('#submit').prop('disabled', false);</script>";
}

?>