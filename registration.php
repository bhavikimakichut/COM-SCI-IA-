<?php
session_start();
include("config.php");

// Redirect if already logged in
if(isset($_SESSION['login_user'])) {
    header("location: student/profile.php");
    exit();
}

$success = 0;
$failed = 0;

if(isset($_POST['register'])) {
    $firstname = sanitizeInput(mysqli_real_escape_string($conn, $_POST['firstname']));
    $lastname = sanitizeInput(mysqli_real_escape_string($conn, $_POST['lastname']));
    $email = sanitizeEmail(mysqli_real_escape_string($conn, $_POST['email']));
    $password = sanitizeInput(mysqli_real_escape_string($conn, $_POST['password']));
    $confirmpassword = sanitizeInput(mysqli_real_escape_string($conn, $_POST['confirmpassword']));
    
    // Check if passwords match
    if($password != $confirmpassword) {
        $failed = 2; // Passwords don't match
    } else {
        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $check_email);
        
        if(mysqli_num_rows($result) > 0) {
            $failed = 1; // Email already exists
        } else {
            // Hash password and insert
            $hashed_password = sha1($password);
            $sql = "INSERT INTO users (firstname, lastname, email, password, classid) 
                    VALUES ('$firstname', '$lastname', '$email', '$hashed_password', 0)";
            
            if(mysqli_query($conn, $sql)) {
                $success = 1;
            } else {
                $failed = 3; // Database error
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="index.php">⚡ PBIS Electrical Engineering</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item active"><a class="nav-link" href="registration.php">Register</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Student Registration</h2>
                        
                        <form method="POST" action="registration.php">
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmpassword">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required>
                            </div>
                            
                            <button type="submit" name="register" class="btn btn-success btn-block btn-lg mt-4">Register</button>
                        </form>
                        
                        <hr class="mt-4">
                        <p class="text-center">
                            Already have an account? <a href="login.php">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var success = <?php echo $success; ?>;
        var failed = <?php echo $failed; ?>;
        
        if(success == 1){
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: 'Your account has been created. Redirecting to login...',
                timer: 2000,
                showConfirmButton: false
            }).then(function() {
                window.location = "login.php";
            });
        }
        
        if(failed == 1){
            Swal.fire({
                icon: 'error',
                title: 'Email Already Exists',
                text: 'This email is already registered. Please use a different email or login.'
            });
        }
        
        if(failed == 2){
            Swal.fire({
                icon: 'error',
                title: 'Passwords Do Not Match',
                text: 'Please make sure both passwords are the same.'
            });
        }
        
        if(failed == 3){
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: 'Something went wrong. Please try again.'
            });
        }
    </script>
</body>
</html>

