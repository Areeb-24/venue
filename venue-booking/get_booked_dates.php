<?php
include 'db_connect.php';

$location = $_GET['location'];
$venues = explode(',', $_GET['venues']);

$placeholders = implode(',', array_fill(0, count($venues), '?'));
$sql = "SELECT booked_date FROM bookings WHERE location_id = ? AND venue_type IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('i', 1) . str_repeat('s', count($venues)), $location, ...$venues);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
while ($row = $result->fetch_assoc()) {
  $dates[] = $row['booked_date'];
}
echo json_encode($dates);
?>
