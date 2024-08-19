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
    $raw_id = $_POST['raw_id'];
    $extracted_quantity = $_POST['extracted_quantity'];
    $remaining_quantity = $_POST['remaining_quantity'];
    $date = date('Y-m-d'); // This will be used to check the update count

    // Calculate disposed quantity
    $disposed_quantity = $extracted_quantity - $remaining_quantity;

    // Fetch the imported quantity for validation
    $result = $db->query("SELECT imported_quantity FROM raw_material WHERE raw_id=$raw_id");
    $raw_material = $result->fetch_assoc();
    $imported_quantity = $raw_material['imported_quantity'];

    if ($extracted_quantity > $imported_quantity) {
        $mssg = "Inserted quantity exceeds the available quantity";
    } else {
        if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
            // Update the existing record
            $update_id = $_POST['update_id'];

            // Fetch the current update count and original date
            $result = $db->query("SELECT update_count, date FROM extraction WHERE ext_id=$update_id");
            $row = $result->fetch_assoc();
            $update_count = $row['update_count'];
            $original_date = $row['date'];

            if ($update_count >= 2) {
                $mssg = "This row has already been updated twice. No further updates allowed.";
                header('refresh:3;url=extraction.php');
                exit();
            }

            $update_count++; // Increment update count

            $stmt = $db->prepare("UPDATE extraction SET raw_id=?, extracted_quantity=?, remaining_quantity=?, disposed_quantity=?, update_count=? WHERE ext_id=?");
            $stmt->bind_param("idddsi", $raw_id, $extracted_quantity, $remaining_quantity, $disposed_quantity, $update_count, $update_id);
            if ($stmt->execute()) {
                $mssg = "Extraction Updated";
                header('location:extraction.php');
                exit();
            } else {
                $mssg = "Failed to Update Extraction";
            }
            $stmt->close();
        } else {
            // Insert a new record
            $stmt = $db->prepare("INSERT INTO extraction (raw_id, extracted_quantity, remaining_quantity, disposed_quantity, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iddds", $raw_id, $extracted_quantity, $remaining_quantity, $disposed_quantity, $date);
            if ($stmt->execute()) {
                $mssg = "Extraction Added";
                header('location:extraction.php');
                exit();
            } else {
                $mssg = "Failed to Add Extraction";
            }
            $stmt->close();
        }
    }
}

// Get the data for update if needed
$update_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $db->query("SELECT * FROM extraction WHERE ext_id=$edit_id");
    $update_data = $result->fetch_assoc();
}

// Fetch raw materials for the dropdown
$raw_materials = $db->query("SELECT * FROM raw_material");
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extraction</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px; /* Set the desired height */
            overflow-y: auto; /* Enable vertical scrolling */
            position: relative;
            margin-left:30px;
            width: 830px;
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
        .updated {
            color: green;
            font-weight: bold;
        }
    </style>
    <script>
        function updateDisposedQuantity() {
            const extractedQuantity = parseFloat(document.getElementById('extracted_quantity').value) || 0;
            const remainingQuantity = parseFloat(document.getElementById('remaining_quantity').value) || 0;
            const disposedQuantity = extractedQuantity - remainingQuantity;
            document.getElementById('disposed_quantity').value = disposedQuantity;
        }
    </script>
</head>
<body>
    <!-- Navbar starts here -->
    <nav class="navbar navbar-expand-sm navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">BAHO-NEZA FOOD LTD</a>
            <div class="collapse navbar-collapse mx-5" id="mynavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="raw_material.php">Raw Materials</a></li>
                    <li class="nav-item"><a class="nav-link text-light" href="extraction.php">Extraction</a></li>
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
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE EXTRACTION' : 'ADD EXTRACTION'; ?></h4><br>
            <p class="success-mssg"><?php echo $mssg; ?></p>
            <script>
                setTimeout(() => {
                    document.querySelector(".success-mssg").style.display = 'none';
                }, 3000);
            </script>
            <form action="" method="post" class="record-form">
                <label for="raw_id" class="labels">Select Raw Material</label>
                <select name="raw_id" class="form-control" required>
                    <option value="">Enter raw material</option>
                    <?php while ($raw = $raw_materials->fetch_assoc()) { ?>
                        <option value="<?php echo $raw['raw_id']; ?>" <?php if (isset($update_data) && $update_data['raw_id'] == $raw['raw_id']) echo 'selected'; ?>>
                            <?php echo $raw['name']; ?>
                        </option>
                    <?php } ?>
                </select>
                <label for="extracted_quantity" class="labels">Extracted Quantity</label>
                <input type="number" id="extracted_quantity" name="extracted_quantity" value="<?php echo $update_data['extracted_quantity'] ?? ''; ?>" placeholder="Enter extracted quantity..." class="form-control" required oninput="updateDisposedQuantity()">
                <label for="remaining_quantity" class="labels">Remaining Quantity</label>
                <input type="number" id="remaining_quantity" name="remaining_quantity" value="<?php echo $update_data['remaining_quantity'] ?? ''; ?>" placeholder="Enter remaining quantity..." class="form-control" required oninput="updateDisposedQuantity()">
                <label for="disposed_quantity" class="labels">Disposed Quantity</label>
                <input type="number" id="disposed_quantity" name="disposed_quantity" value="<?php echo $update_data['disposed_quantity'] ?? ''; ?>" placeholder="Disposed quantity..." class="form-control" readonly><br>
                <label for="date" class="labels">Extraction Date</label>
                <input type="date" name="date" value="<?php echo isset($update_data) ? $update_data['date'] : date('Y-m-d'); ?>" class="form-control" readonly><br>
                <?php if (isset($update_data)) { ?>
                    <input type="hidden" name="update_id" value="<?php echo $update_data['ext_id']; ?>">
                <?php } ?>
                <input type="submit" class="add-product-btn" value="<?php echo isset($update_data) ? 'Update Extraction' : 'Add Extraction'; ?>">
            </form>
        </div>
        <div class="table-data">
            <div class="table-responsive table-container mt-3">
                <table class="table table-success table-hover">
                    <thead>
                        <tr>
                            <th>Raw Material</th>
                            <th>Extracted (Q)</th>
                            <th>Remaining (Q)</th>
                            <th>Disposed (Q)</th>
                            <th>Date</th>
                            <th colspan="2">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qry = $db->query("SELECT extraction.*, raw_material.name FROM extraction JOIN raw_material ON extraction.raw_id = raw_material.raw_id");
                        while ($row = $qry->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['extracted_quantity']; ?></td>
                                <td><?php echo $row['remaining_quantity']; ?></td>
                                <td><?php echo $row['disposed_quantity']; ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td>
                                    <a href="?edit_id=<?php echo $row['ext_id']; ?>" style="margin-left:10px;"><i class="fas fa-pen"></i></a>
                                    <a href="delete_extraction.php?ext_id=<?php echo $row['ext_id']; ?>" style="margin-left:20px;color:red;"><i class="fas fa-trash-can"></i></a>
                                    <?php if ($row['update_count'] > 0) { ?>
                                        <span class="updated">(updated)</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="bootstrap.min.js"></script>
</body>
</html>
