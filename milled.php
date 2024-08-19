<?php
session_start();
include 'config.php';
$mssg = "";

// Check if user is logged in
if (!empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    header('location:index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['raw_id']) && !empty($_POST['raw_id'])) {
        $ext_id = $_POST['raw_id']; // This is the ext_id from the extraction table
        $process = $_POST['process'];
        $quantity = $_POST['quantity'];
        $date = date('Y-m-d'); // This will be used to record the processed date

        // Fetch raw_id and remaining_quantity from the extraction table
        $result = $db->query("SELECT remaining_quantity, raw_id FROM extraction WHERE ext_id=$ext_id");
        
        if ($result && $result->num_rows > 0) {
            $extraction = $result->fetch_assoc();
            $remaining_quantity = $extraction['remaining_quantity'];
            $raw_id = $extraction['raw_id']; // Get the raw_id

            // Calculate the new remaining quantity
            $new_remaining_quantity = $remaining_quantity - $quantity;

            if ($quantity > $remaining_quantity) {
                $mssg = "Inserted quantity exceeds the available remaining quantity";
            } else {
                if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
                    // Update the existing record
                    $update_id = $_POST['update_id'];

                    // Check the current update count
                    $result = $db->query("SELECT update_count FROM milled_product WHERE mill_id=$update_id");
                    $row = $result->fetch_assoc();
                    $update_count = $row['update_count'];

                    if ($update_count >= 2) {
                        $mssg = "Update limit exceeded for this record.";
                        header('location:milled.php');
                    } else {
                        // Increment update count and update the record
                        $update_count++;
                        $stmt = $db->prepare("UPDATE milled_product SET raw_id=?, process=?, quantity=?, date=?, ext_id=?, update_count=? WHERE mill_id=?");
                        $stmt->bind_param("isdsiid", $raw_id, $process, $quantity, $date, $ext_id, $update_count, $update_id);
                        if ($stmt->execute()) {
                            $mssg = "Milled Product Updated";
                            header('location:milled.php');
                            exit();
                        } else {
                            $mssg = "Failed to Update Milled Product";
                        }
                        $stmt->close();
                    }
                } else {
                    // Insert a new record
                    $stmt = $db->prepare("INSERT INTO milled_product (raw_id, process, quantity, date, ext_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("isdsi", $raw_id, $process, $quantity, $date, $ext_id);
                    if ($stmt->execute()) {
                        $mssg = "Milled Product Added";
                        header('location:milled.php');
                        exit();
                    } else {
                        $mssg = "Failed to Add Milled Product";
                    }
                    $stmt->close();
                }
            }
        } else {
            $mssg = "No matching record found in extraction table for the selected raw material.";
            if ($result) {
                $mssg .= " Query returned no results.";
            } else {
                $mssg .= " Query failed: " . $db->error;
            }
        }
    } else {
        $mssg = "Please select a raw material.";
    }
}

// Get the data for update if needed
$update_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $db->query("SELECT * FROM milled_product WHERE mill_id=$edit_id");
    $update_data = $result->fetch_assoc();
}

// Fetch raw materials for the dropdown
$raw_materials = $db->query("
    SELECT rm.raw_id, rm.name, e.ext_id, e.remaining_quantity 
    FROM raw_material rm
    JOIN extraction e ON rm.raw_id = e.raw_id
    WHERE e.remaining_quantity > 0
");
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milled Products</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Font-Awesome-6.x/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
            margin-left:10px;
            width: 830px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top:50px;
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
                    <li class="nav-item"><a class="nav-link text-light" href="milled.php">Processing</a></li>
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
            <h4 class="form-head"><?php echo isset($update_data) ? 'UPDATE PROCESSING' : 'PROCESSING'; ?></h4><br>
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
                        <option value="<?php echo $raw['ext_id']; ?>" <?php if (isset($update_data) && $update_data['raw_id'] == $raw['raw_id']) echo 'selected'; ?>>
                            <?php echo $raw['name'] . " (Remaining: " . $raw['remaining_quantity'] . ")"; ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="process" class="labels">Select Process</label>
                <select name="process" class="form-control" required>
                    <option value="">Select Process</option>
                    <option value="Milled" <?php if (isset($update_data) && $update_data['process'] == 'Milled') echo 'selected'; ?>>Mill</option>
                    <option value="Fried" <?php if (isset($update_data) && $update_data['process'] == 'Fried') echo 'selected'; ?>>Fry</option>
                    <option value="Unused" <?php if (isset($update_data) && $update_data['process'] == 'Unused') echo 'selected'; ?>>Unused</option>
                </select>
                <label for="quantity" class="labels">Quantity</label>
                <input type="number" name="quantity" value="<?php echo $update_data['quantity'] ?? ''; ?>" placeholder="Enter quantity..." class="form-control" required>
                <label for="date" class="labels">Processed At (Date)</label>
                <input type="date" name="date" value="<?php echo isset($update_data) ? $update_data['date'] : date('Y-m-d'); ?>" class="form-control" disabled>
                <input type="hidden" name="update_id" value="<?php echo $update_data['mill_id'] ?? ''; ?>">
                <br>
                <input type="submit" value="<?php echo isset($update_data) ? 'Update' : 'Submit'; ?>" class="btn btn-success">
            </form>
        </div>

        <div class="table-container">
            <table class="table table-success table-hover">
                <thead>
                    <tr>
                        <th>Raw Material</th>
                        <th>Process</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Remaining Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->query("
                        SELECT mp.mill_id, rm.name, mp.process, mp.quantity, mp.date, e.remaining_quantity, mp.update_count 
                        FROM milled_product mp
                        JOIN extraction e ON mp.ext_id = e.ext_id
                        JOIN raw_material rm ON mp.raw_id = rm.raw_id
                    ");

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $calculated_remaining = $row['remaining_quantity'] - $row['quantity'];
                            $update_message = ($row['update_count'] > 0) ? "(Updated)" : "";
                            echo "<tr" . ($row['update_count'] > 0 ? " class='updated'" : "") . ">
                                    <td>{$row['name']}</td>
                                    <td>{$row['process']}</td>
                                    <td>{$row['quantity']}</td>
                                    <td>{$row['date']}</td>
                                    <td>Rest = {$calculated_remaining} {$update_message}</td>
                                    <td>
                                        <a href='milled.php?edit_id={$row['mill_id']}'><i class='fas fa-pen'></i></a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
