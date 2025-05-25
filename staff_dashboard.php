<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["staff_loggedin"]) || $_SESSION["staff_loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

// Include config file
require_once "mysqli_connect.php";

// Fetch all orders (reservations)
$sql = "SELECT order_id, contact_person, contact_no, num_pax, event_date, event_time, location, company_name, email, registration_date FROM orders ORDER BY event_date DESC, registration_date DESC";
$result = mysqli_query($dbc, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="includes/header.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .reservations-table th,
        .reservations-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .reservations-table th {
            background-color: #4CAF50;
            color: white;
        }
        .reservations-table tr:hover {
            background-color: #f5f5f5;
        }
        .logout-btn {
            float: right;
            margin-bottom: 20px;
        }

        /* TEMPORARY: Email Management Styles - REMOVE AFTER TESTING */
        .email-management {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .warning-banner {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .warning-banner strong {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 20px 0;
        }

        .email-table th, .email-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .email-table th {
            background-color: #f5f5f5;
        }

        .form-group {
            margin: 15px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .form-group label {
            display: block;
            margin: 5px 0;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
    </style>
</head>
<body>
    <?php include('includes/header.html'); ?>
    
    <div class="dashboard-container">
        <div class="welcome-message">
            <h2>Welcome, Staff Member!</h2>
            <a href="staff_logout.php" class="btn btn-delete logout-btn">Logout</a>
        </div>

        <h3>Reservation List</h3>
        <table class="reservations-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Contact Person</th>
                    <th>Contact No</th>
                    <th>No. of Pax</th>
                    <th>Event Date</th>
                    <th>Event Time</th>
                    <th>Location</th>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact_person']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['num_pax']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['event_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['registration_date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align: center;'>No reservations found</td></tr>";
                }
                ?>
            </tbody>
        </table>

    <?php include('includes/footer.html'); ?>
</body>
</html>
<?php
// Close connection
mysqli_close($dbc);
?>
