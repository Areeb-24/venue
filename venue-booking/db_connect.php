<?php
$servername = "localhost";
$username = "root";  // change if needed
$password = "";
$dbname = "venue_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
