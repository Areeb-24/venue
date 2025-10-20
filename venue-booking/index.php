<?php 
include 'db_connect.php'; 
include 'get_venue_charges.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Venue Booking System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .admin-table td:nth-child(3),
    .admin-table td:nth-child(7) {
      line-height: 1.5;
      word-wrap: break-word;
    }
    .admin-table th:nth-child(3), .admin-table th:nth-child(7) {
      min-width: 150px;
    }
    .admin-table th:last-child, .admin-table td:last-child {
      min-width: 140px;
    }
  </style>
</head>
<body>

  <div class="main-container">
    <div class="header no-print">
      <h1>Venue Booking System</h1>
    </div>

    <div class="tab-nav no-print">
      <button class="tab-link active" onclick="openTab(event, 'Booking')">Book Venue</button>
      <button class="tab-link" onclick="openTab(event, 'Admin')">Admin Panel</button>
    </div>

    <div id="Booking" class="tab-content no-print" style="display: block;">
      <div class="container">
        <h2>New Booking</h2>
        <form id="bookingForm" method="POST" action="book_venue.php">
          
          <label for="location">Select Location:</label>
          <select id="location" name="location" required></select>

          <label for="user_name">Full Name:</label>
          <input type="text" id="user_name" name="user_name" placeholder="Enter your full name" required>

          <label for="booking_reason">Reason for Booking:</label>
          <input type="text" id="booking_reason" name="booking_reason" placeholder="e.g., Wedding, Meeting, Birthday Party" required>

          <label>Select Venue Type:</label>
          <div class="checkbox-group">
            <label><input type="checkbox" name="venueType[]" value="Community Hall"> Community Hall</label>
            <label><input type="checkbox" name="venueType[]" value="Park"> Park</label>
          </div>

          <label>Select Dates:</label>
          <input type="text" id="datePicker" name="dates" placeholder="Select one or more dates" readonly required>

          <label>Booking Type:</label>
          <div class="radio-group">
            <label><input type="radio" name="userType" value="Employee" required> Employee</label>
            <label><input type="radio" name="userType" value="Pensioner" required> Pensioner</label>
            <label><input type="radio" name="userType" value="General Public" required> General Public</label>
          </div>

          <button type="submit" class="btn-primary">Book Now</button>
        </form>
      </div>
    </div>

    <div id="Admin" class="tab-content">
      <div class="container">
        
        <div class="no-print">
          <h2>Manage Locations</h2>
          <form action="add_location.php" method="POST" class="admin-form">
            <label for="location_name">Add New Location:</label>
            <input type="text" id="location_name" name="location_name" placeholder="Enter new location name" required>
            <button type="submit" class="btn-secondary">Add Location</button>
          </form>
          
          <h3 class="admin-sub-header">Existing Locations</h3>
          <div class="table-responsive-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Location Name</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $res = $conn->query("SELECT * FROM locations ORDER BY name");
                  if ($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                      echo "<tr>
                              <td>{$row['id']}</td>
                              <td>" . htmlspecialchars($row['name']) . "</td>
                              <td><a href='delete_location.php?id={$row['id']}' class='btn-delete' onclick=\"return confirm('Are you sure you want to delete this location?');\">Delete</a></td>
                            </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='3'>No locations found.</td></tr>";
                  }
                ?>
              </tbody>
            </table>
          </div> <hr class="section-divider">
        </div>

        <div id="printable-area">
          <h2 class="admin-section-header">All Bookings</h2>
          
          <?php
            // --- Get Filter Values (No Change) ---
            $sort_order = $_GET['sort_order'] ?? 'DESC';
            $filter_location = $_GET['filter_location'] ?? '';
            $filter_venue = $_GET['filter_venue'] ?? '';
            $filter_user = $_GET['filter_user'] ?? '';
            $filter_start_date = $_GET['filter_start_date'] ?? '';
            $filter_end_date = $_GET['filter_end_date'] ?? '';
            $filter_status = $_GET['filter_status'] ?? ''; 

            // --- SQL Query (No Change) ---
            $sql = "SELECT 
                        l.name AS location_name, 
                        b.user_name, 
                        b.booking_reason, 
                        b.user_type, 
                        b.status, 
                        b.booking_group_id,
                        GROUP_CONCAT(DISTINCT b.venue_type ORDER BY b.venue_type SEPARATOR ', ') AS venues,
                        GROUP_CONCAT(DISTINCT b.booked_date ORDER BY b.booked_date ASC SEPARATOR ',') AS dates,
                        COUNT(DISTINCT b.booked_date) AS num_days,
                        SUM(DISTINCT b.per_day_charge) AS sum_of_per_day_charges_per_venue,
                        SUM(DISTINCT b.refundable_security) AS overall_refundable_security,
                        SUM(DISTINCT b.application_form_cost) AS overall_application_form_cost
                    FROM bookings b
                    JOIN locations l ON b.location_id = l.id";
            
            $where_clauses = [];
            $where_clauses[] = "b.booking_group_id IS NOT NULL";
            if (!empty($filter_location)) {
              $where_clauses[] = "b.location_id = " . intval($filter_location);
            }
            if (!empty($filter_venue)) {
              $where_clauses[] = "b.booking_group_id IN (SELECT booking_group_id FROM bookings WHERE venue_.type = '" . $conn->real_escape_string($filter_venue) . "')";
            }
            if (!empty($filter_user)) {
              $where_clauses[] = "b.user_type = '" . $conn->real_escape_string($filter_user) . "'";
            }
            if (!empty($filter_start_date)) {
              $where_clauses[] = "b.booking_group_id IN (SELECT booking_group_id FROM bookings WHERE booked_date >= '" . $conn->real_escape_string($filter_start_date) . "')";
            }
            if (!empty($filter_end_date)) {
              $where_clauses[] = "b.booking_group_id IN (SELECT booking_group_id FROM bookings WHERE booked_date <= '" . $conn->real_escape_string($filter_end_date) . "')";
            }
            if (!empty($filter_status)) {
              $where_clauses[] = "b.status = '" . $conn->real_escape_string($filter_status) . "'";
            }
            if (count($where_clauses) > 0) {
              $sql .= " WHERE " . implode(' AND ', $where_clauses);
            }
            $sql .= " GROUP BY b.booking_group_id, l.name, b.user_name, b.booking_reason, b.user_type, b.status";
            $sort_field = ($sort_order == 'ASC' ? 'MIN(b.booked_date) ASC' : 'MAX(b.booked_date) DESC');
            $sql .= " ORDER BY $sort_field";
          ?>

          <form action="index.php" method="GET" class="filter-form no-print">
            <input type="hidden" name="active_tab" value="Admin">
            <div class="filter-grid">
              <div class="filter-item">
                <label for="filter_location">Location:</label>
                <select name="filter_location" id="filter_location">
                  <option value="">All Locations</option>
                  <?php
                    $loc_res = $conn->query("SELECT * FROM locations ORDER BY name");
                    while($loc_row = $loc_res->fetch_assoc()) {
                      $selected = ($filter_location == $loc_row['id']) ? 'selected' : '';
                      echo "<option value='{$loc_row['id']}' $selected>" . htmlspecialchars($loc_row['name']) . "</option>";
                    }
                  ?>
                </select>
              </div>
              <div class="filter-item">
                <label for="filter_venue">Venue Type:</label>
                <select name="filter_venue" id="filter_venue">
                  <option value="">All Venues</option>
                  <option value="Community Hall" <?php if($filter_venue == 'Community Hall') echo 'selected'; ?>>Community Hall</option>
                  <option value="Park" <?php if($filter_venue == 'Park') echo 'selected'; ?>>Park</option>
                </select>
              </div>
              <div class="filter-item">
                <label for="filter_user">User Type:</label>
                <select name="filter_user" id="filter_user">
                  <option value="">All Users</option>
                  <option value="Employee" <?php if($filter_user == 'Employee') echo 'selected'; ?>>Employee</option>
                  <option value="Pensioner" <?php if($filter_user == 'Pensioner') echo 'selected'; ?>>Pensioner</option>
                  <option value="General Public" <?php if($filter_user == 'General Public') echo 'selected'; ?>>General Public</option>
                </select>
              </div>
              <div class="filter-item">
                <label for="filter_start_date">Start Date:</label>
                <input type="text" id="filterStartDate" name="filter_start_date" placeholder="From..." value="<?php echo htmlspecialchars($filter_start_date); ?>">
              </div>
              <div class="filter-item">
                <label for="filter_end_date">End Date:</label>
                <input type="text" id="filterEndDate" name="filter_end_date" placeholder="To..." value="<?php echo htmlspecialchars($filter_end_date); ?>">
              </div>
              <div class="filter-item">
                <label for="filter_status">Status:</label>
                <select name="filter_status" id="filter_status">
                  <option value="">All Statuses</option>
                  <option value="Pending" <?php if($filter_status == 'Pending') echo 'selected'; ?>>Pending</option>
                  <option value="Approved" <?php if($filter_status == 'Approved') echo 'selected'; ?>>Approved</option>
                </select>
              </div>
              <div class="filter-item">
                <label for="sort_order">Order by Date:</label>
                <select name="sort_order" id="sort_order">
                  <option value="DESC" <?php if($sort_order == 'DESC') echo 'selected'; ?>>Newest First</option>
                  <option value="ASC" <?php if($sort_order == 'ASC') echo 'selected'; ?>>Oldest First</option>
                </select>
              </div>
            </div>
            <div class="filter-actions">
              <a href="index.php?active_tab=Admin" class="btn-clear">Clear Filters</a>
              <button type="submit" class="btn-filter">Apply Filters</button>
              <button type="button" class="btn-print" onclick="printBookings()">Print</button>
            </div>
          </form>

          <div class="table-responsive-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>S. No.</th>
                  <th>Location</th>
                  <th>Venues</th> 
                  <th>Name</th>
                  <th>Reason</th>
                  <th>User Type</th>
                  <th>Booked Dates</th> 
                  <th>Status</th>
                  <th>Total Charges</th> 
                  <th class="no-print">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $bookings_res = $conn->query($sql);
                  $s_no = 1; 
                  
                  if ($bookings_res && $bookings_res->num_rows > 0) {
                    while($row = $bookings_res->fetch_assoc()) {
                      
                      // --- Format Dates ---
                      $date_list = explode(',', $row['dates']);
                      $formatted_dates = [];
                      foreach ($date_list as $d) {
                          if(!empty($d)) {
                              $formatted_dates[] = date('d-m-Y', strtotime($d));
                          }
                      }
                      $dates_display = implode(', ', $formatted_dates);
                      
                      // --- Format Venues ---
                      $venues_display = htmlspecialchars($row['venues']);

                      // --- Calculate Total Charges ---
                      $num_days = (int)$row['num_days'];
                      $per_day_charge_base_sum = (float)$row['sum_of_per_day_charges_per_venue'];
                      $one_time_fees = (float)$row['overall_refundable_security'] + (float)$row['overall_application_form_cost'];
                      $final_total_charges = ($per_day_charge_base_sum * $num_days) + $one_time_fees;
                      $total_charges_display = '₹' . number_format($final_total_charges, 2);

                      echo "<tr>
                              <td>" . $s_no++ . "</td>
                              <td>" . htmlspecialchars($row['location_name']) . "</td>
                              <td>" . $venues_display . "</td>
                              <td>" . htmlspecialchars($row['user_name']) . "</td>
                              <td>" . htmlspecialchars($row['booking_reason']) . "</td>
                              <td>" . htmlspecialchars($row['user_type']) . "</td>
                              <td>" . $dates_display . "</td>
                              <td>" . htmlspecialchars($row['status']) . "</td>
                              <td>" . $total_charges_display . "</td> 
                              <td class='no-print'>"; 

                      if ($row['status'] == 'Pending') {
                        echo "<a href='approve_booking.php?group_id={$row['booking_group_id']}' class='btn-approve'>Approve</a> ";
                      }
                      
                      echo "<a href='cancel_booking.php?group_id={$row['booking_group_id']}' class='btn-delete' onclick=\"return confirm('Are you sure you want to cancel this entire booking?');\">Cancel</a>
                              </td>
                            </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='10'>No bookings found matching your criteria.</td></tr>"; 
                    if($conn->error) {
                      echo "<tr><td colspan='10'>SQL Error: " . $conn->error . "</td></tr>";
                    }
                  }
                ?>
              </tbody>
            </table>
          </div> 
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // --- Tab and Print Functions (no change) ---
    function printBookings() {
      window.print();
    }

    function openTab(evt, tabName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tab-content");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tab-link");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(tabName).style.display = "block";
      if (evt) {
        evt.currentTarget.className += " active";
      } else {
        document.querySelector('.tab-link[onclick*="' + tabName + '"]').className += " active";
      }
    }

    // --- Main Application Logic ---
    document.addEventListener('DOMContentLoaded', (event) => {
        
        // --- 1. Initialize ALL flatpickr instances *ONCE* ---
        
        // Store the booking calendar instance so we can update it
        const datePickerInstance = flatpickr("#datePicker", {
            mode: "multiple",
            dateFormat: "Y-m-d", 
            altInput: true,
            altFormat: "d-m-Y",
            minDate: "today",
            disable: [] // Start with empty disable list
        });

        // Admin filter calendars
        flatpickr("#filterStartDate", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
        });
        flatpickr("#filterEndDate", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
        });

        // --- 2. Tab-opening logic ---
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('active_tab');
        if (activeTab === 'Admin') {
            openTab(null, 'Admin');
        }

        // --- 3. Location and Calendar-disabling logic ---

        // Function to update disabled dates
        function updateDisabledDates() {
            // DEBUG: Log that the function was called
            console.log("updateDisabledDates called...");
            
            const locationId = document.getElementById('location').value;
            const venues = Array.from(document.querySelectorAll("input[name='venueType[]']:checked"))
                .map(v => v.value);
            
            // DEBUG: Log the current selections
            console.log("Location ID:", locationId);
            console.log("Venues:", venues);
            
            if (!locationId || venues.length === 0) {
                // DEBUG: Log that we are clearing dates
                console.log("Clearing disabled dates.");
                datePickerInstance.set('disable', []);
                return;
            }

            const fetchURL = `get_booked_dates.php?location=${locationId}&venues=${venues.join(',')}`;
            
            // DEBUG: Log the URL we are about to fetch
            console.log("Fetching:", fetchURL);

            fetch(fetchURL)
                .then(response => {
                    if (!response.ok) {
                        // DEBUG: Log if the server response is bad
                        console.error("Network response was not ok. Status:", response.status);
                        throw new Error("Network response was not ok");
                    }
                    // Get the raw text first to check for PHP errors
                    return response.text(); 
                })
                .then(text => {
                    // DEBUG: Log the raw text from the server
                    console.log("Raw response text:", text);
                    
                    try {
                        // Try to parse the text as JSON
                        const dates = JSON.parse(text);
                        
                        // DEBUG: Log the final array of dates
                        console.log("Parsed dates to disable:", dates); 
                        
                        // Set the disabled dates
                        datePickerInstance.set('disable', dates);
                    } catch (e) {
                        // DEBUG: Log an error if JSON parsing fails
                        console.error("Failed to parse JSON:", e);
                        console.error("This usually means get_booked_dates.php had a PHP error.");
                    }
                })
                .catch(error => {
                    // DEBUG: Log any other network error
                    console.error('Fetch Error:', error);
                });
        }

        // --- 4. Attach event listeners ---
        document.querySelectorAll("input[name='venueType[]']").forEach(cb => {
            cb.addEventListener("change", updateDisabledDates);
        });
        document.getElementById('location').addEventListener('change', updateDisabledDates);

        // --- 5. Fetch locations and *then* run updateDisabledDates() ---
        fetch('get_locations.php')
            .then(res => res.json())
            .then(data => {
                const dropdown = document.getElementById('location');
                dropdown.innerHTML = '<option value="">-- Select a Location --</option>';
                data.forEach(loc => {
                    const option = document.createElement('option');
                    option.value = loc.id;
                    option.textContent = loc.name;
                    dropdown.appendChild(option);
                });
                
                // Run this once on load
                updateDisabledDates(); 
            });
    });
  </script>
</body>
</html>
