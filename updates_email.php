<?php
require('mysqli_connect.php');

// Function to update email
function update_email($dbc, $table, $id_field, $id, $new_email) {
    $q = "UPDATE $table SET email = ? WHERE $id_field = ?";
    $stmt = mysqli_prepare($dbc, $q);
    mysqli_stmt_bind_param($stmt, 'si', $new_email, $id);
    return mysqli_stmt_execute($stmt);
}

// Function to display current emails
function display_emails($dbc) {
    echo "<h2>Current Email Addresses</h2>";
    
    // Display admin emails
    echo "<h3>Admin Emails:</h3>";
    $q = "SELECT user_id, email FROM admin";
    $result = mysqli_query($dbc, $q);
    echo "<table border='1'>";
    echo "<tr><th>User ID</th><th>Email</th></tr>";
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        echo "<tr><td>{$row['user_id']}</td><td>{$row['email']}</td></tr>";
    }
    echo "</table>";
    
    // Display staff emails
    echo "<h3>Staff Emails:</h3>";
    $q = "SELECT id, email FROM staff";
    $result = mysqli_query($dbc, $q);
    echo "<table border='1'>";
    echo "<tr><th>Staff ID</th><th>Email</th></tr>";
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['email']}</td></tr>";
    }
    echo "</table>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['admin_id']) && isset($_POST['admin_email'])) {
        update_email($dbc, 'admin', 'user_id', $_POST['admin_id'], $_POST['admin_email']);
    }
    if (isset($_POST['staff_id']) && isset($_POST['staff_email'])) {
        update_email($dbc, 'staff', 'id', $_POST['staff_id'], $_POST['staff_email']);
    }
}

// Display the form and current emails
echo "<!DOCTYPE html>
<html>
<head>
    <title>Update Email Addresses</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; }
        form { margin: 20px 0; }
        .form-group { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Update Email Addresses</h1>";

display_emails($dbc);

echo "<h2>Update Email Addresses</h2>
    <form method='post'>
        <div class='form-group'>
            <h3>Update Admin Email</h3>
            <label>Admin ID: <input type='number' name='admin_id' required></label><br>
            <label>New Email: <input type='email' name='admin_email' required></label>
        </div>
        
        <div class='form-group'>
            <h3>Update Staff Email</h3>
            <label>Staff ID: <input type='number' name='staff_id' required></label><br>
            <label>New Email: <input type='email' name='staff_email' required></label>
        </div>
        
        <input type='submit' value='Update Emails'>
    </form>
</body>
</html>";
?> 
