<?php

    // ดึงค่าการเชื่อมต่อจาก Environment Variables บน Vercel 
    // หากดึงไม่เจอ จะใช้ค่าเริ่มต้นจาก XAMPP สำรองไว้ให้ (ช่วยให้สามารถรันสลับเครื่องไปมาได้)
    define('DB_SERVER', getenv('DB_HOST') ?: 'localhost'); 
    define('DB_USERNAME', getenv('DB_USER') ?: 'root'); 
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: ''); 
    define('DB_NAME', getenv('DB_NAME') ?: 'register_idris'); 
    define('DB_PORT', getenv('DB_PORT') ?: '16494'); // เพิ่มตัวแปร Port เพื่อรองรับ Aiven

    class DB_con {
        public $conn;
        function __construct() {
            // ปรับคำสั่ง mysqli_connect ให้รองรับการระบุพอร์ตสำหรับการต่อเชื่อมเข้า Cloud 
            $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
            $this->conn = $conn;

            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }
        }

        public function usernameavailable($uname) {
            $checkusername = mysqli_query($this->conn, "SELECT username FROM users WHERE username='$uname'");
            return $checkusername;
        }

        public function registration($fname, $uname, $uemail, $password) {
            $reg = mysqli_query($this->conn, "INSERT INTO users(fullname, username, useremail, password)
            VALUES('$fname', '$uname', '$uemail', '$password')");
            return $reg;
        }

        public function signin($uname, $password) {
            $signinquery = mysqli_query($this->conn, "SELECT id, fullname FROM users WHERE username='$uname' and password='$password'");
            return $signinquery;
        }

        public function emailavailable($uemail) {
            $result = mysqli_query($this->conn, "SELECT useremail FROM users WHERE useremail='$uemail'");
            return $result;
        }
    }
?>
