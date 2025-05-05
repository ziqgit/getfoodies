<?php
// Include database connection
require_once "mysqli_connect.php";

// Read the SQL file
$sql = file_get_contents('create_staff_tables.sql');

// Execute multi query
if (mysqli_multi_query($dbc, $sql)) {
    echo "Tables created successfully!<br>";
    echo "Default staff account created:<br>";
    echo "Email: staff@catering.com<br>";
    echo "Password: staff123<br>";
} else {
    echo "Error creating tables: " . mysqli_error($dbc);
}

// Close connection
mysqli_close($dbc);
?> 
