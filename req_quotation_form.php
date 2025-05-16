<?php
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none';");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://maxcdn.bootstrapcdn.com; style-src 'self' 'unsafe-inline' https://maxcdn.bootstrapcdn.com; img-src 'self' data: https:; font-src 'self' https://maxcdn.bootstrapcdn.com; connect-src 'self'; frame-src 'self'; object-src 'none';");

// Start session and generate CSRF token if not set
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<html>
<head>
    <link rel="stylesheet" href="includes/req_quotation_form.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<?php 
require("script.php");
$page_title = 'Free Quotation';
include ('includes/header.html');
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF validation failed.');
    }
    $errors = array();

    // Debug: Output all POST data
    echo '<pre>POST DATA: ' . print_r($_POST, true) . '</pre>';

    // Check required fields
    $required_fields = ['occasion', 'event_date', 'event_time', 'budget', 'num_pax', 'event_address', 'location', 'contact_person', 'contact_no', 'email'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "You forgot to fill in $field.";
        } else {
            $$field = trim($_POST[$field]);
        }
    }

    // Optional fields
    $special_req = trim($_POST['special_req'] ?? '');
    $promo_code = trim($_POST['promo_code'] ?? '');
    $subscribe = isset($_POST['subscribe']) ? 'Yes' : 'No';
    $company_name = trim($_POST['company_name'] ?? '');

    // Validate numerical fields
    if (!empty($budget) && !is_numeric($budget)) {
        $errors[] = "Budget must be a number.";
    }
    if (!empty($num_pax) && !is_numeric($num_pax)) {
        $errors[] = "Number of Pax must be a number.";
    }
    if (!empty($contact_no) && !is_numeric($contact_no)) {
        $errors[] = "Contact Number must be a number.";
    }

    // Validate email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Debug: Output validation errors
    if (!empty($errors)) {
        echo '<pre>VALIDATION ERRORS: ' . print_r($errors, true) . '</pre>';
    }

    // Calculate total budget
    if (empty($errors)) {
        $total_budget = $budget * $num_pax;
        $total_budget = number_format($total_budget, 2);

        // Database connection
        require('mysqli_connect.php');

        // Insert query
        $q = "INSERT INTO orders (company_name, occasion, event_date, event_time, budget, registration_date, total_budget, num_pax, event_address, location, contact_person, contact_no, email, special_req, promo_code, subscribe) VALUES ('$company_name', '$occasion', '$event_date', '$event_time', '$budget', NOW(), '$total_budget', '$num_pax', '$event_address', '$location', '$contact_person', '$contact_no', '$email', '$special_req', '$promo_code', '$subscribe')";
        $r = mysqli_query($dbc, $q);

        // Debug: Output SQL query and MySQL error
        echo '<pre>SQL QUERY: ' . $q . '</pre>';
        if (!$r) {
            echo '<pre>MYSQL ERROR: ' . mysqli_error($dbc) . '</pre>';
        }

        if ($r) {
            // Send email
            $message = "Here are your event details:\n
                        Occasion: $occasion\n
                        Event Date: $event_date\n
                        Event Time: $event_time\n
                        Event Address: $event_address\n
                        Location: $location\n
                        Budget/Pax: RM$budget\n
                        Number of Pax: $num_pax\n
                        Total Budget: RM$total_budget\n
                        Contact Person: $contact_person\n
                        Contact Number: $contact_no\n
                        Email: $email\n
                        Special Request: $special_req\n
                        Promo Code: $promo_code\n
                        Subscribe: $subscribe";
            $response = sendMailSendGrid($email, "Quotation Details", nl2br($message));

            // Debug: Output mail response
            echo '<pre>MAIL RESPONSE: ' . print_r($response, true) . '</pre>';

            echo '<div class="wrapper1">';
            echo '<h1>Thank you!</h1>';
            echo '<h2>You are now registered!</h2>';
            echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            echo '<p>-----------------------------------------------------------------------</p>';
            echo '<p>Thank you for registering with us. We will contact you soon.</p>';
            if ($response == "success") {
                echo '<p class="success">Email has been sent successfully.</p>';
            } else {
                echo "<p class=\"error\">$response</p>";
            }
            echo '</div>';

        } else {
            echo '<h1>System Error</h1>';
            echo '<p class="error">You could not be registered due to a system error. We apologize for any inconvenience.</p>';
            echo '<p>' . mysqli_error($dbc) . '<br /><br />Query: ' . $q . '</p>';
        }
        mysqli_close($dbc);
        include('includes/footer.html');
        exit();
    } else {
        // Generate a JavaScript alert with error messages
        echo '<script type="text/javascript">';
        echo 'alert(' . json_encode("The following error(s) occurred:\n" . implode("\n - ", $errors) . "\nPlease try again.") . ');';
        echo '</script>';
    }
}
?>

