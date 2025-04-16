<?php
ob_start();
session_start(); // Start the session.

// If no session value is present, redirect the user:
if (!isset($_SESSION['user_id'])) {
    // Need the functions:
    require('includes/login_functions.inc.php');
    redirect_user();    
}

$page_title = 'Edit Reservation Details';
include('includes/header.html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation Details</title>
    <link rel="stylesheet" href="includes/editUser.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            display: none;
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border: 1px solid #d6e9c6;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function(){
            // Show success message if it exists
            <?php if (isset($_SESSION['success_message'])): ?>
                $("<div></div>").addClass("success-message").text("<?php echo $_SESSION['success_message']; ?>").prependTo("body");
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            // Get value on input function
            $("#price, #qty, #contact_no").on("input", function(){
                var value = $(this).val();
                // Check if the value is not a number
                if(isNaN(value)){
                    showPopup("Please enter a valid number.");
                    $(this).val("");
                }
            });

            // Email validation
            $("#email").on("blur", function(){
                var email = $(this).val();
                // Regular expression for email format
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!emailRegex.test(email)){
                    showPopup("Please enter a valid email address.");
                    $(this).val("");
                }
            });

            // Form validation on submit
            $("#editForm").on("submit", function(event){
                var errors = [];

                if($("select[name='occasion']").val() === '') {
                    errors.push('You forgot to select occasion.');
                }

                if($("input[name='event_date']").val() === '') {
                    errors.push('You forgot to select event date.');
                }

                if($("input[name='event_time']").val() === '') {
                    errors.push('You forgot to select event time.');
                }

                if($("input[name='budget']").val() === '') {
                    errors.push('You forgot to enter your budget.');
                }

                if($("input[name='num_pax']").val() === '') {
                    errors.push('You forgot to enter number of pax.');
                }

                if($("input[name='total_budget']").val() === '') {
                    errors.push('You forgot to enter your total budget.');
                }

                if($("textarea[name='event_address']").val() === '') {
                    errors.push('You forgot to fill in event address.');
                }

                if($("select[name='location']").val() === '') {
                    errors.push('You forgot to select location.');
                }

                if($("input[name='contact_person']").val() === '') {
                    errors.push('You forgot to fill in contact person.');
                }

                if($("input[name='contact_no']").val() === '') {
                    errors.push('You forgot to fill in contact number.');
                }

                if($("input[name='email']").val() === '') {
                    errors.push('You forgot to fill in email.');
                }

                if(errors.length > 0) {
                    event.preventDefault();
                    showPopup(errors.join('<br>'));
                }
            });

            // Function to show popup
            function showPopup(message) {
                // Create popup element
                var popup = $("<div></div>").addClass("popup").html(message);
                // Append popup to body
                $("body").append(popup);
                // Fade in popup
                popup.fadeIn();
                // After 2 seconds, fade out and remove popup
                setTimeout(function(){
                    popup.fadeOut(function(){
                        $(this).remove();
                    });
                }, 2000);
            }
        });
    </script>
</head>
<body>
<?php
// This page is for editing a user record.
// This page is accessed through view_users.php.

// Check for a valid user ID, through GET or POST:
if ( (isset($_GET['id'])) && (is_numeric($_GET['id'])) ) { // From view_users.php
    $id = $_GET['id'];
} elseif ( (isset($_POST['id'])) && (is_numeric($_POST['id'])) ) { // Form submission.
    $id = $_POST['id'];
} else { // No valid ID, kill the script.
    echo '<p class="error">This page has been accessed in error.</p>';
    include ('includes/footer.html'); 
    exit();
}

require ('mysqli_connect.php'); 

