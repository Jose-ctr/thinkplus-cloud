<?php
include 'config.php';
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $school_name = $conn->real_escape_string($_POST['school_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $conn->real_escape_string($_POST['phone']);

    $sql = "INSERT INTO schools (school_name, email, password, phone) VALUES ('$school_name', '$email', '$password', '$phone')";

    if($conn->query($sql) === TRUE){
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
