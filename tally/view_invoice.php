<?php
session_start();
include 'accountant.php';

// Check if the bill_no is provided
if (!isset($_GET['bill_no'])) {
    // Redirect to an error page or handle the error
    header("Location: error.php");
    exit();
}

// Get the bill_no from the GET parameters
$bill_no = $_GET['bill_no'];

// Function to get invoice details by bill_no
function getInvoiceDetails($conn, $bill_no) {
    // Implement the code to fetch invoice details
    $query = "SELECT pr.bill_no, pr.date, pr.supplier_name, pr.mobile_no, pr.paid_amount, pr.due_amount, pr.grand_total, pr.status, pp.product_name, pp.quantity, pp.price 
    FROM purchase_report pr
    JOIN purchased_product pp ON pr.bill_no = pp.bill_no
    WHERE pr.bill_no = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $bill_no);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Fetch invoice details by bill_no
$invoiceDetails = getInvoiceDetails($conn, $bill_no);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<a href="companymanager_dashboard.php" style="position: fixed; top: 20px; left: calc(80% + 100px);" class="btn btn-secondary">Back</a>

    <div class="container mt-5">
        <?php $supplierDetailsDisplayed = false; ?>
        <h2>Invoice Details</h2>
        <?php while ($row = $invoiceDetails->fetch_assoc()) : ?>
            <?php if (!$supplierDetailsDisplayed) : ?>
                <p><strong>Bill No:</strong> <?php echo $row['bill_no']; ?></p>
                <p><strong>Date:</strong> <?php echo $row['date']; ?></p>
                <p><strong>Supplier Name:</strong> <?php echo $row['supplier_name']; ?></p>
                <p><strong>Mobile No:</strong> <?php echo $row['mobile_no']; ?></p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price Per Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                        </tr>
            <?php $supplierDetailsDisplayed = true; ?>
            <?php else: ?>
                        <tr>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
            <!-- Display grand total, paid amount, due amount, and status inside the loop -->
            <div style="position: fixed; bottom: 0; left: 80%; transform: translateX(-50%);">
    <p><strong>Grand Total:</strong> <?php echo $row['grand_total']; ?></p>
    <p><strong>Paid Amount:</strong> <?php echo $row['paid_amount']; ?></p>
    <p><strong>Due Amount:</strong> <?php echo $row['due_amount']; ?></p>
    <?php if ($row['status'] == 'due'): ?>
        <p><strong>Status:</strong> <span style="color: red;"><?php echo $row['status']; ?></span></p>
    <?php else: ?>
        <p><strong>Status:</strong> <span style="color: green;"><?php echo $row['status']; ?></span></p>
    <?php endif; ?>
</div>


        <?php endwhile; ?>
    </div>

    <button onclick="window.print()" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);" class="btn btn-primary">Print</button>

</body>
</html>
