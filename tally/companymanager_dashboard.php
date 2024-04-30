<?php
session_start();
include 'accountant.php';

if (isset($_POST['logout'])) {
    // Perform any additional logout actions if needed
    // For example, destroying the session
    session_destroy();

    // Redirect to the login page after logging out
    header("Location: accountantlogin.php");
    exit();
}
// Function to check if an admin already exists with the given username
function isUsernameExists($conn, $adminUsername) {
    $query = "SELECT COUNT(*) FROM branch_admins WHERE admin_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $adminUsername);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function isPasswordExists($conn, $adminPassword) {
    // Note: Storing passwords as MD5 hashes is not recommended for security reasons.
    // Use a stronger and more secure hashing algorithm like bcrypt.

    $hashedPassword = md5($adminPassword);

    $query = "SELECT COUNT(*) FROM branch_admins WHERE password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}



function getCompanyNameById($conn, $companyId) {
    $query = "SELECT company_name FROM companies WHERE company_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $companyId);
    $stmt->execute();
    $stmt->bind_result($companyName);
    $stmt->fetch();
    $stmt->close();

    return $companyName;
}



if (isset($_POST['addAdmin'])) {
    $adminUsername = $_POST['adminUsername'];
    $adminPassword = $_POST['adminPassword'];
    $adminBranch = $_POST['adminBranch'];
    $companyId = $_SESSION['company_id'];
    
    // Fetch company name based on company ID
    $companyName = getCompanyNameById($conn, $companyId);

    // Check if the username and password already exist
    if (isUsernameExists($conn, $adminUsername)) {
        echo '<script>alert("Admin with this username already exists!");</script>';
    } else {
        // If the username doesn't exist, proceed with checking the password
        if (isPasswordExists($conn, $adminPassword)) {
            echo '<script>alert("Admin with this password already exists!");</script>';
        } else {
            // If the password doesn't exist, proceed with adding the admin
            $adminPassword = md5($adminPassword);
            addAdmin($conn, $adminUsername, $adminPassword, $adminBranch, $companyName);
        }
    }
}
// Function to check if a branch is already assigned to an admin
function isBranchAssignedToAdmin($conn, $branchId) {
    $query = "SELECT COUNT(*) FROM branch_admins WHERE branch_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branchId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

// Function to add an admin to the branch_admins table
function addAdmin($conn, $adminUsername, $adminPassword, $adminBranch, $companyName) {
    $query = "INSERT INTO branch_admins (admin_name, password, branch_id, company_name) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $adminUsername, $adminPassword, $adminBranch, $companyName);

    if ($stmt->execute()) {
        echo '<script>alert("Admin added successfully!");</script>';
    } else {
        echo '<script>alert("Error adding admin: ' . $stmt->error . '");</script>';
    }

    $stmt->close();
}



function isCompanyIdValid($conn, $companyId) {
    $sql = "SELECT COUNT(*) FROM companies WHERE company_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $companyId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

function createBranch($conn, $branchName, $companyId) {
    if (!isCompanyIdValid($conn, $companyId)) {
        echo '<script>alert("Invalid company ID!");</script>';
        return;
    }

    $checkExistingBranch = "SELECT * FROM branches WHERE branch_name = ?";
    $stmt = $conn->prepare($checkExistingBranch);
    $stmt->bind_param("s", $branchName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script>alert("Branch already exists!");</script>';
    } else {
        $sql = "INSERT INTO branches (branch_name, company_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $branchName, $companyId);
        if ($stmt->execute()) {
            echo '<script>alert("Branch added successfully!");</script>';
        } else {
            echo '<script>alert("Error adding branch: ' . $stmt->error . '");</script>';
        }
    }
}


// Fetch company name based on company ID
$companyId = $_SESSION['company_id'];
$companyName = getCompanyNameById($conn, $companyId);

// Fetch branches for dropdown
$branches = getBranches($conn, $companyId);

if (isset($_POST['createBranch'])) {
    $branchName = $_POST['branchName'];
    $companyId = $_SESSION['company_id'];
    createBranch($conn, $branchName, $companyId);
    header("Location: companymanager_dashboard.php");
}

if (isset($_POST['addNewInvoice'])) {
    // Fetch data from the form
    $companyID = $_SESSION['company_id'];
    $branchId = $_POST['branch'];
$branchNameSql = "SELECT branch_name FROM branches WHERE branch_id = '$branchId'";
$branchNameResult = $conn->query($branchNameSql);

if ($branchNameResult->num_rows > 0) {
    $row = $branchNameResult->fetch_assoc();
    $branchName = $row['branch_name'];
} else {
    $branchName = 'Unknown Branch'; // Default value if branch name not found
}
    // Continue with inserting the invoice
    $supplierID =  $_POST['supplierId'];
    $address = $_POST['address'];
    $supplierName = $_POST['supplierName'];
    $contact_no = $_POST['contact'];
    $invoiceDate = $_POST['billDate'];
    $grandTotal = $_POST['grandTotal'];
    $paidAmount = $_POST['paidAmount'];

    // Calculate due amount
    $dueamount = ($grandTotal - $paidAmount);
    $balance_amount = 0;

    // If paid amount is greater than grand total, update paid amount and set due amount to 0
    if ($paidAmount > $grandTotal) {
        $balance_amount = $paidAmount - $grandTotal;
        $dueamount = 0;
    }

    if ($dueamount == 0) {
        $invoiceStatus = 'Paid';
    } else {
        $invoiceStatus = 'Due';
    }

    // Generate the invoice number
    $invoiceNumber = generateInvoiceNumber($conn, $companyID, $invoiceDate);

    // Insert invoice details into purchase_report table
   // Insert invoice details into purchase_report table
// Insert invoice details into purchase_report table
$invoiceSql = "INSERT INTO purchase_report (bill_no, company_id, branch_name, date, supplier_id, supplier_name, mobile_no, paid_amount, grand_total, due_amount, status)
    VALUES ('$invoiceNumber', '$companyID', '$branchName', CAST('$invoiceDate' AS DATE), '$supplierID','$supplierName' ,'$contact_no', '$paidAmount', '$grandTotal','$dueamount','$invoiceStatus')";


    // Execute the invoice insertion query
    if ($conn->query($invoiceSql) === TRUE) {
        // Loop through each product and insert into purchased_product table
        $branchId = $_POST['branch'];
        $branchNameSql = "SELECT branch_name FROM branches WHERE branch_id = '$branchId'";
        $branchNameResult = $conn->query($branchNameSql);
        
        if ($branchNameResult->num_rows > 0) {
            $row = $branchNameResult->fetch_assoc();
            $branchName = $row['branch_name'];
        } else {
            $branchName = 'Unknown Branch'; // Default value if branch name not found
        }
        $products = $_POST['product'];
        $prices = $_POST['price'];
        $quantities = $_POST['quantity'];
        $taxes = $_POST['tax'];

        // Inform about successful insertion of invoice
        echo '<script>alert("New invoice added successfully!");</script>';
        for ($i = 0; $i < count($products); $i++) {

            
            // Insert product into purchased_product table
            $productName = implode(', ', $products[$i]);
            $price = implode(', ', $prices[$i]);
            $quantity = implode(', ', $quantities[$i]);
            $tax = implode(', ', $taxes[$i]);

            $productSql = "INSERT INTO purchased_product (bill_no, branch_name, product_name, price, quantity, tax) 
                VALUES ('$invoiceNumber','$branchName', '$productName', '$price', '$quantity', '$tax')";

            if ($conn->query($productSql) !== TRUE) {
                // Error inserting product details
                echo "Error: " . $productSql . "<br>" . $conn->error;
            }
            // Insert product into stock table
            $stockSql = "INSERT INTO stock (branch_name, product_name, price, tax, quantity)
                VALUES ('$branchName','$productName', '$price', '$tax', '$quantity')";

            if ($conn->query($stockSql) !== TRUE) {
                // Error inserting stock details
                echo "Error: " . $stockSql . "<br>" . $conn->error;
            }
        }
        echo '</table>';
    } else {
        // Error inserting invoice details
        echo "Error: " . $invoiceSql . "<br>" . $conn->error;
    }
}









// Function to generate invoice number
function generateInvoiceNumber($conn, $companyID, $invoiceDate) {
    // Format the invoice date (assuming it's in YYYY-MM-DD format)
    $formattedInvoiceDate = date('Ymd', strtotime($invoiceDate));

    // Query to get the last used sequence number for the given date
    $result = $conn->query("SELECT MAX(SUBSTRING(bill_no, -2)) as max_sequence 
                            FROM  purchase_report
                            WHERE company_id = '$companyID' 
                            AND date = '$formattedInvoiceDate'");
    $row = $result->fetch_assoc();
    $maxSequence = ($row['max_sequence']) ? intval($row['max_sequence']) : 0;

    // Increment the sequence number or restart from 01 if the date is different
    $newSequence = ($maxSequence >= 99) ? 1 : $maxSequence + 1;

    // Pad the sequence number with leading zeros
    $paddedSequence = str_pad($newSequence, 2, '0', STR_PAD_LEFT);

    // Concatenate branch ID, formatted date, and padded sequence to create the invoice number
    $invoiceNumber = $companyID . $formattedInvoiceDate . $paddedSequence;

    return $invoiceNumber;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>manager Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
    .modal-lg {
        max-width: 80% !important; /* Adjust the percentage as needed */
    }

        </style>
</head>
<body>
<div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-6 text-right">
                <form action="" method="post">
                    <button type="submit" class="btn btn-outline-danger btn-sm" name="logout">Logout</button>
                </form>
            </div>
        </div>



    <div class="top-buttons text-center">
    <div class="col-md-2 mb-3">
    <a href="view_purchase_report.php?company=<?php echo urlencode($companyName); ?>" class="btn btn-success btn-block btn-square pt-2">purchased Invoices</a>

     </div>    <button class="btn btn-primary my-1 btn-text btn-lg btn-green" onclick="toggleFormVisibility()">purchase product</button>
    <button class="btn btn-success my-1 btn-text btn-lg btn-yellow" onclick="toggleBranchFormVisibility()">Add Branch</button>
    <button class="btn btn-warning  my-1 btn-text btn-lg btn-red" onclick="toggleAdminFormVisibility()">Add Admin</button>
      <div class="row">
        <div class="col-md-8">
            <!-- Change the form action in manager_dashboard.php -->
            <form action="view_invoice_report.php" method="get">
                <div class="form-group">
                    <label for="company">Select Branch:</label>
                    <select class="form-control" name="branch" required style="width: 100%;">
                        <?php foreach ($branches as $branch) : ?>
                            <option value="<?php echo $branch['branch_name']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-info my-1 btn-text" style="width: 200%;">View Invoice Report</button>
              </div>
             </form>
          </div>
        </div>
    </div>   
    

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


  <!-- Add Branch Form Modal -->
  <div id="createBranchModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleBranchFormVisibility()">&times;</span>
                <form action="companymanager_dashboard.php" method="post">
    <div class="form-group">
        <label for="branchName">Branch Name:</label>
        <input type="text" class="form-control" name="branchName" required>
    </div>
    <input type="hidden" name="companyId" value="<?php echo $companyId; ?>"> <!-- Add this hidden input field -->
    <button type="submit" class="btn btn-success btn-block" name="createBranch">Create Branch</button>
</form>

            </div>
        </div>


<!-- Inside the "Add Admin Form Modal" -->
<div class="modal" id="addAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add Admin</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form action="companymanager_dashboard.php" method="post">
                        <!-- Your form fields go here -->
                        <div class="form-group">
                            <label for="adminUsername">Username:</label>
                            <input type="text" class="form-control" name="adminUsername" required>
                        </div>
                        <div class="form-group">
                <label for="adminPassword">Password:</label>
                <input type="password" class="form-control" name="adminPassword" required>
            </div>

            <div class="form-group">
                <label for="adminBranch">Select Branch:</label>
                <select class="form-control" name="adminBranch" required>
                    <?php foreach ($branches as $branch) : ?>
                        <?php if (!isBranchAssignedToAdmin($conn, $branch['branch_id'])) : ?>
                            <option value="<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
                        <!-- ... (other form fields) -->
                        <button type="submit" class="btn btn-warning btn-block" name="addAdmin">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>



<script>

    function toggleBranchFormVisibility() {
        var modal = document.getElementById("createBranchModal");
        modal.style.display = "block";
    }

     // Function to toggle the visibility of the invoice modal
     function toggleFormVisibility() {
        var modal = document.getElementById("newInvoiceModal");
        modal.style.display = modal.style.display === "block" ? "none" : "block";
    }
     // Document ready function
     $(document).ready(function () {
        // Initialize Bootstrap's modal
        $('#addAdminModal').modal({ show: false });
    });

    function toggleAdminFormVisibility() {
        // Show/hide the modal using Bootstrap's modal function
        $('#addAdminModal').modal('toggle');
    }

     // Event listener for deleting rows
     $(document).on('click', '.delete-row', function () {
            $(this).closest('.row').remove();
            // Call a function here to update the grand total if needed
        });

    document.getElementById('addRow').addEventListener('click', function () {
       addNewproductRow();
       calculateGrandTotal();
       updateDueAmount();
  });
  // Function to add a new product row
  function addNewproductRow() {
        // Clone the existing subject row template
        var clonedRow = document.getElementById('subjectRowTemplate').content.cloneNode(true);

        // Modify the IDs and names of the cloned row elements to avoid conflicts
        var rowIdx = document.getElementById('productRowsContainer').childElementCount;
        clonedRow.querySelectorAll('[id]').forEach(function (element) {
            element.id += '_' + rowIdx;
        });
        clonedRow.querySelectorAll('[name]').forEach(function (element) {
            element.name += '[]';
        });

       
        // Append the cloned row to the form
        var clonedRowContainer = document.createElement('div');
        clonedRowContainer.className = 'row';
        clonedRowContainer.appendChild(clonedRow);
        document.getElementById('productRowsContainer').appendChild(clonedRowContainer);
    }

    document.addEventListener('input', function(event) {
        if (event.target.matches('.product-row input')) {
            calculateRowTotal(event.target.closest('.product-row'));
            calculateGrandTotal();
            updateDueAmount();
        }
    });

    // Function to calculate the total for each row
    function calculateRowTotal(row) {
        var price = parseFloat(row.querySelector('.price').value);
        var tax = parseFloat(row.querySelector('.tax').value);
        var quantity = parseFloat(row.querySelector('.quantity').value);

        var total = (price * quantity) + (price * quantity * (tax / 100));

        row.querySelector('.total').value = isNaN(total) ? '' : total.toFixed(2);
        // Call a function here to update the grand total if needed
    }

    // Function to calculate the grand total
function calculateGrandTotal() {
    var totalInputs = document.querySelectorAll('.total');
    var grandTotal = 0;
    totalInputs.forEach(function(input) {
        if(input.value !== '') {
            grandTotal += parseFloat(input.value);
        }
    });
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
}

// Function to calculate and update the Due Amount
function updateDueAmount() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var dueAmount = grandTotal - paidAmount;
    if(dueAmount < 0){

    // Display the calculated due amount
    document.getElementById('dueAmount').value = 0;
    }
    else{
    // Display the calculated due amount
    document.getElementById('dueAmount').value = Math.abs(dueAmount.toFixed(2));
    }
 }

  // Attach an event listener to the 'Paid Amount' field for real-time updates
  document.getElementById('paidAmount').addEventListener('input', function () {
    updateDueAmount();
 });


  // Attach an event listener to the 'Paid Amount' field for real-time updates
 document.getElementById('paidAmount').addEventListener('input', function () {
    updateDueAmount();
    checkPaidAmountValidity(); // Call the function to check paid amount validity
 });


// Function to check the validity of the paid amount
function checkPaidAmountValidity() {
    var grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
    var paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;

    var minValidPaidAmount = grandTotal * 0.5; // 50% of the grand total

    // Check if paidAmount is less than 50% of grandTotal
    if (paidAmount < minValidPaidAmount) {
        // Display the warning message
        document.getElementById('warningMessage').innerText = 'Your paid amount is less than 50% of the grand total. Please pay at least 50% of the grand total.';
        document.getElementById('warningMessage').style.display = 'block';
        // Disable the submit button
        document.getElementById('addNewInvoice').disabled = true;
    } else if (paidAmount > grandTotal) {
        // Display the warning message for invalid multiple of grand total
        document.getElementById('warningMessage').innerText = 'Your paid amount is greater than the grand total, you could not pay more then grand total.';
        document.getElementById('warningMessage').style.display = 'block';
        // Disable the submit button
        document.getElementById('addNewInvoice').disabled = true;
    } else {
        // Hide the warning message
        document.getElementById('warningMessage').style.display = 'none';

        // Enable the submit button
        document.getElementById('addNewInvoice').disabled = false;
    }
}


</script>

 <!-- Subject Row Template -->
<template id="subjectRowTemplate">
    <div class="row product-row">
        <!-- Product Name -->
        <div class="form-group col-md-2">
            <label for="product">Product Name</label>
            <input type="text" class="form-control" name="product[]" required>
        </div>

        <!-- Price -->
        <div class="form-group col-md-2">
            <label for="price">Price:</label>
            <input type="number" class="form-control price" name="price[]" required>
        </div>

        <!-- Tax -->
        <div class="form-group col-md-2">
            <label for="tax">Tax:</label>
            <input type="number" class="form-control tax" name="tax[]" required>
        </div>

        <!-- Quantity -->
        <div class="form-group col-md-2">
            <label for="quantity">Quantity:</label>
            <input type="number" class="form-control quantity" name="quantity[]" required>
        </div>

        <!-- Total -->
        <div class="form-group col-md-2">
            <label for="total">Total:</label>
            <input type="text" class="form-control total" name="total" readonly>
        </div>

        <!-- Delete Button -->
        <div class="form-group col-md-2" style="margin-top: 30px;">
            <button type="button" class="btn btn-danger delete-row">Delete</button>
        </div>
    </div>
</template>
</body>
</html>