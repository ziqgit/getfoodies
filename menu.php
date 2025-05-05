<FilesMatch "\.(php)$">
    Header set Content-Security-Policy "frame-ancestors 'none';"
</FilesMatch>
<html>
  <?php
        $page_title = 'Menu';
        include ('includes/header.html');
      
  ?> 
<head>
  <link rel="stylesheet" href="includes/menu.css">
</head>
<body>
<h1>Menu</h1>
<div class="row">
  <h2>Company Event</h2>
  <div class="column">
    <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf1();">
    <div class="menuImage">
        <img src="includes/images/company1.jpg" alt="company 1">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf1() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/catering1.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>

    <p>Asian or Western Seminar</p>
  </div>
  <div class="column">
      <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf2();">
    <div class="menuImage">
        <img src="includes/images/company2.jpg" alt="company 1">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>
<script>
    function openPdf2() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/catering2.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
<p>Brand Hi Tea</p>

    
  </div>
  <div class="column">
     <!-- Add this around your image in menu.php -->
  <a href="" onclick="openPdf3();">
    <div class="menuImage">
        <img src="includes/images/company3.jpg" alt="company 1">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
  </a>
<script>
    function openPdf3() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/catering3.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
  
    <p>Breakfast and Tea</p>
</div>
</div>
<!-------------------------------------------WEDDING------------------------------------------------------>

<div class="row2">
  <h2>Wedding</h2>
  <div class="column">
    <!-- Add this around your image in menu.php -->
    <a href="" onclick="openPdf4();">
      <div class="menuImage">
        <img src="includes/images/wed.jpg" alt="wedding 1">
        <div class="hov">
          <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
          <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
      </div>
    </a>

    <script>
      function openPdf4() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/wedding1.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
      }
    </script>

    <p>Western Wedding</p>
  </div>

  <div class="column">
    <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf5();">
    <div class="menuImage">
        <img src="includes/images/wed2.jpg" alt="wedding 2">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf5() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/wedding2.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>

    <p>Malay Wedding</p>
  </div>
  <div class="column">
    <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf6();">
    <div class="menuImage">
        <img src="includes/images/wed3.jpg" alt="wedding 3">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf6() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/wedding3.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
    <p>Chinese Buffet</p>
</div>
</div>

<!--------------------------------------------BIRTHDAY--------------------------------------------------------------->
<div class="row2">
<h2>Birthday Party</h2>
<div class="column">
     <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf7();">
    <div class="menuImage">
        <img src="includes/images/birth.jpg" alt="birthday 1">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf7() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/bf1.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
    <p>Lada Merah </p>
  </div>
  <div class="column">
     <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf8();">
    <div class="menuImage">
        <img src="includes/images/birth2.jpg" alt="birthday 2">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf8() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/bf2.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
    <p>Deli Delights</p>
  </div>
  <div class="column">
     <!-- Add this around your image in menu.php -->
<a href="" onclick="openPdf9();">
    <div class="menuImage">
        <img src="includes/images/bbq.jpg" alt="birthday 3">
        <div class="hov">
            <h1>Click to See Menu</h1>
        </div>
        <div class="text-box">
            <a href="req_quotation_form.php" class="but but-white">Free Quotation</a>
        </div>
    </div>
</a>

<script>
    function openPdf9() {
        // Replace 'your_pdf_file.pdf' with the actual path to your PDF file
        var pdfPath = 'includes/pdf/bf3.pdf';

        // Open the PDF file in a new window or tab
        window.open(pdfPath, '_blank');
    }
</script>
    <p>Joe Roast Lamb</p>
</div>
</div>

</body>
<?php include ('includes/footer.html'); ?>
</html>
