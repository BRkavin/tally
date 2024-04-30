<?php
session_start();
include 'accountant.php';
include 'session_helper.php';


$loginError = ""; // Initialize login error message

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password = md5($password);

    // Check if it's a master login
    if (accountantLogin($conn, $username, $password)) {
        $_SESSION['username'] = $username; // Set the username in the session
        header("Location: accountant_dashboard.php");
        exit();
    }

    // Check if it's a manager login
    $managerLoginResult = managerLogin($conn, $username, $password);
    if ($managerLoginResult['success']) {
        $_SESSION['username'] = $username; // Set the username in the session
        $_SESSION['company_id'] = $managerLoginResult['company_id'];
        $_SESSION['company_name'] = $managerLoginResult['company_name'];
        header("Location: companymanager_dashboard.php");
        exit();
    }

    // Check if it's an admin login
    $branchLoginResult = branchadminLogin($conn, $username, $password);
    if ($branchLoginResult['success']) {
        $_SESSION['username'] = $username; // Set the username in the session
        $_SESSION['branch_id'] = $branchLoginResult['branch_id'];
        header("Location: branchmanager_dashboard.php");
        exit();
    }

    // If none of the logins were successful, set the common error message
    $loginError = "Invalid username or password";
}

// Check if both username and password are invalid
if (isset($_POST['login']) && empty($loginError)) {
    $loginError = "Invalid username and password";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
     body {
    background-image: url('login1.jpg'); /* Background image */
    background-size: cover; /* Cover the entire background */
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}



.login-form {
    background-color: rgba(255, 255, 255, 0.8); /* Transparent white background */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding: 30px;
    width: 350px;
    color: #000; /* Black text color */
}


        .login-form h1 {
            font-size: 24px;
            margin-bottom: 30px;
            text-align: center;
            color: #000; /* Black heading color */
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border: none;
            border-bottom: 1px solid transparent;
        }
        .form-control:hover,
        .form-control:focus {
            box-shadow: none;
        }
        .btn-primary {
            background-color: #9c27b0; /* Darker purple */
            border: none;
            width: 50%;
            border-radius: 25px;
            padding: 10px 15px; /* Adjusted padding */
            font-size: 16px;
            margin-left:70px;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #7b1fa2; /* Darker hover color */
        }
        .btn-primary:focus {
            outline: none;
            box-shadow: none;
        }
        .btn-primary:active {
            transform: translateY(1px);
        }
        .alert-danger {
            border-radius: 10px;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-10px); }
            40% { transform: translateX(10px); }
            60% { transform: translateX(-10px); }
            80% { transform: translateX(10px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="login-form">
                <h1>Login</h1>
                <form action="accountantlogin.php" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" name="username" required>
                        <?php if (isset($loginError) && strpos($loginError, "username") !== false) : ?>
                            <div class="invalid-feedback d-block"><?php echo $loginError; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="password" required>
                        <?php if (isset($loginError) && strpos($loginError, "password") !== false) : ?>
                            <div class="invalid-feedback d-block"><?php echo $loginError; ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary" name="login">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>









    <script>
    <?php if (isset($loginError)) : ?>
        $(document).ready(function () {
            $('#loginErrorModal').modal('show');
            $('#loginErrorMessage').text('<?php echo $loginError; ?>');
        });
    <?php endif; ?>
</script>
</body>
</html>
