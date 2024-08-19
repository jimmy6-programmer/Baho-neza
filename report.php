<?php
require('fpdf/fpdf.php'); // Include FPDF library

// Database connection
include 'config.php';

// Fetch the product information
if (isset($_POST['raw_id'])) {
    $raw_id = $_POST['raw_id'];

    // Fetch the raw material details
    $raw_material_details = $db->query("
        SELECT * FROM raw_material
        WHERE raw_id = $raw_id
    ")->fetch_assoc();

    // Fetch all related records from various tables
    $extractions = $db->query("
        SELECT * FROM extraction
        WHERE raw_id = $raw_id
    ");

    $milled_products = $db->query("
        SELECT * FROM milled_product
        WHERE raw_id = $raw_id
    ");

    $shipments = $db->query("
        SELECT * FROM shipment
        WHERE final_pro_id = (
            SELECT final_pro_id FROM final_product WHERE raw_id = $raw_id
        )
    ");

    // Initialize PDF document
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Financial Report for ' . $raw_material_details['name'], 0, 1, 'C');

    // Raw Material details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Raw Material ID: ' . $raw_id, 0, 1);
    $pdf->Cell(0, 10, 'Raw Material Name: ' . $raw_material_details['name'], 0, 1);
    $pdf->Cell(0, 10, 'Available Quantity: ' . $raw_material_details['imported_quantity'], 0, 1);

    // Extraction Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Extractions', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    while ($extraction = $extractions->fetch_assoc()) {
        $pdf->Cell(0, 10, 'Extraction ID: ' . $extraction['ext_id'] . ' - Quantity: ' . $extraction['extracted_quantity'], 0, 1);
    }

    // Milled Products Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Milled Products', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    while ($milled_product = $milled_products->fetch_assoc()) {
        $pdf->Cell(0, 10, 'Mill ID: ' . $milled_product['mill_id'] . ' - Quantity: ' . $milled_product['quantity'], 0, 1);
    }

    // Shipments Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Shipments', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    while ($shipment = $shipments->fetch_assoc()) {
        $pdf->Cell(0, 10, 'Ship ID: ' . $shipment['ship_id'] . ' - Quantity: ' . $shipment['quantity'], 0, 1);
    }

    // Output the PDF
    $pdf->Output('I', 'Financial_Report_' . $raw_material_details['name'] . '.pdf');
    exit;
}

// Fetch raw materials for the form
$raw_materials = $db->query("SELECT raw_id, name FROM raw_material");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Financial Report</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Generate Financial Report</h2>
        <form method="post" action="">
            <label for="raw_id">Select Raw Material:</label>
            <select name="raw_id" class="form-control" required>
                <option value="">--Select Raw Material--</option>
                <?php while ($material = $raw_materials->fetch_assoc()) { ?>
                    <option value="<?php echo $material['raw_id']; ?>"><?php echo $material['name']; ?></option>
                <?php } ?>
            </select>
            <br>
            <input type="submit" class="btn btn-primary" value="Generate Report">
        </form>
    </div>
</body>
</html>