// Check if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = array(); // Collect error message
    
    // Check for an occasion:
    if (empty($_POST['occasion'])) {
        $errors[] = 'You forgot to select occasion.';
    } else {
        $occ = trim($_POST['occasion']);
    }
    
    // Check for an event date:
    if (empty($_POST['event_date'])) {
        $errors[] = 'You forgot to select event date.';
    } else {
        $ed = trim($_POST['event_date']);
    }
    
    // Check for an event time:
    if (empty($_POST['event_time'])) {
        $errors[] = 'You forgot to select event time.';
    } else {
        $et = trim($_POST['event_time']);
    }

    // Check for a budget:
    if (empty($_POST['budget'])) {
        $errors[] = 'You forgot to enter your budget.';
    } else {
        $b = trim($_POST['budget']);
    }

    // Check for number of pax:
    if (empty($_POST['num_pax'])) {
        $errors[] = 'You forgot to enter number of pax.';
    } else {
        $npax = trim($_POST['num_pax']);
    }

    // Check for a total budget:
    if (empty($_POST['total_budget'])) {
        $errors[] = 'You forgot to enter your total budget.';
    } else {
        $tb = trim($_POST['total_budget']);
    }

    // Check for an event address:
    if (empty($_POST['event_address'])) {
        $errors[] = 'You forgot to fill in event address.';
    } else {
        $ea = trim($_POST['event_address']);
    }

    // Check for a location:
    if (empty($_POST['location'])) {
        $errors[] = 'You forgot to select location.';
    } else {
        $loc = trim($_POST['location']);
    }

    // Check for a contact person:
    if (empty($_POST['contact_person'])) {
        $errors[] = 'You forgot to fill in contact person.';
    } else {
        $cp = trim($_POST['contact_person']);
    }
    
    // Check for a contact number:
    if (empty($_POST['contact_no'])) {
        $errors[] = 'You forgot to fill in contact number.';
    } else {
        $cnum = trim($_POST['contact_no']);
    }

    // Check for an email:
    if (empty($_POST['email'])) {
        $errors[] = 'You forgot to fill in email.';
    } else {
        $e = trim($_POST['email']);
    }

    // Can be empty. Not compulsory to enter.
    $sr = trim($_POST['special_req']);

    if (empty($errors)) { // If everything's OK.

        // Make the query:
        $q = "UPDATE orders SET occasion='$occ', event_date='$ed', event_time='$et', budget='$b', num_pax='$npax', total_budget='$tb', event_address='$ea', location='$loc', contact_person='$cp', contact_no='$cnum', email='$e', special_req='$sr' WHERE order_id=$id LIMIT 1";
        $r = @mysqli_query ($dbc, $q);

        if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.

            // Set success message and redirect:
            $_SESSION['success_message'] = 'Update Successful!';
            header('Location: edit_user.php?id=' . $id);
            exit();
                
        } else { // If it did not run OK.
            echo '<p class="error">The user could not be edited due to a system error. We apologize for any inconvenience.</p>'; // Public message.
            //echo '<p>' . mysqli_error($dbc) . '<br />Query: ' . $q . '</p>'; // Debugging message.
        }
                
    } else { // Report the errors.

        echo '<p class="error">The following error(s) occurred:<br />';
        foreach ($errors as $msg) { // Print each error.
            echo " - $msg<br />\n";
        }
        echo '</p><p class="error" >Please try again.</p>';

    } // End of if (empty($errors)) IF.

} // End of submit conditional.

// Always show the form...

// Retrieve the user's information:
$q = "SELECT occasion, event_date, event_time, budget, num_pax, total_budget, event_address, location, contact_person, contact_no, email, special_req FROM orders WHERE order_id=$id";        
$r = @mysqli_query ($dbc, $q);

if (mysqli_num_rows($r) == 1) { // Valid user ID, show the form.

    // Get the user's information:
    $row = mysqli_fetch_array ($r, MYSQLI_NUM);
    
    ?>

    <div class="wrapper">
    <?php
    
    // Create the form:
    echo '<form action="edit_user.php" method="post" id="editForm">

    <p>Occasion: <select name="occasion">';
        
    $occasions = array('','Company Event', 'Happy Birthday Event', 'Wedding Event');
    foreach ($occasions as $occasion) {
        echo "<option value=\"$occasion\" " . ($row[0] == $occasion ? 'selected' : '') . ">$occasion</option>\n";
    }
        
    echo '</select></p>
    <p>Event Date: <input type="date" name="event_date" size="15" maxlength="40" value="' . $row[1] . '"/></p>
    <p>Event Time: <input type="time" name="event_time" size="20" maxlength="60" value="' . $row[2] . '"/></p>
    <p>Budget/Pax (RM): <input type="text" id="price" name="budget" size="20" maxlength="60" value="' . $row[3] . '"/></p>
    <p>Number of Pax: <input type="text" id="qty" name="num_pax" size="20" maxlength="60" value="' . $row[4] . '"/></p>
    <p>Total Budget (RM): <input type="text" id="total_budget" disabled value="' . $row[5] . '"/>
    <input type="hidden" name="total_budget" id="hidden_total_budget" value="' . $row[5] . '"/></p>
    <p>Event Address: <br><textarea name="event_address" style="width:100px; height:60px;">' . $row[6] . '</textarea></p>
    <p>Location: <select name="location">';
        
    $locations = array('','Kuala Lumpur', 'Selangor');
    foreach ($locations as $location) {
        echo "<option value=\"$location\" " . ($row[7] == $location ? 'selected' : '') . ">$location</option>\n";
    }
        
    echo '</select></p>
    <p>Contact Person: <input type="text" name="contact_person" size="20" maxlength="60" value="' . $row[8] . '"/></p>
    <p>Contact Number: <input type="text" id="contact_no" name="contact_no" size="15" maxlength="40" value="' . $row[9] . '" /></p>
    <p>Email: <input type="email" name="email" id="email" size="15" maxlength="50" value="' . $row[10] . '" /></p>
    <p>Special Request: <br><textarea name="special_req" style="width:100px; height:60px;">' . $row[11] . '</textarea></p> 
    <p><input type="submit" name="submit" value="Update" /></p>
    <input type="hidden" name="id" value="' . $id . '" />
    </form>
    <br>
    <a href=view_customer.php><input type="submit" name="submit" value="Back" /></a>';
} else { // Not a valid user ID.
    echo '<p class="error">This page has been accessed in error.</p>';
}
?>
</div>
</body>
</html>
<?php
mysqli_close($dbc);
        
include ('includes/footer.html');
ob_end_flush();
?>

<script>
    $(document).ready(function(){
        // Get value on keyup function
        $("#price, #qty").keyup(function(){
            var total=0;        
            var x = Number($("#price").val());
            var y = Number($("#qty").val());
            var total= "RM"+x * y;  

            $('#total_budget, #hidden_total_budget').val(total);
        });
    });
</script>
