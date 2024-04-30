<?php
include 'config.php';

// Master Login
function accountantLogin($conn, $username, $password) {
    $sql = "SELECT * FROM accountant WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    return ($result->num_rows == 1);
}

// company manager Login
function managerLogin($conn, $username, $password) {
    $sql = "SELECT company_id FROM company_managers WHERE manager_name = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the company_id from the result
        $row = $result->fetch_assoc();
        $company_id = $row['company_id'];

        // Set the company_id in the session
        $_SESSION['company_id'] = $company_id;

        // Return success status and branch_id
        return array('success' => true, 'company_id' => $company_id);
    } else {
        // Return failure status
        return array('success' => false);
    }
}


// Admin Login
function branchadminLogin($conn, $username, $password) {
    $sql = "SELECT branch_id, company_name FROM branch_admins WHERE admin_name = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the branch_id from the result
        $row = $result->fetch_assoc();
        $branch_id = $row['branch_id'];
        $company_name = $row['company_name'];

        // Set the branch_id in the session
        $_SESSION['branch_id'] = $branch_id;
        $_SESSION['company_name'] = $company_name;


        // Return success status and branch_id
        return array('success' => true, 'branch_id' => $branch_id ,'company_name' => $company_name);
    } else {
        // Return failure status
        return array('success' => false);
    }
}
// Get company for Master
function getcompanyname($conn) {
    $sql = "SELECT * FROM companies";
    $result = $conn->query($sql);

    $companies = array();
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }

    return $companies;
}


// Get Branches for Master
function getBranches($conn , $companyId) {
    // Prepare the SQL statement with a placeholder
    $sql = "SELECT * FROM branches WHERE company_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind the parameter
    $stmt->bind_param("s", $companyId); // Assuming company_id is a string
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Fetch the rows
    $branches = array();
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    
    // Close the statement
    $stmt->close();
    
    return $branches;
}

?>