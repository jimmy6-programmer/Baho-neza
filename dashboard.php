<?php
session_start();
include 'config.php';
if (!empty($_SESSION['username'])) {
    $name = $_SESSION['username'];
}else{
    header('location:index.php');
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
</head>
<body>
      <!-- Navbar starts here -->
    <nav class="navbar navbar-expand-sm navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">BAHO-NEZA FOOD LTD</a>
            <div class="collapse navbar-collapse mx-5" id="mynavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="raw_material.php">Raw Materials</a></li>
                    <li class="nav-item"><a class="nav-link" href="extraction.php">Extraction</a></li>
                    <li class="nav-item"><a class="nav-link" href="milled.php">Processing</a></li>
                    <li class="nav-item"><a class="nav-link" href="final_product.php">Final Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="shipment.php">Shipment</a></li>
                    <li class="nav-item"><a class="nav-link" href="sales.php">Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php" style="color:rgba(211, 25, 25, 0.765);"><i class="fas fa-user" style="margin-left:120px;color:red;"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar ends here -->


<div class="dashboard-content">
    <div class="description">
        <div class="words">
        <h2>About us</h2>
        <p>
        Welcome to  <span class="baho">BAHONEZA FOOD LTD</span>, <br> your one-stop shop for premium flour products.  <br> We offer a wide variety of flours to meet all your  needs. <br> our high-quality flours ensure perfect results every time. <br> Experience the difference with our exceptional products today!</p>
        </div>
    </div>
    <div class="photo">
        
    </div>
</div>
<div class="footer">
    <p>Baho-neza food Ltd Operations System</p>
</div>
</body>
</html>