<?php
// ดึงค่าการเชื่อมต่อจาก Environment Variables
define('DB_SERVER',   getenv('DB_HOST')     ?: 'mysql-38bb5ec0-idris-app-1.i.aivencloud.com');
define('DB_USERNAME', getenv('DB_USER')     ?: 'avnadmin');
define('DB_PASS',     getenv('DB_PASSWORD') ?: ''); // ⚠️ อย่าลืมเติมใน Environment Variables บน Vercel ด้วยนะครับ
define('DB_NAME',     getenv('DB_NAME')     ?: 'defaultdb'); 
define('DB_PORT',     (int)(getenv('DB_PORT') ?: 16494));

class DB_con {
    public $conn;

    function __construct() {
        $conn = mysqli_init();

        if (!$conn) {
            die("mysqli_init failed");
        }

        // เปิด SSL สำหรับ Aiven
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

    // 1. เช็ก Username ซ้ำ
    public function usernameavailable($uname) {
        $stmt = mysqli_prepare($this->conn, "SELECT username FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $uname);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result; // ส่งคืน mysqli_result object เพื่อให้หน้าเดิมนำไป num_rows ได้
    }

    // 2. สมัครสมาชิก (แก้ไขการ Return ค่าให้เข้ากับหน้าสมัครสมาชิกเดิม)
    public function registration($fname, $uname, $uemail, $password) {
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO users(fullname, username, useremail, password) VALUES(?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $fname, $uname, $uemail, $password);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // คืนค่าเป็น mysqli_result หรือจำลองสเตตัสเพื่อให้โค้ดเดิมทำงานต่อได้ไม่เออร์เรอร์
        return $result; 
    }

    // 3. เข้าสู่ระบบ
    public function signin($uname, $password) {
        $stmt = mysqli_prepare($this->conn, 
            "SELECT id, fullname FROM users WHERE username = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $uname, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    // 4. เช็ก Email ซ้ำ
    public function emailavailable($uemail) {
        $stmt = mysqli_prepare($this->conn, 
            "SELECT useremail FROM users WHERE useremail = ?");
        mysqli_stmt_bind_param($stmt, "s", $uemail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
}
?>
