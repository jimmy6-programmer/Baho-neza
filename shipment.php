<?php
session_start();
include 'config.php';
$mssg = "";

if (!empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    header('location:index.php');
}

// Handle form submission for adding or updating
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $final_pro_id = $_POST['final_pro_id'];
    $destination = $_POST['destination'];
    $quantity = $_POST['quantity'];
    $date = date('Y-m-d');

    // Fetch the final product details for validation
    $result = $db->query("SELECT quantity, unit_price FROM final_product WHERE final_pro_id=$final_pro_id");
    $final_product = $result->fetch_assoc();
    $final_quantity = $final_product['quantity'];
    $unit_price = $final_product['unit_price'];

    // Calculate total price
    $total_price = $quantity * $unit_price;

    if ($quantity > $final_quantity) {
        $mssg = "Entered quantity exceeds the available quantity in the final product.";
    } else {
        if (isset($_POST['ship_id'])) {
            // Handle update
            $ship_id = $_POST['ship_id'];
            $current_update_count = $_POST['update_count'];

            if ($current_update_count < 2) {
                // Update shipment
                $db->query("UPDATE shipment SET final_pro_id=$final_pro_id, destination='$destination', quantity=$quantity, total_price=$total_price, ship_date='$date', update_count=update_count+1 WHERE ship_id=$ship_id");

                $remaining_quantity = $final_quantity - $quantity;
                $mssg = "Shipment updated successfully. Remaining quantity: " . $remaining_quantity;
                header('location:shipment.php');
            } else {
                $mssg = "This shipment has already been updated twice and cannot be updated further.";
                header('location:shipment.php');
            }
        } else {
            // Handle insert
            $db->query("INSERT INTO shipment (final_pro_id, destination, quantity, total_price, ship_date, update_count) VALUES ($final_pro_id, '$destination', $quantity, $total_price, '$date', 0)");

            $remaining_quantity = $final_quantity - $quantity;
            $mssg = "Shipment added successfully. Remaining quantity: " . $remaining_quantity;
        }
    }
}

// Fetch shipment data and calculate the rest quantity
$shipments = $db->query("
    SELECT shipment.ship_id, final_product.product_type, shipment.destination, 
    shipment.quantity, shipment.total_price, shipment.ship_date, 
    (final_product.quantity - shipment.quantity) as rest, 
    shipment.update_count
    FROM shipment 
    JOIN final_product ON shipment.final_pro_id = final_product.final_pro_id
");

if (!$shipments) {
    die("Error executing query: " . $db->error);
}

// Fetch final products
$final_products = $db->query("SELECT final_pro_id, product_type, quantity, unit_price FROM final_product");

// Fetch shipment data if updating
$update_data = null;
$update_count = 0;
if (isset($_GET['edit'])) {
    $ship_id = $_GET['edit'];
    $update_data = $db->query("SELECT * FROM shipment WHERE ship_id=$ship_id")->fetch_assoc();

    // Get the number of times this shipment has been updated
    $update_count = $update_data['update_count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
            margin-left: 10px;
            width: 800px;
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
        }
        .updated {
            color: green;
            font-weight: bold;
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
                    <li class="nav-item"><a class="nav-link text-light" href="shipment.php">Shipment</a></li>
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
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE SHIPMENT' : 'ADD SHIPMENT'; ?></h4><br>
            <p class="success-mssg"><?php echo $mssg; ?></p>
            <script>
                setTimeout(() => {
                    document.querySelector(".success-mssg").style.display = 'none';
                }, 3000);
            </script>
            <form action="" method="post" class="record-form">
                <?php if (isset($update_data)) { ?>
                    <input type="hidden" name="ship_id" value="<?php echo $update_data['ship_id']; ?>">
                    <input type="hidden" name="update_count" value="<?php echo $update_count; ?>">
                <?php } ?>
                
                <label for="final_pro_id" class="labels">Select Final Product</label>
                <select name="final_pro_id" class="form-control" required>
                    <option value="">Select Final Product - Quantity</option>
                    <?php while ($product = $final_products->fetch_assoc()) { ?> 
                        <option value="<?php echo $product['final_pro_id']; ?>" <?php if (isset($update_data) && $update_data['final_pro_id'] == $product['final_pro_id']) echo 'selected'; ?>>
                            <?php echo $product['product_type'] . " - Quantity: " . $product['quantity']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="destination" class="labels">Select Destination</label>
                <select name="destination" class="form-control" required>
                    <option value="Nyamata" <?php if (isset($update_data) && $update_data['destination'] == 'Nyamata') echo 'selected'; ?>>Nyamata</option>
                    <option value="Kimironko" <?php if (isset($update_data) && $update_data['destination'] == 'Kimironko') echo 'selected'; ?>>Kimironko</option>
                </select>

                <label for="quantity" class="labels">Quantity</label>
                <input type="number" name="quantity" placeholder="Enter quantity..." class="form-control" value="<?php echo isset($update_data) ? $update_data['quantity'] : ''; ?>" required>

                <label for="date" class="labels">Date</label>
                <input type="text" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" readonly><br>

                <input type="submit" class="add-product-btn" value="<?php echo isset($update_data) ? 'Update Shipment' : 'Add Shipment'; ?>">
            </form>
        </div>
        <div class="table-data">
            <div class="table-responsive table-container mt-3">
                <table class="table table-success table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Destination</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Ship Date</th>
                            <th>Rest</th>
                            <th>Updates</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($shipment = $shipments->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $shipment['product_type']; ?></td>
                                <td><?php echo $shipment['destination']; ?></td>
                                <td><?php echo $shipment['quantity']; ?></td>
                                <td><?php echo $shipment['total_price']; ?></td>
                                <td><?php echo $shipment['ship_date']; ?></td>
                                <td><?php echo $shipment['rest']; ?></td>
                                <td><?php echo $shipment['update_count']; ?></td>
                                <td>
                                    <a href="shipment.php?edit=<?php echo $shipment['ship_id']; ?>">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </td>
                                <?php if ($shipment['update_count'] > 0) { ?>
                                    <td class="updated">(updated)</td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
