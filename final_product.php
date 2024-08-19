<?php
session_start();
include 'config.php';
$mssg = "";

if (!empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    header('location:index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mill_id = $_POST['mill_id'];
    $process = $_POST['process'];
    $product_type = $_POST['product_type'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $total_price = $quantity * $unit_price;
    $date = date('Y-m-d');

    // Fetch the milled quantity for validation
    $result = $db->query("SELECT quantity FROM milled_product WHERE mill_id=$mill_id");
    $milled_product = $result->fetch_assoc();
    $milled_quantity = $milled_product['quantity'];

    if ($quantity > $milled_quantity) {
        $mssg = "Entered quantity exceeds the milled quantity";
        header('location:final_product.php');
    } else {
        if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
            // Check update count
            $update_id = $_POST['update_id'];
            $result = $db->query("SELECT update_count FROM final_product WHERE final_pro_id=$update_id");
            $final_product = $result->fetch_assoc();

            if ($final_product['update_count'] >= 2) {
                $mssg = "You cannot update this product more than two times.";
                header('location:final_product.php');
            } else {
                // Update the existing record
                $stmt = $db->prepare("UPDATE final_product SET mill_id=?, process=?, product_type=?, quantity=?, unit_price=?, total_price=?, date=?, update_count=update_count+1 WHERE final_pro_id=?");
                $stmt->bind_param("issdidis", $mill_id, $process, $product_type, $quantity, $unit_price, $total_price, $date, $update_id);
                if ($stmt->execute()) {
                    $mssg = "Final Product Updated";
                    header('location:final_product.php');
                } else {
                    $mssg = "Failed to Update Final Product";
                }
                $stmt->close();
            }
        } else {
            // Insert a new record
            $stmt = $db->prepare("INSERT INTO final_product (mill_id, process, product_type, quantity, unit_price, total_price, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdids", $mill_id, $process, $product_type, $quantity, $unit_price, $total_price, $date);
            if ($stmt->execute()) {
                $mssg = "Final Product Added";
                header('location:final_product.php');
            } else {
                $mssg = "Failed to Add Final Product";
            }
            $stmt->close();
        }
    }
}

// Get the data for update if needed
$update_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $db->query("SELECT * FROM final_product WHERE final_pro_id=$edit_id");
    $update_data = $result->fetch_assoc();
}

// Fetch milled products along with the raw material name, process, and quantity
$milled_products = $db->query("
    SELECT milled_product.mill_id, milled_product.quantity, milled_product.process, raw_material.name
    FROM milled_product
    JOIN raw_material ON milled_product.raw_id = raw_material.raw_id
");

?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Product</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
            margin-left: 20px;
            width: 840px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
        }
        .add-form {
            height: 480px;
            margin-left:10px;
            width: 400px;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link text-light" href="final_product.php">Final Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="shipment.php">Shipment</a></li>
                    <li class="nav-item"><a class="nav-link" href="sales.php">Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php" style="color:rgba(211, 25, 25, 0.765);"><i class="fas fa-user" style="margin-left:120px;color:red;"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar ends here -->

    <div class="form-table">
        <div class="add-form">
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE FINAL PRODUCT' : 'ADD FINAL PRODUCT'; ?></h4><br>
            <p class="success-mssg"><?php echo $mssg; ?></p>
            <script>
                setTimeout(() => {
                    document.querySelector(".success-mssg").style.display = 'none';
                }, 3000);
            </script>
            <form action="" method="post" class="record-form">
    <label for="mill_id" class="labels">Select Milled Product</label>
    <select name="mill_id" class="form-control" required>
    <option value="">Select Milled Product - Process - Quantity</option>
        <?php while ($mill = $milled_products->fetch_assoc()) { ?> 
            <option value="<?php echo $mill['mill_id']; ?>" <?php if (isset($update_data) && $update_data['mill_id'] == $mill['mill_id']) echo 'selected'; ?>>
                <?php echo $mill['name'] . " - Process: " . $mill['process'] . " - Quantity: " . $mill['quantity']; ?>
            </option>
        <?php } ?>
    </select>
    <!-- Hidden field to store the selected process -->
    <input type="hidden" name="process" value="<?php echo isset($mill) ? $mill['process'] : ''; ?>">
    <!-- Other input fields -->
    <label for="product_type" class="labels">Product Type</label>
    <input type="text" name="product_type" value="<?php echo $update_data['product_type'] ?? ''; ?>" placeholder="Enter product type..." class="form-control" required>
    <label for="quantity" class="labels">Quantity</label>
    <input type="number" name="quantity" value="<?php echo $update_data['quantity'] ?? ''; ?>" placeholder="Enter quantity..." class="form-control" required>
    <label for="unit_price" class="labels">Unit Price</label>
    <input type="number" name="unit_price" value="<?php echo $update_data['unit_price'] ?? ''; ?>" placeholder="Enter unit price..." class="form-control" required><br>
    <?php if (isset($update_data)) { ?>
        <input type="hidden" name="update_id" value="<?php echo $update_data['final_pro_id']; ?>">
    <?php } ?>
    <input type="submit" class="add-product-btn" value="<?php echo isset($update_data) ? 'Update Final Product' : 'Add Final Product'; ?>">
</form>

        </div>
        <div class="table-data">
    <div class="table-responsive table-container mt-3">
        <table class="table table-success table-hover">
            <thead>
                <tr>
                    <th>Processed Product</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>(U)Price</th>
                    <th>Total</th>
                    <th colspan="2">Operations</th>
                </tr>
            </thead>
            <tbody>
    <?php
    // Fetch final products along with the name of the milled product and process
    $qry = $db->query("
        SELECT final_product.*, milled_product.quantity AS milled_quantity, raw_material.name AS milled_product_name, milled_product.process AS milled_process
        FROM final_product
        JOIN milled_product ON final_product.mill_id = milled_product.mill_id
        JOIN raw_material ON milled_product.raw_id = raw_material.raw_id
    ");
    while ($row = $qry->fetch_assoc()) {
        $update_message = ($row['update_count'] > 0) ? " (Updated)" : "";
    ?>
        
        <tr>
            <td><?php echo $row['milled_product_name'] . " - Process: " . $row['milled_process'] . " - Quantity: " . $row['milled_quantity']; ?></td>
            <td><?php echo $row['product_type']; ?></td>
            <td><?php echo $row['quantity']; ?></td>    
            <td><?php echo $row['unit_price']; ?></td>
            <td><?php echo $row['total_price']; ?></td>
            <td><a href="final_product.php?edit_id=<?php echo $row['final_pro_id']; ?>"><i class="fas fa-pen" style="margin-left:20px;"><span><small style="color:green;"><?php echo $update_message; ?></small></span></i></a></td>
        </tr>
    <?php } ?>
</tbody>

        </table>
    </div>
</div>

    </div>
</body>
</html>
