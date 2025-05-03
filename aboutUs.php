<?php
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none';");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://maxcdn.bootstrapcdn.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-src 'self'; script-src 'self';");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #333;
            background-color:cornsilk;
        }

        .aboutus{
            padding: 20px;
            text-align: center;
            color:MidnightBlue;
            
        }

        .about {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 20px;
        }

        .picture,
        .picture2,
        .picture3 {
            text-align: center;
            position: relative;
        }

        .image{
            width: 400px;
            height: 400px;
            border-radius: 1000%;
            margin-bottom: 10px;
            
            transition: transform 0.3s ease-in-out;
            object-fit: cover;
        }

        .image:hover {
            transform: scale(1.2);
        }

        .name,
        .name2,
        .name3 {
            margin: 10px 0 5px;
            font-size: 18px;
        }

        .studID,
        .studID2,
        .studID3 {
            color: #777;
            margin: 0;
        }


        p {
            line-height: 1.6;
            margin-bottom: 20px;
        }

        strong {
            color: #7F00FF;
        }

        .wrapper{
            margin-left: 50px;
        }
        .text1{
            margin-left: 50px;
        }
        .wrapper0{
            background-color: cornsilk;
            color:MidnightBlue;
            padding: 20px;
            text-align: center;
        }
        .aboutus2{
            background-color: cornsilk;
            color:MidnightBlue;
            padding: 5px;
            text-align: center;
        }

        p, .text1{
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            font-size:20px;
            text-align:center;
        }
    </style>
</head>

<body>

    <?php
    $page_title = 'About Us';
    include('includes/header.html');
    ?>

    <header class="aboutus">
        <h1>About Us</h1>
    </header>

    <p class="text1">Welcome to Get Foodies!, where your events become unforgettable! Whether it's an office party, wedding, birthday bash, or any special occasion, we offer the perfect catering solutions to meet your demands. Choose from a diverse range of caterers, and we guarantee delicious and uniquely crafted menus for your events.</p>
    
    <header class="aboutus2">
        <h1>Our Team</h1>
    </header>


    <div class="about">
        <div class="picture">
            <img class="image" src="includes/images/izz.jpg" alt="Izz Photo">
            <h3 class="name">AHMAD IZZ DANIAL BIN ABD RASHID</h3>
            <h4 class="studID">52213122114</h4>
        </div>
        <div class="picture2">
            <img class="image" src="includes/images/amir.jpg" alt="Amir Photo">
            <h3 class="name2">AMIR AMIRUL BIN MOHD KHAIRI</h3>
            <h4 class="studID2">52213122341</h4>
        </div>
        <div class="picture3">
            <img class="image" src="includes/images/imran.jpg" alt="Imran Photo">
            <h3 class="name3">IMRAN HAKIMI BIN ABU BAKAR</h3>
            <h4 class="studID3">52213122379</h4>
        </div>
    </div>

    <div class="wrapper0" align="center">
        <h1>Frequently Asked Questions - Get Foodies!</h1>
    </div>

    <div class="wrapper" align="left">
        <p>
            <strong>What is Get Foodies!?</strong><br>
            In Malaysia, Get Foodies! is the go-to platform for connecting locals with top-notch caterers. We have a wide variety of caterers available to meet your every demand, whether it's a wedding, a birthday party, an office party, or any other type of event. Choose from a plethora of caterers offering a dizzying selection of customizable meals!
        </p>

        <p>
            <strong>How Does It Work?</strong><br>
            Get the ball rolling by narrowing your search for caterers to those who meet your exact specifications in terms of price range, expected attendance, preferred cuisine, specialty dishes, and dietary restrictions. Submit your event details, contact info, and payment info when you've decided on a catered menu. After receiving your purchase confirmation email, the caterer will get in touch with you either immediately or within one to two business days to finalize the details.
        </p>

        <p>
            <strong>Is It Possible to customize a Catering Menu to Meet My Needs?</strong><br>
            Sure thing! To personalize your catering experience, go ahead and place your order. In the "Special Requests" section, write down any unique details you may have.
        </p>

        <p>
            <strong>Is Get Foodies!'s Order Processing Price Included?</strong><br>
            No, using Get Foodies! won't cost you any money.
        </p>

        <p>
            <strong>How Do I Make Payment for My Order?</strong><br>
            It is customary to pay the caterer directly when ordering catering services.
        </p>

        <p>
            <strong>What is the recommended minimum number of days to place an order?</strong><br>
            Caterers have different ordering lead times. A week's notice is required for all orders. To avoid disappointment during festive occasions, we recommend placing your order well in advance.
        </p>

        <p>
            <strong>My order was placed on Get Foodies!. What should I do if I want to change or cancel it?</strong><br>
            After the caterer confirms the order with you, contact them personally. The policy of each caterer governs the handling of adjustments and cancellations. Before you make any deposit payments, make sure you fully grasp these policies.
        </p>

        <p>
            <strong>When I decide to cancel my order, will there be any fees?</strong><br>
            No cancellation fees will be charged by Get Foodies!. However, depending on the caterer's policies, there may be cancellation fees. Before you make any payments, make sure you understand these policies completely.
        </p>
    </div>

    <?php
    include('includes/footer.html');
    ?>

</body>

</html>

