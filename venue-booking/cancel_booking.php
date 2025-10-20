<?php
include 'db_connect.php';
$id = $_GET['id'];
$conn->query("DELETE FROM bookings WHERE id=$id");
header("Location: index.php?active_tab=Admin"); // <-- FIXED
?>