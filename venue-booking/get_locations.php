<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM locations");
$locations = [];
while ($row = $result->fetch_assoc()) {
  $locations[] = $row;
}
echo json_encode($locations);
?>