<script src="jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $("#price, #qty").keyup(function() {
        var total = 0;
        var x = Number($("#price").val());
        var y = Number($("#qty").val());
        var total = "RM" + x * y;
        $('#total_budget').val(total);
        $('#hidden_total_budget').val(x * y);
    });
});
</script>

<div class="wrapper">
    <h1>Enquire Now! Request FREE Quote</h1>
    <form action="req_quotation_form.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="column2">
            <h2>Event Details</h2>
            <p>Occasion:
                <?php
                $occasion = array('', 'Company Event', 'Happy Birthday Event', 'Wedding Event');
                $selected_occasion = isset($_POST['occasion']) ? htmlspecialchars($_POST['occasion'], ENT_QUOTES, 'UTF-8') : '';
                echo '<select name="occasion">';
                foreach ($occasion as $key => $value) {
                    $safe_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    $selected = ($selected_occasion === $safe_value) ? 'selected' : '';
                    echo "<option value=\"$safe_value\" $selected>$safe_value</option>\n";
                }
                echo '</select>';
                ?>
            </p>
            <p>Event Date: <input type="date" name="event_date" size="15" maxlength="40" value="<?php if (isset($_POST['event_date'])) echo htmlspecialchars($_POST['event_date'], ENT_QUOTES, 'UTF-8'); ?>" />
                Event Time: <input type="time" name="event_time" size="20" maxlength="60" value="<?php if (isset($_POST['event_time'])) echo htmlspecialchars($_POST['event_time'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
        </div>
        <div class="column2">
            <p>Budget/Pax (RM): <input type="text" id="price" name="budget" size="20" maxlength="60" value="<?php if (isset($_POST['budget'])) echo htmlspecialchars($_POST['budget'], ENT_QUOTES, 'UTF-8'); ?>" />
                Number of Pax: <input type="text" id="qty" name="num_pax" size="20" maxlength="60" value="<?php if (isset($_POST['num_pax'])) echo htmlspecialchars($_POST['num_pax'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
            <p class="tb">Total Budget (RM): <input type="text" placeholder="Total Budget" id="total_budget" disabled />
                <input type="hidden" name="total_budget" id="hidden_total_budget" value="<?php if (isset($_POST['total_budget'])) echo htmlspecialchars($_POST['total_budget'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
        </div>
        <div class="column2">
            <p>Event Address: <br><textarea name="event_address" placeholder="Your Event Address"><?php if (isset($_POST['event_address'])) echo htmlspecialchars($_POST['event_address'], ENT_QUOTES, 'UTF-8'); ?></textarea></p>
            <p>Location:
                <?php
                $location = array('', 'Kuala Lumpur', 'Selangor');
                $selected_location = isset($_POST['location']) ? htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8') : '';
                echo '<select name="location">';
                foreach ($location as $key => $value) {
                    $safe_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    $selected = ($selected_location === $safe_value) ? 'selected' : '';
                    echo "<option value=\"$safe_value\" $selected>$safe_value</option>\n";
                }
                echo '</select>';
                ?>
            </p>
            <p>Contact Person: <input type="text" name="contact_person" size="20" placeholder="Your Name" maxlength="60" value="<?php if (isset($_POST['contact_person'])) echo htmlspecialchars($_POST['contact_person'], ENT_QUOTES, 'UTF-8'); ?>" />
                Contact Number: <input type="text" name="contact_no" size="20" placeholder="Your Phone Number" maxlength="60" value="<?php if (isset($_POST['contact_no'])) echo htmlspecialchars($_POST['contact_no'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
            <center><p>Email: <input type="text" name="email" size="30" maxlength="60" placeholder="Your Email" value="<?php if (isset($_POST['email'])) echo htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
        </div>
        <div class="column3">
            <p>Special Request: <br><textarea name="special_req" placeholder="exp: Kambing golek nak garing."><?php if (isset($_POST['special_req'])) echo htmlspecialchars($_POST['special_req'], ENT_QUOTES, 'UTF-8'); ?></textarea></p>
            <p>Promo Code: <input type="text" name="promo_code" size="20" maxlength="60" value="<?php if (isset($_POST['promo_code'])) echo htmlspecialchars($_POST['promo_code'], ENT_QUOTES, 'UTF-8'); ?>" />
            </p>
            <div class="checkbox-wrapper-19">
                <p>Subscribe to our newsletter <input type="checkbox" id="cbtest-19" name="subscribe" size="20" maxlength="60" value="Yes" /> 
                <label for="cbtest-19" class="check-box"></p>
            </div>
        </div>
        <p>Company Name: <input type="text" name="company_name" size="30" maxlength="30" value="<?php if (isset($_POST['company_name'])) echo htmlspecialchars($_POST['company_name'], ENT_QUOTES, 'UTF-8'); ?>" /></p>
        <p class="textsubmit"><input type="submit" name="submit" value="Submit for FREE Quote" /></p>
    </form>
</div>

<?php include('includes/footer.html'); ?>
</body>
</html>
