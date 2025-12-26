<?php
session_start();
include("config.php");

$success = 0;
$failed = 0;

if(isset($_POST['register'])) {
    $firstname = sanitizeInput(mysqli_real_escape_string($conn, $_POST['firstname']));
    $lastname = sanitizeInput(mysqli_real_escape_string($conn, $_POST['lastname']));
    $initials = strtoupper(sanitizeInput(mysqli_real_escape_string($conn, $_POST['initials'])));
    $email = sanitizeEmail(mysqli_real_escape_string($conn, $_POST['email']));
    $password = sanitizeInput(mysqli_real_escape_string($conn, $_POST['password']));
    $confirmpassword = sanitizeInput(mysqli_real_escape_string($conn, $_POST['confirmpassword']));
    
    // Check if passwords match
    if($password != $confirmpassword) {
        $failed = 2; // Passwords don't match
    } else {
        // Check if email already exists
        $check_email = "SELECT * FROM teacher WHERE email='$email'";
        $result = mysqli_query($conn, $check_email);
        
        if(mysqli_num_rows($result) > 0) {
            $failed = 1; // Email already exists
        } else {
            // Hash password and insert
            $hashed_password = sha1($password);
            $sql = "INSERT INTO teacher (firstname, lastname, initials, email, password) 
                    VALUES ('$firstname', '$lastname', '$initials', '$email', '$hashed_password')";
            
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
    <title>Teacher Registration - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <style>
        body {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-chalkboard-teacher fa-4x text-success"></i>
                            <h2 class="mt-3">Teacher Registration</h2>
                            <p class="text-muted">Create your teacher account</p>
                        </div>
                        
                        <form method="POST" action="teacher_registration.php">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstname">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="initials">Initials (e.g., JF)</label>
                                <input type="text" class="form-control" id="initials" name="initials" maxlength="5" required placeholder="JF">
                                <small class="form-text text-muted">This will be displayed on your profile icon</small>
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
                            
                            <button type="submit" name="register" class="btn btn-success btn-block btn-lg mt-4">
                                <i class="fas fa-user-plus"></i> Register as Teacher
                            </button>
                        </form>
                        
                        <hr class="mt-4">
                        <p class="text-center">
                            Already have an account? <a href="login.php">Login here</a>
                        </p>
                        <p class="text-center">
                            <a href="index.php">← Back to Home</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script type="text/javascript">
        var success = <?php echo $success; ?>;
        var failed = <?php echo $failed; ?>;
        
        if(success == 1){
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: 'Your teacher account has been created. Redirecting to login...',
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


