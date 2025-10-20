<?php
include 'db_connect.php';
$name = $_POST['location_name'];
$conn->query("INSERT INTO locations (name) VALUES ('$name')");
header("Location: index.php?active_tab=Admin"); // <-- FIXED
?>