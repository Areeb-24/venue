<?php
include 'db_connect.php';
$id = $_GET['id'];
$conn->query("DELETE FROM locations WHERE id=$id");
header("Location: index.php?active_tab=Admin"); // <-- FIXED
?>