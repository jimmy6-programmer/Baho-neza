<?php
session_start();
include 'config.php';
$mssg = "";

// Check for session message
if (isset($_SESSION['mssg'])) {
    $mssg = $_SESSION['mssg'];
    unset($_SESSION['mssg']);
}

if (!empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    header('location:index.php');
}

// Initialize the updated rows array in session if not already set
if (!isset($_SESSION['updated_rows'])) {
    $_SESSION['updated_rows'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $quantity = $_POST['imported_quantity'];
    $u_price = $_POST['unit_price'];
    $total = $quantity * $u_price;
    $current_date = date('Y-m-d'); // Set the current date

    if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
        // Check the current update count
        $update_id = $_POST['update_id'];
        $result = $db->query("SELECT update_count FROM raw_material WHERE raw_id=$update_id");
        $row = $result->fetch_assoc();

        if ($row['update_count'] < 2) {
            // Update the existing record
            $stmt = $db->prepare("UPDATE raw_material SET name=?, imported_quantity=?, unit_price=?, total_price=?, import_date=?, update_count=update_count+1 WHERE raw_id=?");
            $stmt->bind_param("siddsi", $name, $quantity, $u_price, $total, $current_date, $update_id);
            if ($stmt->execute()) {
                $_SESSION['mssg'] = "Product Updated";
                
                // Add the updated row ID to the session array
                $_SESSION['updated_rows'][] = $update_id;

                header('location:raw_material.php');
                exit();
            } else {
                $mssg = "Failed to Update Product";
            }
            $stmt->close();
        } else {
            header('location:raw_material.php');
        }
    } else {
        // Insert a new record
        $stmt = $db->prepare("INSERT INTO raw_material (name, imported_quantity, unit_price, total_price, import_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidds", $name, $quantity, $u_price, $total, $current_date);
        if ($stmt->execute()) {
            $_SESSION['mssg'] = "Product Added";
            header('location:raw_material.php');
            exit();
        } else {
            $mssg = "Failed to Add Product";
        }
        $stmt->close();
    }
}

// Get the data for update if needed
$update_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $db->query("SELECT * FROM raw_material WHERE raw_id=$edit_id");
    $update_data = $result->fetch_assoc();
}

// Get the current date
$current_date = date('Y-m-d');
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Material Management</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
            margin-left:40px;
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
            height: 450px;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
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
                    <li class="nav-item"><a class="nav-link text-light" href="#">Raw Materials</a></li>
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

    <div class="form-table">
        <div class="add-form">
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE PRODUCT' : 'ADD PRODUCT'; ?></h4><br>
            <p class="success-mssg"><?php echo $mssg; ?></p>
            <script>
                setTimeout(() => {
                    document.querySelector(".success-mssg").style.display = 'none';
                }, 3000);
            </script>
            <form action="" method="post" class="record-form">
                <label for="" class="labels">Name Of The Product</label>
                <input type="text" name="name" value="<?php echo $update_data['name'] ?? ''; ?>" placeholder="Enter a product name..." class="form-control" required>
                <label for="" class="labels">Imported quantity</label>
                <input type="number" name="imported_quantity" value="<?php echo $update_data['imported_quantity'] ?? ''; ?>" placeholder="Enter quantity(KG)..." class="form-control" required>
                <label for="" class="labels">Unit Price</label>
                <input type="number" name="unit_price" value="<?php echo $update_data['unit_price'] ?? ''; ?>" placeholder="Enter a product unit price..." class="form-control" required><br>
                <label for="" class="labels">Import Date</label>
                <input type="text" name="import_date" value="<?php echo isset($update_data) ? $update_data['import_date'] : $current_date; ?>" class="form-control" readonly><br>
                <?php if (isset($update_data)) { ?>
                    <input type="hidden" name="update_id" value="<?php echo $update_data['raw_id']; ?>">
                <?php } ?>
                <input type="submit" class="add-product-btn" value="<?php echo isset($update_data) ? 'Update Product' : 'Add Product'; ?>">
            </form>
        </div>
        <div class="table-data">
            <div class="table-responsive table-container mt-3">
                <table class="table table-success table-hover">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Imported Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Import Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qry = $db->query("SELECT * FROM raw_material");
                        while ($row = $qry->fetch_assoc()) {
                            $updated = '';
                            if (in_array($row['raw_id'], $_SESSION['updated_rows'])) {
                                $updated = ' (updated)';
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['name'] . $updated; ?></td>
                                <td><?php echo $row['imported_quantity']; ?></td>
                                <td><?php echo $row['unit_price']; ?></td>
                                <td><?php echo $row['total_price']; ?></td>
                                <td><?php echo $row['import_date']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
