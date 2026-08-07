<?php include 'config.php';?>
<!DOCTYPE html>
<html>
<head>
    <title>Register School - ThinkPlus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Register Your School</h2>
    <form method="POST">
        <input name="school_name" placeholder="School Name" required><br><br>
        <input name="county" placeholder="County" required><br><br>
        <input name="phone" placeholder="School Phone" required><br><br>
        <input name="email" type="email" placeholder="Email" required><br><br>
        <input name="password" type="password" placeholder="Password" required><br><br>
        <button name="register">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<?php
if(isset($_POST['register'])){
    $name = $_POST['school_name'];
    $county = $_POST['county'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM schools WHERE email='$email'");
    if($check->num_rows > 0){
        echo "<p style='color:red'>Email already exists</p>";
    } else {
        $sql = "INSERT INTO schools (name,county,phone,email,password) VALUES ('$name','$county','$phone','$email','$pass')";
        if($conn->query($sql)){
            echo "<p style='color:green'>Registration successful! <a href='login.php'>Login now</a></p>";
        } else {
            echo "<p style='color:red'>Error: " . $conn->error . "</p>";
        }
    }
}
?>
</body>
</html>
