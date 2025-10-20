<?php
include 'db_connect.php';

$location = $_POST['location'];
$venueTypes = $_POST['venueType'];
$userType = $_POST['userType'];
$dates = explode(',', $_POST['dates']);

foreach ($venueTypes as $venue) {
  foreach ($dates as $date) {
    $sql = "INSERT INTO bookings (location_id, venue_type, user_type, booked_date)
            VALUES ('$location', '$venue', '$userType', '$date')";
    $conn->query($sql);
  }
}
echo "<script>alert('Booking successful!'); window.location='index.php';</script>";
?>
