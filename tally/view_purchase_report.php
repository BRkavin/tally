<?php
session_start();
include 'accountant.php';

// Function to get the company ID by its name
function getCompanyIdByName($conn, $companyName) {
    $query = "SELECT company_id FROM companies WHERE company_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $companyName);
    $stmt->execute();
    $stmt->bind_result($companyId);
    $stmt->fetch();
    $stmt->close();

    return $companyId;
}

// Check if the user is logged in
if (!isset($_SESSION['company_id'])) {
    // Redirect to the login page if not logged in
    header("Location: accountantlogin.php");
    exit();
}

// Include your database connection file here if it's not already included

// Retrieve the company ID of the logged-in company manager
$companyId = $_SESSION['company_id'];

// Fetch purchase reports for the specific company manager
$companyName = $_GET['company'];

// Use the company name to fetch the company ID from the database
$companyId = getCompanyIdByName($conn, $companyName);

// Function to retrieve invoice report by company ID
function getInvoiceReportByCompanyId($conn, $companyId) {
    // Implement the code to fetch the invoice report based on the company ID
    $sql = "SELECT * FROM purchase_report WHERE company_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Use the company ID to fetch and display the invoice report
$invoiceReport = getInvoiceReportByCompanyId($conn, $companyId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Purchase Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Purchase Reports</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Bill NO</th>
                    <th>Company ID</th>
                    <th>Date</th>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>Supplier Contact</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $invoiceReport->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['bill_no']; ?></td>
                        <td><?php echo $row['company_id']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['supplier_id']; ?></td>
                        <td><?php echo $row['supplier_name']; ?></td>
                        <td><?php echo $row['mobile_no']; ?></td>
                        <td><?php echo $row['grand_total']; ?></td>
                        <td><?php echo $row['paid_amount']; ?></td>
                        <td><?php echo $row['due_amount']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
    <!-- Add a form for the "Print Report" button -->
    <form action="view_invoice.php" method="GET">
        <!-- Pass the bill_no as a hidden input -->
        <input type="hidden" name="bill_no" value="<?php echo $row['bill_no']; ?>">
        <!-- Button to submit the form -->
        <button type="submit" class="btn btn-primary">Print Report</button>
    </form>
</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
