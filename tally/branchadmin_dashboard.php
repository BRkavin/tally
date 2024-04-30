<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: accountantlogin.php");
    exit();
}

// Include your database connection code here
// Assuming you have a $conn variable for database connection
include 'accountant.php'; // Update the path as per your file structure

// Fetch branch details based on branch_id
$branchId = $_SESSION['branch_id'];
$branchName = '';
$branchSql = "SELECT branch_name FROM branches WHERE branch_id = '$branchId'";
$branchResult = $conn->query($branchSql);

if ($branchResult->num_rows > 0) {
    $row = $branchResult->fetch_assoc();
    $branchName = $row['branch_name'];
}

// Display the branch_id and company_name
$companyName = $_SESSION['company_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<h1>Welcome to Branch Admin Dashboard</h1>

<p>Branch ID: <?php echo $branchId; ?></p>
<p>Branch Name: <?php echo $branchName; ?></p>
<p>Company Name: <?php echo $companyName; ?></p>
<button class="btn btn-primary my-1 btn-text btn-lg btn-green" onclick="toggleFormVisibility()">purchase product</button>
<!-- Your dashboard content goes here -->

 <!-- Add New Invoice Form Modal -->
 <div id="newInvoiceModal" class="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <!-- Warning Message -->
<div class="alert alert-warning mt-3" id="warningMessage" style="display: none;">
</div>
            <div class="modal-header">
                <h4 class="modal-title">Add New Invoice</h4>
                <button type="button" class="close" onclick="toggleFormVisibility()">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <div class="container mt-5">
                    <!-- Add New Invoice Form -->
                    <form action="companymanager_dashboard.php" method="post" id="newpurchaseForm" class="row mx-auto">

                        <!-- supplier id -->
                        <div class="form-group col-md-2">
                            <label for="supplierId">Supplier id:</label>
                            <input type="text" class="form-control" name="supplierId" id="supplierId" required>
                        </div>

                        <!-- Supplier Name -->
                        <div class="form-group col-md-2">
                            <label for="supplierName">Supplier Name:</label>
                            <input type="text" class="form-control" name="supplierName" id="supplierName" required>
                        </div>

                        <!-- Address -->
                        <div class="form-group col-md-2">
                            <label for="address">Supplier Address:</label>
                            <input type="text" class="form-control" name="address" id="address" required>
                        </div>

                        <!-- Parent Name -->
                        <div class="form-group col-md-2">
                            <label for="contact">Supplier contact:</label>
                            <input type="text" class="form-control" name="contact" id="contact" required>
                        </div>

                        <!-- Invoice Date -->
                        <div class="form-group col-md-4">
                            <label for="billDate">Bill Date:</label>
                            <input type="date" class="form-control" name="billDate" id="billDate" required>
                        </div>
                        <!-- Branches Dropdown -->
                        <div class="form-group col-md-4">
                            <label for="branch">Branch:</label>
                            <select class="form-control" name="branch" id="branch" required>
                                <?php
                                    // Include your database connection code here
                                    // Assuming you have a $conn variable for database connection
                                    // Replace 'branches' with your actual table name
                                    $sql = "SELECT * FROM branches";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row["branch_id"] . "'>" . $row["branch_name"] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No branches found</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <!-- Subject details rows -->
                        <div id="productRowsContainer"></div>
                        <div class="form-group col-md-12">
                            <button type="button" id="addRow" class="btn btn-secondary">Add Row</button>
                        </div>

                        <!-- Grand Total -->
                        <div class="form-group col-md-4">
                            <label for="grandTotal">Grand Total:</label>
                            <input type="text" class="form-control" name="grandTotal" id="grandTotal" readonly>
                        </div>

                        <!-- Paid Amount -->
                        <div class="form-group col-md-4">
                            <label for="paidAmount">Paid Amount:</label>
                            <input type="text" class="form-control" name="paidAmount" id="paidAmount" required oninput="validatePaidAmount();">
                            <span id="paidAmountError" class="error"></span>
                        </div>

                        <!-- Due Amount -->
                        <div class="form-group col-md-4">
                            <label for="dueAmount">Due Amount:</label>
                            <input type="text" class="form-control" name="dueAmount" id="dueAmount" readonly>
                        </div>

                        <!-- Add New Invoice Button -->
                        <div class="form-group col-md-12">
                            <button type="submit" name="addNewInvoice" id="addNewInvoice" class="btn btn-primary">Add New product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



</body>
</html>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>


 // Function to toggle the visibility of the invoice modal
 function toggleFormVisibility() {
        var modal = document.getElementById("newInvoiceModal");
        modal.style.display = modal.style.display === "block" ? "none" : "block";
    }
    </script>
