<?php
session_start();
include_once('functions.php');

$userdata = new DB_con();

if (isset($_POST['login'])) {
    $uname = $_POST['username'];
    $password = md5($_POST['password']);

    $result = $userdata->signin($uname, $password);
    if ($result && mysqli_num_rows($result) > 0) {
        $num = mysqli_fetch_array($result); // นำข้อมูลมาเก็บเข้าเซสชันด้านล่างตามปกติ
        $_SESSION['id'] = $num['id'];
        $_SESSION['fullname'] = $num['fullname'];
        echo "<script>alert('Login Success!');</script>";
        echo "<script>window.location.href='welcome.php'</script>";
    } else {
        echo "<script>alert('Something went wrong! Please try again.');</script>";
        echo "<script>window.location.href='signin.php'</script>";
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
        <form method="post">
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