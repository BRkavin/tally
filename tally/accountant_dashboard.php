<?php
include 'accountant.php';

if (isset($_POST['logout'])) {
    // Perform any additional logout actions if needed
    // For example, destroying the session
    session_destroy();

    // Redirect to the login page after logging out
    header("Location: accountantlogin.php");
    exit();
}
// Fetch companies for dropdown
$companies = getcompanyname($conn);

// Function to check if an admin already exists with the given username
function ismanagernameExists($conn, $managername) {
    $query = "SELECT COUNT(*) FROM company_managers WHERE manager_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $managername);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function isPasswordExists($conn, $managerPassword) {
    // Note: Storing passwords as MD5 hashes is not recommended for security reasons.
    // Use a stronger and more secure hashing algorithm like bcrypt.

    $hashedPassword = md5($managerPassword);

    $query = "SELECT COUNT(*) FROM company_managers WHERE password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

if (isset($_POST['addManager'])) {
    $managername = $_POST['managername'];
    $managerPassword = $_POST['managerPassword'];
    $managerCompany = $_POST['managerCompany'];
    $companyName = ""; // Initialize company name variable

    // Fetch the company name based on the selected company ID
    foreach ($companies as $company) {
        if ($company['company_id'] == $managerCompany) {
            $companyName = $company['company_name'];
            break;
        }
    }

    // Check if the username and password already exist
    if (ismanagernameExists($conn, $managername)) {
        echo '<script>alert("manager with this name already exists!");</script>';
    } else {
        // If the username doesn't exist, proceed with checking the password
        if (isPasswordExists($conn, $managerPassword)) {
            echo '<script>alert("Manager with this password already exists!");</script>';
        } else {
            // If the password doesn't exist, proceed with adding the admin
            $managerPassword = md5($managerPassword);
            addManager($conn, $managername, $managerPassword, $managerCompany, $companyName);
        }
    }
}

// Function to check if a company is already assigned to an manager
function isCompanyAssignedToManager($conn, $companyId) {
    $query = "SELECT COUNT(*) FROM company_managers WHERE company_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $companyId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

// Function to add an admin to the branch_admins table
function addManager($conn, $managername, $managerPassword, $managerCompany, $companyName) {
    $query = "INSERT INTO company_managers (manager_name, password, company_id, company_name) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $managername, $managerPassword, $managerCompany, $companyName);

    if ($stmt->execute()) {
        echo '<script>alert("Manager added successfully!");</script>';
    } else {
        echo '<script>alert("Error adding manager: ' . $stmt->error . '");</script>';
    }

    $stmt->close();
}


// Create Company
function createcompany($conn, $companyName) {
    // Check if the company name already exists
    $checkExistingcompany = "SELECT * FROM companies WHERE company_name = '$companyName'";
    $result = $conn->query($checkExistingcompany);

    if ($result->num_rows > 0) {
        echo '<script>alert("company already exists!");</script>';
    } else {
        // Company name doesn't exist, proceed with inserting
        $sql = "INSERT INTO companies (company_name) VALUES ('$companyName')";
        $conn->query($sql);
    }
}

if (isset($_POST['createCompany'])) {
    $companyName = $_POST['companyName'];
    createcompany($conn, $companyName);
    header("Location: accountant_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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


      <!-- Modal for displaying login error -->
    <!-- <div class="modal fade" id="branchErrorModal" tabindex="-1" role="dialog" aria-labelledby="companyErrorModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyErrorModalLabel">company Name already exists!!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-danger" id="companyErrorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div> -->


    <div class="top-buttons text-center">
      <button class="btn btn-success my-1 btn-text btn-lg btn-yellow" onclick="toggleCompanyFormVisibility()">Add company</button>
      <button class="btn btn-warning  my-1 btn-text btn-lg btn-red" onclick="toggleManagerFormVisibility()">Add manager</button>
      <div class="row">
        <div class="col-md-8">
            <!-- Change the form action in master_dashboard.php -->
            <form action="view_invoice_report.php" method="get">
                <div class="form-group">
                    <label for="company">Select company:</label>
                    <select class="form-control" name="company" required style="width: 100%;">
                        <?php foreach ($companies as $company) : ?>
                            <option value="<?php echo $company['company_name']; ?>"><?php echo $company['company_name']; ?></option>
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
    
   <!-- Modal for adding company -->
<div id="createCompanyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleCompanyFormVisibility()">&times;</span>
            <form action="accountant_dashboard.php" method="post">
                <div class="form-group">
                    <label for="companyName">Company Name:</label>
                    <input type="text" class="form-control" name="companyName" required>
                </div>
                <button type="submit" class="btn btn-success btn-block" name="createCompany">Create Company</button>
            </form>
        </div>
    </div>
</div>


<!-- Inside the "Add manager Form Modal" -->
<div class="modal" id="addManagerModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add Manager</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form action="accountant_dashboard.php" method="post">
                        <!-- Your form fields go here -->
                        <div class="form-group">
                            <label for="managername">Managername:</label>
                            <input type="text" class="form-control" name="managername" required>
                        </div>
                        <div class="form-group">
                <label for="managerPassword">Password:</label>
                <input type="password" class="form-control" name="managerPassword" required>
            </div>

            <div class="form-group">
                <label for="managerCompany">Select Company:</label>
                <select class="form-control" name="managerCompany" required>
                    <?php foreach ($companies as $company) : ?>
                        <?php if (!isCompanyAssignedToManager($conn, $company['company_id'])) : ?>
                            <option value="<?php echo $company['company_id']; ?>"><?php echo $company['company_name']; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
                        <!-- ... (other form fields) -->
                        <button type="submit" class="btn btn-warning btn-block" name="addManager">Add Manager</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>



<script>
    function toggleCompanyFormVisibility() {
        var modal = document.getElementById("createCompanyModal");
        modal.style.display = "block";
    }


    // Document ready function
    $(document).ready(function () {
        // Initialize Bootstrap's modal
        $('#addManagerModal').modal({ show: false });
    });

    function toggleManagerFormVisibility() {
        // Show/hide the modal using Bootstrap's modal function
        $('#addManagerModal').modal('toggle');
    }
</script>
</body>
</html>