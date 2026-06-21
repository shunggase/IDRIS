<?php

    define('DB_SERVER', 'localhost'); // your host name
    define('DB_USERNAME', 'root'); // your database username
    define('DB_PASSWORD', ''); // your database password
    define('DB_NAME', 'register_idris'); // your database name

    class DB_con {
        function __construct() {
            $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
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