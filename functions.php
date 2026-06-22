<?php
// ดึงค่าการเชื่อมต่อจาก Environment Variables
define('DB_SERVER',   getenv('DB_HOST')     ?: 'localhost');
define('DB_USERNAME', getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASSWORD') ?: '');
define('DB_NAME',     getenv('DB_NAME')     ?: 'defaultdb'); // ✅ แก้ไข: defaultdb คือชื่อจริงบน Aiven
define('DB_PORT',     (int)(getenv('DB_PORT') ?: 3306));

class DB_con {
    public $conn;

    function __construct() {
        // ✅ แก้ไข: ใช้ mysqli_init + mysqli_real_connect เพื่อเปิด SSL รองรับ Aiven
        $conn = mysqli_init();

        if (!$conn) {
            die("mysqli_init failed");
        }

        // เปิด SSL (จำเป็นสำหรับ Aiven)
        mysqli_ssl_set($conn, null, null, null, null, null);
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

        $connected = mysqli_real_connect(
            $conn,
            DB_SERVER,
            DB_USERNAME,
            DB_PASS,
            DB_NAME,
            DB_PORT,
            null,
            MYSQLI_CLIENT_SSL
        );

        if (!$connected) {
            die("Failed to connect to MySQL: " . mysqli_connect_error());
        }

        mysqli_set_charset($conn, "utf8mb4");
        $this->conn = $conn;
    }

    public function usernameavailable($uname) {
        // ✅ แนะนำ: ใช้ Prepared Statement ป้องกัน SQL Injection
        $stmt = mysqli_prepare($this->conn, "SELECT username FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $uname);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    public function registration($fname, $uname, $uemail, $password) {
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO users(fullname, username, useremail, password) VALUES(?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $fname, $uname, $uemail, $password);
        return mysqli_stmt_execute($stmt);
    }

    public function signin($uname, $password) {
        $stmt = mysqli_prepare($this->conn, 
            "SELECT id, fullname FROM users WHERE username = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $uname, $password);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    public function emailavailable($uemail) {
        $stmt = mysqli_prepare($this->conn, 
            "SELECT useremail FROM users WHERE useremail = ?");
        mysqli_stmt_bind_param($stmt, "s", $uemail);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
}
?>