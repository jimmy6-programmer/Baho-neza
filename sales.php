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
    $product_name = $_POST['product_name'];
    $quantity_sold = $_POST['quantity_sold'];
    $sale_date = $_POST['sale_date'];
    $destination = $_POST['destination'];

    // Fetch ship_id based on product_name and destination
    $stmt = $db->prepare("
        SELECT shipment.ship_id, shipment.quantity, final_product.product_type 
        FROM shipment 
        JOIN final_product ON shipment.final_pro_id = final_product.final_pro_id 
        WHERE final_product.product_type = ? AND shipment.destination = ?
    ");
    $stmt->bind_param("ss", $product_name, $destination);
    $stmt->execute();
    $shipment = $stmt->get_result()->fetch_assoc();

    if ($shipment) {
        $ship_id = $shipment['ship_id'];
        $remaining_quantity = $shipment['quantity'] - $quantity_sold;

        if ($remaining_quantity < 0) {
            $mssg = "Entered quantity exceeds the available quantity in shipment";
        } else {
            if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
                // Update the existing record
                $update_id = $_POST['update_id'];
                $stmt = $db->prepare("
                    UPDATE sales 
                    SET ship_id=?, quantity_sold=?, remaining_quantity=?, sale_date=?, destination=? 
                    WHERE sale_id=?
                ");
                $stmt->bind_param("iiiisi", $ship_id, $quantity_sold, $remaining_quantity, $sale_date, $destination, $update_id);
                if ($stmt->execute()) {
                    $mssg = "Sale Updated Successfully";
                    header('location:sales.php');
                } else {
                    $mssg = "Failed to Update Sale";
                }
                $stmt->close();
            } else {
                // Insert a new record
                $stmt = $db->prepare("
                    INSERT INTO sales (ship_id, quantity_sold, remaining_quantity, sale_date, destination) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiiss", $ship_id, $quantity_sold, $remaining_quantity, $sale_date, $destination);
                if ($stmt->execute()) {
                    // Update the remaining quantity in the shipment table
                    $db->query("UPDATE shipment SET quantity=$remaining_quantity WHERE ship_id=$ship_id AND destination='$destination'");
                    $mssg = "Sale Added Successfully";
                    header('location:sales.php');
                } else {
                    $mssg = "Failed to Add Sale";
                }
                $stmt->close();
            }
        }
    } else {
        $mssg = "No shipment found for the selected product and destination";
    }
}

// Get the data for update if needed
$update_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $db->query("SELECT * FROM sales WHERE sale_id=$edit_id");
    $update_data = $result->fetch_assoc();
}

// Fetch shipments and corresponding products for the dropdown
$products = $db->query("
    SELECT final_product.product_type 
    FROM shipment 
    JOIN final_product ON shipment.final_pro_id = final_product.final_pro_id
    GROUP BY final_product.product_type
");

// Fetch distinct destinations for the dropdown
$destinations = $db->query("SELECT DISTINCT destination FROM shipment");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px; /* Set the desired height */
            overflow-y: auto; /* Enable vertical scrolling */
            position: relative;
            margin-left: 40px;
            width: 800px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container thead th {
            position: sticky;
            top: 0;
            background: #fff; /* or the background color of your table header */
            z-index: 10;
        }
        .add-form {
            height: 480px;
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
                    <li class="nav-item"><a class="nav-link" href="final_product.php">Final Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="shipment.php">Shipment</a></li>
                    <li class="nav-item"><a class="nav-link text-light" href="sales.php">Sales</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php" style="color:rgba(211, 25, 25, 0.765);"><i class="fas fa-user" style="margin-left:120px;color:red;"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navbar ends here -->

    <div class="form-table">
        <div class="add-form">
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE SALE' : 'ADD SALE'; ?></h4><br>
            <p class="success-mssg"><?php echo $mssg; ?></p>
            <script>
                setTimeout(() => {
                    document.querySelector(".success-mssg").style.display = 'none';
                }, 3000);
            </script>
            <form action="" method="post" class="record-form">
                <label for="product_name" class="labels">Select Shipped Product</label>
                <select name="product_name" id="product_name" class="form-control" required>
    <?php 
    while ($product = $products->fetch_assoc()) { 
        $selected = '';

        // Fetch the corresponding shipment data for update scenario
        if (isset($update_data)) {
            $stmt = $db->prepare("
                SELECT shipment.ship_id, shipment.quantity, final_product.product_type 
                FROM shipment 
                JOIN final_product ON shipment.final_pro_id = final_product.final_pro_id 
                WHERE shipment.ship_id = ?
            ");
            $stmt->bind_param("i", $update_data['ship_id']);
            $stmt->execute();
            $shipment = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($shipment && $shipment['product_type'] == $product['product_type']) {
                $selected = 'selected';
            }
        }
    ?> 
        <option value="<?php echo $product['product_type']; ?>" <?php echo $selected; ?>>
            <?php echo $product['product_type']; ?>
        </option>
    <?php } ?>
</select>

                <label for="destination" class="labels">Select Destination</label>
                <select name="destination" id="destination" class="form-control" required>
    <?php 
    while ($destination = $destinations->fetch_assoc()) { 
        $selected = isset($update_data) && $update_data['destination'] == $destination['destination'] ? 'selected' : '';
        echo "<option value='{$destination['destination']}' $selected>{$destination['destination']}</option>";
    }
    ?>
</select>

                <label for="quantity_sold" class="labels">Quantity Sold</label>
                <input type="number" name="quantity_sold" id="quantity_sold" value="<?php echo $update_data['quantity_sold'] ?? ''; ?>" placeholder="Enter quantity sold..." class="form-control" required><br>
                <input type="hidden" name="update_id" value="<?php echo $update_data['sale_id'] ?? ''; ?>">
                <input type="date" name="sale_date" value="<?php echo $update_data['sale_date'] ?? ''; ?>" placeholder="Sold At..." class="form-control" required><br>
                <input type="submit" class="add-product-btn" value="<?php echo isset($update_data) ? 'Update Sale' : 'Add Sale'; ?>">
            </form>
        </div>
        <!-- Real Table -->

        <div class="table-data">
            <div class="table-responsive table-container mt-3">
                <table class="table table-success table-hover">
                    <thead>
                        <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Destination</th>
                        <th>Quantity Sold</th>
                        <th>Remaining Quantity</th>
                        <th>Sold At</th>
                        <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $sales = $db->query("
                        SELECT sales.sale_id, sales.ship_id, sales.quantity_sold, sales.remaining_quantity, sales.sale_date, 
                        final_product.product_type, shipment.destination 
                        FROM sales 
                        JOIN shipment ON sales.ship_id = shipment.ship_id 
                        JOIN final_product ON shipment.final_pro_id = final_product.final_pro_id 
                        ORDER BY sales.sale_id DESC
                    ");
                    $counter = 1;
                    while ($sale = $sales->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $sale['product_type']; ?></td>
                            <td><?php echo $sale['destination']; ?></td>
                            <td><?php echo $sale['quantity_sold']; ?></td>
                            <td><?php echo $sale['remaining_quantity']; ?></td>
                            <td><?php echo $sale['sale_date']; ?></td>
                            <td>
                                <a href="sales.php?edit_id=<?php echo $sale['sale_id']; ?>"><i class="fas fa-pen"></i></a>
                                <a href="sales.php?del_id=<?php echo $sale['sale_id']; ?>"><i class="fas fa-trash-can" style="color:red;margin-left:10px;"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Real Table -->
    </div>

    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>


