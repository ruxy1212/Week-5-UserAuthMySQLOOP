<?php

Class Dbh {
    private $hostname;
    private $username;
    private $password;
    private $dbname;

    protected function connect(){ 
        $this->hostname = "127.0.0.1";
        $this->username = "root";
        $this->dbname = "zuriphp";
        $this->password = "1234gh0987";

        $conn = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
        // if(!$conn){
        //     echo "<script> alert('Error connecting to the database') </script>";
        // }
        return $conn;
        }
}

?>