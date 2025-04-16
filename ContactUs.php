<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: cornsilk;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header1 {
            color: MidnightBlue;
            text-align: center;
            padding: 20px;
        }

        section {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .contact-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .contact-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .contact-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .contact-form button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .contact-info h2 {
            border-bottom: 2px solid #ccc;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .info-box div {
            margin-bottom: 12px;
        }

        .map-section .map-container {
            overflow: hidden;
            border-radius: 8px;
        }

        .map-container img {
            width: 100%;
            border-radius: 8px;
            transition: transform 0.3s ease-in-out;
        }

        .map-container img:hover {
            transform: scale(1.05);
        }

        .wrapper0{
            min-height: 740px;
        }

        .wrapper {
    border-style: dashed;
    max-width: 20%;
    margin-left: auto;
    margin-right: auto;
    padding-left: 16px;
    padding-right: 16px;
    padding-top: 10px;
    padding-bottom: 10px;
    position: relative;    
    top: 250px;
    text-align: center;
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0, 0.4); /* Black w/opacity/see-through */
    color: white;
    font-weight: bold;
    border: 3px solid #f1f1f1;
    z-index: 2;
}
       
    </style>
</head>
<body>
<?php
// ContactUs.php

$page_title = 'Contact Us';
include('includes/header.html');

// Check for form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = array(); // Initialize an error array.

    // Check for a first name:
    if (empty($_POST['first_name'])) {
        $errors[] = 'You forgot to enter your first name.';
    } else {
        $fn = trim($_POST['first_name']);
    }

    // Check for a last name:
    if (empty($_POST['last_name'])) {
        $errors[] = 'You forgot to enter your last name.';
    } else {
        $ln = trim($_POST['last_name']);
    }

    // Check for an email:
    if (empty($_POST['email'])) {
        $errors[] = 'You forgot to enter your email.';
    } else {
        $e = trim($_POST['email']);
    }

    // Check for a phone no:
    if (empty($_POST['phone_no'])) {
        $errors[] = 'You forgot to enter your phone no.';
    } else {
        $pn = trim($_POST['phone_no']);
    }

    // Check for a message:
    if (empty($_POST['Message'])) {
        $errors[] = 'You forgot to enter your message.';
    } else {
        $m = trim($_POST['Message']);
    }

    if (empty($errors)) { // If everything's OK.

        // Register the user in the database...
        require ('../mysqli_connect.php'); // Connect to the db.

        // Make the query:
        $q = "INSERT INTO message (first_name, last_name, email, phone_no, Message,Send_date) 
              VALUES ('$fn', '$ln', '$e', '$pn', '$m', NOW())";
        $r = mysqli_query($dbc, $q); // Run the query.
        
        ?>
        <div class="wrapper0">
        <div class="wrapper">
        <?php
        if ($r) { // If it ran OK.

            // Print a message:
            echo '<h1>Thank you!</h1>
                  <p>Your message has been sent!</p><p><br /></p>
                  <a href="ContactUs.php"><p ><input class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="back to Contact Us" /></p></a>';

        } else { // If it did not run OK.

            // Public message:
            echo '<h1>System Error</h1>
                  <p class="error">You could not be registered due to a system error. We apologize for any inconvenience.</p>';

            // Debugging message:
            echo '<p>' . mysqli_error($dbc) . '<br /><br />Query: ' . $q . '</p>';

        } // End of if ($r) IF.

        mysqli_close($dbc); // Close the database connection.
        ?>
        </div>
        </div>
        <?php

        // Include the footer and quit the script:
        include ('includes/footer.html');
        exit();

    } else { // Report the errors.

        echo '<h1>Error!</h1>
              <p class="error">The following error(s) occurred:<br />';
        foreach ($errors as $msg) { // Print each error.
            echo " - $msg<br />\n";
        }
        echo '</p><p>Please try again.</p><p><br /></p>';

    } // End of if (empty($errors)) IF.

} // End of the main Submit conditional.
?>	
<header class="header1">
    <h1>Contact Us</h1>
</header>

<section class="contact-form">
    <h2>Get in Touch</h2>
    <form action="ContactUs.php" method="post" class="contact-form">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" placeholder="Your First Name" value="<?php if (isset($_POST['first_name'])) echo htmlspecialchars($_POST['first_name']); ?>" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" placeholder="Your Last Name" value="<?php if (isset($_POST['last_name'])) echo htmlspecialchars($_POST['last_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Your Email" value="<?php if (isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>" required>

        <label for="phone_no">Phone No:</label>
        <input type="tel" name="phone_no" id="phone_no" placeholder="Your Phone No" value="<?php if (isset($_POST['phone_no'])) echo htmlspecialchars($_POST['phone_no']); ?>" required>

        <label for="Message">Message:</label>
        <textarea name="Message" id="Message" placeholder="Your Message" rows="4" required><?php if (isset($_POST['Message'])) echo htmlspecialchars($_POST['Message']); ?></textarea>

        <button type="submit" name="submit">Send Message</button>
    </form>
</section>

<section class="contact-info">
    <h2>Contact Information</h2>
    <div class="info-box">
        <div>
            <ion-icon name="location"></ion-icon>
            <p>Universiti Kuala Lumpur - Malaysian Institute of Information Technology (UniKL MIIT)</p>
        </div>
        <div>
            <h4>Imran :</h4>
            <p class="mail">Email: imran@gmail.com
            <p class="phone">Phone: 01110032552</p>
        </div>
        <div>
		<h4>Amir :</h4>
            <p class="mail">Email: amirencem@gmail.com</p>
            <p class="phone">Phone: 0143514098</p>       
        </div>
        <div>
        <h4>Izz Danial :</h4>
            <p class="mail">Email: izzdanial23@gmail.com </p>
            <p class="phone">Phone: 0132363192</p>   
        </div>
</section>

<section class="map-section">
    <h2>Location</h2>			
    <!-- MAP -->
                <div class="contact map">
                    <p><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.7544240091006!2d101.69886637598353!3d3.159308053088031!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4828d251e21f%3A0xf2c96e953e48a8a4!2sUniversiti%20Kuala%20Lumpur%20-%20Malaysian%20Institute%20of%20Information%20Technology%20(UniKL%20MIIT)!5e0!3m2!1sen!2smy!4v1705676640479!5m2!1sen!2smy" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></p>
                </div>
            </div>
        </div>
</section>

<?php
include('includes/footer.html');
?>

</body>
</html>
