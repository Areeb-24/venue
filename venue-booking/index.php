<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Venue Booking System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Optional Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
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

          <label>Select Venue Type:</label>
          <div class="checkbox-group">
            <label><input type="checkbox" name="venueType[]" value="Community Hall"> Community Hall</label>
            <label><input type="checkbox" name="venueType[]" value="Park"> Park</label>
          </div>

          <label>Select Dates:</label>
          <input type="text" id="datePicker" name="dates" placeholder="Select one or more dates" readonly required>

          <label>Booking Type:</label>
          <div class="radio-group">
            <label><input type="radio" name="userType" value="Employee/Pensioner" required> Employee/Pensioner</label>
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
          
          <hr class="section-divider">
        </div>

        <div id="printable-area">
          <h2 class="admin-section-header">All Bookings</h2>
          
          <?php
            // --- Get Filter Values ---
            $sort_order = $_GET['sort_order'] ?? 'DESC';
            $filter_location = $_GET['filter_location'] ?? '';
            $filter_venue = $_GET['filter_venue'] ?? '';
            $filter_user = $_GET['filter_user'] ?? '';
            $filter_start_date = $_GET['filter_start_date'] ?? '';
            $filter_end_date = $_GET['filter_end_date'] ?? '';

            // --- Build Dynamic SQL Query ---
            $sql = "SELECT b.id, l.name AS location_name, b.venue_type, b.user_type, b.booked_date 
                    FROM bookings b
                    JOIN locations l ON b.location_id = l.id";
            
            $where_clauses = [];
            
            if (!empty($filter_location)) {
              $where_clauses[] = "b.location_id = " . intval($filter_location);
            }
            if (!empty($filter_venue)) {
              $where_clauses[] = "b.venue_type = '" . $conn->real_escape_string($filter_venue) . "'";
            }
            if (!empty($filter_user)) {
              $where_clauses[] = "b.user_type = '" . $conn->real_escape_string($filter_user) . "'";
            }
            if (!empty($filter_start_date)) {
              $where_clauses[] = "b.booked_date >= '" . $conn->real_escape_string($filter_start_date) . "'";
            }
            if (!empty($filter_end_date)) {
              $where_clauses[] = "b.booked_date <= '" . $conn->real_escape_string($filter_end_date) . "'";
            }

            if (count($where_clauses) > 0) {
              $sql .= " WHERE " . implode(' AND ', $where_clauses);
            }
            
            // Add sorting
            $sql .= " ORDER BY b.booked_date " . ($sort_order == 'ASC' ? 'ASC' : 'DESC');
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
                  <option value="Employee/Pensioner" <?php if($filter_user == 'Employee/Pensioner') echo 'selected'; ?>>Employee/Pensioner</option>
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

          <table class="admin-table">
            <thead>
              <tr>
                <th>S. No.</th> <th>Location</th>
                <th>Venue</th>
                <th>User Type</th>
                <th>Booked Date</th>
                <th class="no-print">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $bookings_res = $conn->query($sql);
                $s_no = 1; // <-- ADDED
                
                if ($bookings_res->num_rows > 0) {
                  while($row = $bookings_res->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $s_no++ . "</td> <td>" . htmlspecialchars($row['location_name']) . "</td>
                            <td>" . htmlspecialchars($row['venue_type']) . "</td>
                            <td>" . htmlspecialchars($row['user_type']) . "</td>
                            <td>" . htmlspecialchars($row['booked_date']) . "</td>
                            <td class='no-print'><a href='cancel_booking.php?id={$row['id']}' class='btn-delete' onclick=\"return confirm('Are you sure you want to cancel this booking?');\">Cancel</a></td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='6'>No bookings found matching your criteria.</td></tr>"; // <-- Colspan remains 6
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // --- Print Function ---
    function printBookings() {
      window.print();
    }

    // --- Tab Navigation ---
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
        // Find the tab link that matches tabName and add active class
        document.querySelector('.tab-link[onclick*="' + tabName + '"]').className += " active";
      }
    }

    // --- Open correct tab on page load (e.g., after filter) ---
    document.addEventListener('DOMContentLoaded', (event) => {
      const urlParams = new URLSearchParams(window.location.search);
      const activeTab = urlParams.get('active_tab');
      if (activeTab === 'Admin') {
        openTab(null, 'Admin');
      }

      // Initialize all flatpickr instances
      flatpickr("#datePicker", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        minDate: "today"
      });

      flatpickr("#filterStartDate", {
        dateFormat: "Y-m-d"
      });

      flatpickr("#filterEndDate", {
        dateFormat: "Y-m-d"
      });
    });

    // --- Booking Form Logic ---
    
    // Fetch locations for dropdown
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
      });

    // Update disabled dates when venue or location changes
    document.querySelectorAll("input[name='venueType[]']").forEach(cb => {
      cb.addEventListener("change", updateDisabledDates);
    });
    document.getElementById('location').addEventListener('change', updateDisabledDates);

    function updateDisabledDates() {
      const locationId = document.getElementById('location').value;
      const venues = Array.from(document.querySelectorAll("input[name='venueType[]']:checked"))
        .map(v => v.value);
      
      if (!locationId || venues.length === 0) {
        if (window.datePickerInstance) {
          window.datePickerInstance.set('disable', []);
        }
        return;
      }

      fetch(`get_booked_dates.php?location=${locationId}&venues=${venues.join(',')}`)
        .then(res => res.json())
        .then(dates => {
          if (window.datePickerInstance) {
            window.datePickerInstance.set('disable', dates);
D          }
        });
    }

    // Store datePicker instance on window to avoid scope issues
    document.addEventListener('DOMContentLoaded', () => {
      window.datePickerInstance = flatpickr("#datePicker", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: []
      });
    });
  </script>
</body>
</html>