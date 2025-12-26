<?php
session_start();
include("config.php");

// Redirect if already logged in
if(isset($_SESSION['login_user'])) {
    header("location: student/profile.php");
    exit();
}
if(isset($_SESSION['t_login'])) {
    header("location: teacher/index.php");
    exit();
}

$failed = 0;

if(isset($_POST['login'])) {
    $email = sanitizeEmail(mysqli_real_escape_string($conn, $_POST['email']));
    $password = sha1(sanitizeInput(mysqli_real_escape_string($conn, $_POST['password'])));
    $loginType = $_POST['loginType'];
    
    if($loginType == 'student') {
        // Student login
        $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $sql);
        
        if($result && mysqli_num_rows($result) == 1) {
            // Fetch user data
            $row = mysqli_fetch_assoc($result);
            $_SESSION['login_user'] = $email;
            
            // Check if user has selected a class
            if($row['classid'] == 0) {
                header('location: student/selectclass.php');
            } else {
                header('location: student/profile.php');
            }
            exit();
        } else {
            $failed = 1;
        }
    } else {
        // Teacher login
        $sql = "SELECT * FROM teacher WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($result) == 1) {
            $_SESSION['t_login'] = $email;
            header('location: teacher/index.php');
            exit();
        } else {
            $failed = 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EE Hub</title>
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
                    <li class="nav-item"><a class="nav-link" href="registration.php">Register</a></li>
                    <li class="nav-item active"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Login</h2>
                        
                        <form method="POST" action="login.php">
                            <div class="form-group">
                                <label>Login As:</label>
                                <select class="form-control" name="loginType" required>
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-success btn-block btn-lg mt-4">Login</button>
                        </form>
                        
                        <hr class="mt-4">
                        <p class="text-center">
                            Don't have an account? <a href="registration.php">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var failed = <?php echo $failed; ?>;
        if(failed >= 1){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Wrong Email or Password. Try again!'
            });
        }
    </script>
</body>
</html>

