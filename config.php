<?php
session_start();
$host = "sql303.infinityfree.com";
$user = "if0_42459348"; 
$pass = "ThinkPlus2026"; 
$db = "if0_42459348_thinkplus";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
