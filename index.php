<?php
header('X-Frame-Options: DENY');
 header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none' frame-ancestors 'none'; form-action 'self'; base-uri 'self';");

        $page_title = 'Homepage';
        include ('includes/header.html');
        
?> 
<html>
    
<head>
    <link rel="stylesheet" href="includes/homepage.css">
</head>
<body class="homeBody">
    <div class="content">
        <h2><font color="gold"> Best in Kuala Lumpur & Selangor </font></h2>
        <h1>Catering Services</h1>
        <h4>Wedding Event, Birthday Party, Company Event</h4>
        <?php
            echo "<p>Today is " . date("Y-m-d") . "</p>";
        ?>
    </div>
    <div class="background-slider"></div>

    <?php include ('includes/footer.html'); ?>
</body>

</html>
