<?php
include_once 'Dbh.php';
session_start(); $validations = array(); $values = array(); 

class UserAuth extends Dbh{
    public $db;

    public function __construct(){
        $this->db = new Dbh();
    }

    public function register($fullnames, $email, $password, $confirmPassword, $country, $gender){
        $values = ['fullnames' => $fullnames, 'email' => $email, 'password' => $password, 'confirmPassword' => $confirmPassword, 'country' => $country, 'gender' => $gender]; //for input fields in case of error
        $conn = $this->db->connect();
        if(!($this->confirmPasswordMatch($password, $confirmPassword))){
            $validations['passmatch'] = "Oops! Your passwords do not match.";
        }
        if(($this->checkEmailExists($email)) == true ){
            $validations['notexist'] = "This email already exists. Please log into your account.";
        }       
        if(empty($validations)){
            $sql = "INSERT INTO Students (`full_names`, `email`, `password`, `country`, `gender`) VALUES ('$fullnames','$email', '$password', '$country', '$gender')";
            if(!($conn->query($sql))){
                $validations['error'] = "Oops".$conn->error;
            }
            if(empty($validations)){
                //sql successful
                echo "<h3 style='color:green;'>Your registration was successful! Please log in</h3>";
                echo "<br>
                <a href='forms/login.php'>Click here</a> if this page does not reload automatically in 5 seconds.
                ";
                header("refresh:5; url=forms/login.php");
            }else{
                $this->processValidation($validations, $values, 'forms/register.php'); //send validation errors back to register page
            }
        }else {
            $this->processValidation($validations, $values, 'forms/register.php');
        }
    }

    public function login($email, $password){
        $conn = $this->db->connect();
        $values = ['email' => $email];
        if(($this->checkEmailExists($email)) == false ){
            $validations['notexist'] = "Email not found";
        } 
        if(empty($validations)){
            $sql = "SELECT * FROM Students WHERE email='$email' AND `password`='$password'";
            $result = $conn->query($sql);
            if($result == false){
                $validations['error'] = "Oops".$conn->error;
            }
            if($result->num_rows < 1){
                $validations['passmatch'] = "Your password is incorrect. Try again!";
            }
            if(empty($validations)){
                $row = mysqli_fetch_array($result);
                $_SESSION['user'] = $row['full_names'];
                echo "<h3 style='color:green;'>Login successful!</h3>";
                echo "<br>
                <a href='dashboard.php'>Click here</a> if this page does not reload automatically in 5 seconds.
                ";
                header("refresh:5; url=dashboard.php");
            }else {
                //wrong email/password
                $this->processValidation($validations, $values, 'forms/login.php');
            }
        }else {
            $this->processValidation($validations, $values, 'forms/login.php');
        }
    }
    
    public function updateUser($username, $password){
        $conn = $this->db->connect();
        $values = ['email' => $username];
        if(($this->checkEmailExists($username)) == false ){
            $validations['notexist'] = "Email not found";
        } 
        if(empty($validations)){
            $sql = "UPDATE Students SET password = '$password' WHERE email = '$username'";
            $result = $conn->query($sql);
            if($conn->query($sql) === TRUE){
                echo "<h3 style='color:green;'>Password Changed Successfully! Log in</h3>";
                echo "<br>
                <a href='forms/login.php'>Click here</a> if this page does not reload automatically in 5 seconds.
                ";
                header("refresh:5; url=forms/login.php");
            } else {
                //header("Location: forms/resetpassword.php?error=1");
                $validations['error'] = "Oops".$conn->error;
                $this->processValidation($validations, $values, 'forms/resetpassword.php');
            }
        } else {
            $this->processValidation($validations, $values, 'forms/resetpassword.php');
        }      
    }

    public function checkEmailExists($email){ 
        $conn = $this->db->connect();
        $sql = "SELECT * FROM Students WHERE email = '$email'";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function processValidation($validations, $values, $location){
        $_SESSION['validations'] = $validations;
        $_SESSION['values'] = $values;
        header("location:$location");
    }

    public function getAllUsers(){
        $conn = $this->db->connect();
        if($this->getUserByUsername($_SESSION['user']) == false){
            //if logged-in user gets deleted
            echo "<script type='text/javascript'> alert('You are not logged in!'); </script>";
            $this->logout($_SESSION['user']);
        }
        $sql = "SELECT * FROM Students";
        $result = $conn->query($sql);
        echo"<html>
        <head>
        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>
        </head>
        <body>
        <a href='dashboard.php' style='background: blue;padding: 6px 15px;border-radius: 6px;color: white;text-decoration: none;'> Go Back To Dashboard </a>
        <center><h1><u> ZURI PHP STUDENTS </u> </h1> 
        <table class='table table-bordered' border='0.5' style='width: 80%; background-color: smoke; border-style: none'; >
        <tr style='height: 40px'>
            <thead class='thead-dark'> <th>ID</th><th>Full Names</th> <th>Email</th> <th>Gender</th> <th>Country</th> <th>Action</th>
        </thead></tr>";
        if($result->num_rows > 0){
            while($data = mysqli_fetch_assoc($result)){
                //show data
                echo "<tr style='height: 30px'>".
                    "<td style='width: 50px; background: blue'>" . $data['id'] . "</td>
                    <td style='width: 150px'>" . $data['full_names'] .
                    "</td> <td style='width: 150px'>" . $data['email'] .
                    "</td> <td style='width: 150px'>" . $data['gender'] . 
                    "</td> <td style='width: 150px'>" . $data['country'] . 
                    "</td>
                    <td style='width: 150px;'> 
                        <form action='action.php' method='post'>
                            <input type='hidden' name='id' value='" . $data['id'] . "'>
                            <button type='submit' name='delete' style='background: blue; color: white; cursor: pointer;'> DELETE </button>
                        </form>
                    </td>
                </tr>";
            }
            echo "</table></table></center></body></html>";
        }
    }

    public function deleteUser($id){
        echo "<script type='text/javascript'>
            var response = confirm('Are you sure you want to delete this account? Once deleted, all of its resources and data will be permanently erased.')
            if(response == true){           
                window.location = 'action.php?delete=Yess&id=$id';            
            }
            else{     
            window.location = 'action.php?all';    
            }
        </script>";
    }

    public function deleteconfirm($id){
        $conn = $this->db->connect();
        $sql = "DELETE FROM Students WHERE id = '$id'";
        if($conn->query($sql) === TRUE){
            // header("refresh:0.5; url=action.php?all");
            echo "<script type='text/javascript'>
                alert('Account Deleted successfully!');
                window.location = 'action.php?all';
            </script>";
        } else {
            // header("refresh:0.5; url=action.php?all=?message=Error");
            echo "<script type='text/javascript'>
                alert('There was an error! Try again');
                window.location = 'action.php?all';
            </script>";
        }
    }

    public function getUserByUsername($username){
        $conn = $this->db->connect();
        $sql = "SELECT * FROM Students WHERE full_names = '$username'";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function logout($username){
        session_start();
        session_destroy();
        header('Location: index.php');
    }

    public function confirmPasswordMatch($password, $confirmPassword){
        if($password === $confirmPassword){
            return true;
        } else {
            return false;
        }
    }
}
