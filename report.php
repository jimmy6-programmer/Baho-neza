<?php
// Connect to the database
$servername = "localhost"; // Change as needed
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "baho-neza"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$reportData = [];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_report'])) {
    $raw_name = $_POST['raw_name']; // Sanitize input

    // Query for Raw Material by Name
    $rawMaterialQuery = "SELECT * FROM raw_material WHERE name = ?";
    $stmt = $conn->prepare($rawMaterialQuery);
    $stmt->bind_param("s", $raw_name);
    $stmt->execute();
    $rawMaterialResult = $stmt->get_result();
    $reportData['raw_material'] = $rawMaterialResult->fetch_assoc();

    if ($reportData['raw_material']) {
        $raw_id = $reportData['raw_material']['raw_id'];

        // Query for Extraction
        $extractionQuery = "SELECT * FROM extraction WHERE raw_id = ?";
        $stmt = $conn->prepare($extractionQuery);
        $stmt->bind_param("i", $raw_id);
        $stmt->execute();
        $extractionResult = $stmt->get_result();
        $reportData['extraction'] = $extractionResult->fetch_all(MYSQLI_ASSOC);

        // Query for Milled Product
        $milledProductQuery = "SELECT * FROM milled_product WHERE raw_id = ?";
        $stmt = $conn->prepare($milledProductQuery);
        $stmt->bind_param("i", $raw_id);
        $stmt->execute();
        $milledProductResult = $stmt->get_result();
        $reportData['milled_product'] = $milledProductResult->fetch_all(MYSQLI_ASSOC);

        // Query for Final Product
        $finalProductQuery = "SELECT fp.*, rm.name AS raw_name FROM final_product fp 
                              JOIN milled_product mp ON fp.mill_id = mp.mill_id 
                              JOIN raw_material rm ON rm.raw_id = mp.raw_id 
                              WHERE rm.name = ?";
        $stmt = $conn->prepare($finalProductQuery);
        $stmt->bind_param("s", $raw_name);
        $stmt->execute();
        $finalProductResult = $stmt->get_result();
        $reportData['final_product'] = $finalProductResult->fetch_all(MYSQLI_ASSOC);

        // Query for Shipment
        $shipmentQuery = "SELECT * FROM shipment JOIN final_product fp ON shipment.final_pro_id = fp.final_pro_id 
                          WHERE fp.raw_id = ?";
        $stmt = $conn->prepare($shipmentQuery);
        $stmt->bind_param("i", $raw_id);
        $stmt->execute();
        $shipmentResult = $stmt->get_result();
        $reportData['shipment'] = $shipmentResult->fetch_all(MYSQLI_ASSOC);

        // Query for Sales
        $salesQuery = "SELECT * FROM sales JOIN shipment ON sales.ship_id = shipment.ship_id 
                       JOIN final_product fp ON shipment.final_pro_id = fp.final_pro_id 
                       WHERE fp.raw_id = ?";
        $stmt = $conn->prepare($salesQuery);
        $stmt->bind_param("i", $raw_id);
        $stmt->execute();
        $salesResult = $stmt->get_result();
        $reportData['sales'] = $salesResult->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional: Link to your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <h1>Generate Financial Report</h1>
    <form method="POST" action="">
        <label for="raw_name">Select Raw Material Name:</label>
        <input type="text" id="raw_name" name="raw_name" required>
        <button type="submit" name="generate_report">Generate Report</button>
    </form>

    <?php if (isset($reportData['raw_material'])): ?>
        <h2>Financial Report for Raw Material: <?php echo htmlspecialchars($reportData['raw_material']['name']); ?></h2>

        <h3>Raw Material Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Imported Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Import Date</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($reportData['raw_material']['raw_id']); ?></td>
                <td><?php echo htmlspecialchars($reportData['raw_material']['name']); ?></td>
                <td><?php echo htmlspecialchars($reportData['raw_material']['imported_quantity']); ?></td>
                <td><?php echo htmlspecialchars($reportData['raw_material']['unit_price']); ?></td>
                <td><?php echo htmlspecialchars($reportData['raw_material']['total_price']); ?></td>
                <td><?php echo htmlspecialchars($reportData['raw_material']['import_date']); ?></td>
            </tr>
        </table>

        <h3>Extraction Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Extracted Quantity</th>
                <th>Remaining Quantity</th>
                <th>Disposed Quantity</th>
                <th>Date</th>
            </tr>
            <?php foreach ($reportData['extraction'] as $extraction): ?>
                <tr>
                    <td><?php echo htmlspecialchars($extraction['ext_id']); ?></td>
                    <td><?php echo htmlspecialchars($extraction['extracted_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($extraction['remaining_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($extraction['disposed_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($extraction['date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Milled Product Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Process</th>
            </tr>
            <?php foreach ($reportData['milled_product'] as $milled): ?>
                <tr>
                    <td><?php echo htmlspecialchars($milled['mill_id']); ?></td>
                    <td><?php echo htmlspecialchars($milled['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($milled['date']); ?></td>
                    <td><?php echo htmlspecialchars($milled['process']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Final Product Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Product Type</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Date</th>
                <th>Derived From Raw Material</th>
            </tr>
            <?php foreach ($reportData['final_product'] as $final): ?>
                <tr>
                    <td><?php echo htmlspecialchars($final['final_pro_id']); ?></td>
                    <td><?php echo htmlspecialchars($final['product_type']); ?></td>
                    <td><?php echo htmlspecialchars($final['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($final['unit_price']); ?></td>
                    <td><?php echo htmlspecialchars($final['total_price']); ?></td>
                    <td><?php echo htmlspecialchars($final['date']); ?></td>
                    <td><?php echo htmlspecialchars($final['raw_name']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Shipment Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Destination</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Ship Date</th>
            </tr>
            <?php foreach ($reportData['shipment'] as $shipment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($shipment['ship_id']); ?></td>
                    <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                    <td><?php echo htmlspecialchars($shipment['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($shipment['total_price']); ?></td>
                    <td><?php echo htmlspecialchars($shipment['ship_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Sales Details:</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Quantity Sold</th>
                <th>Remaining Quantity</th>
                <th>Sale Date</th>
                <th>Destination</th>
            </tr>
            <?php foreach ($reportData['sales'] as $sale): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                    <td><?php echo htmlspecialchars($sale['quantity_sold']); ?></td>
                    <td><?php echo htmlspecialchars($sale['remaining_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                    <td><?php echo htmlspecialchars($sale['destination']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
