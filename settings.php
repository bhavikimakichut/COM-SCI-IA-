<?php
session_start();
include("../config.php");

// Check if logged in
if(!isset($_SESSION['login_user'])) {
    header("location: ../login.php");
    exit();
}

$email = $_SESSION['login_user'];

// Get student info
$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$student_id = $row['id'];
$firstname = $row['firstname'];
$lastname = $row['lastname'];
$picture = $row['picture'];

$success = 0;
$failed = 0;

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = sha1(sanitizeInput(mysqli_real_escape_string($conn, $_POST['current_password'])));
    $new_password = sanitizeInput(mysqli_real_escape_string($conn, $_POST['new_password']));
    $confirm_password = sanitizeInput(mysqli_real_escape_string($conn, $_POST['confirm_password']));
    
    // Check current password
    $check_sql = "SELECT * FROM users WHERE id='$student_id' AND password='$current_password'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) == 0) {
        $failed = 1; // Wrong current password
    } elseif($new_password != $confirm_password) {
        $failed = 2; // Passwords don't match
    } else {
        $hashed_new = sha1($new_password);
        $update_sql = "UPDATE users SET password='$hashed_new' WHERE id='$student_id'";
        if(mysqli_query($conn, $update_sql)) {
            $success = 1;
        }
    }
}

// Handle profile picture upload
if(isset($_POST['upload_picture'])) {
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = $student_id . '_' . time() . '.' . $ext;
            $upload_path = "../image/" . $new_filename;
            
            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                // Delete old picture if not default
                if($picture != '0.jpg' && file_exists("../image/" . $picture)) {
                    unlink("../image/" . $picture);
                }
                
                $update_pic_sql = "UPDATE users SET picture='$new_filename' WHERE id='$student_id'";
                if(mysqli_query($conn, $update_pic_sql)) {
                    $picture = $new_filename;
                    $success = 2;
                }
            } else {
                $failed = 3;
            }
        } else {
            $failed = 4; // Invalid file type
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .profile-section {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }
        .settings-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar p-0">
                <div class="profile-section">
                    <img src="../image/<?php echo $picture; ?>" alt="Profile" class="profile-img">
                    <h5 class="mt-3"><?php echo $firstname . ' ' . $lastname; ?></h5>
                    <small><?php echo $email; ?></small>
                </div>
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tasks.php">
                                <i class="fas fa-tasks"></i> My Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ml-sm-auto px-md-4 py-4">
                <h1 class="mb-4"><i class="fas fa-cog"></i> Settings</h1>
                
                <div class="row">
                    <!-- Profile Picture Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-image"></i> Profile Picture</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="../image/<?php echo $picture; ?>" alt="Profile" class="settings-img mb-3">
                                
                                <form method="POST" action="settings.php" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <input type="file" class="form-control-file" name="profile_pic" accept="image/*" required>
                                        <small class="form-text text-muted">Allowed: JPG, JPEG, PNG, GIF</small>
                                    </div>
                                    <button type="submit" name="upload_picture" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload Picture
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-key"></i> Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="settings.php">
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-success btn-block">
                                        <i class="fas fa-save"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        var success = <?php echo $success; ?>;
        var failed = <?php echo $failed; ?>;
        
        if(success == 1) {
            Swal.fire({
                icon: 'success',
                title: 'Password Changed!',
                text: 'Your password has been updated successfully.',
                timer: 2000
            });
        }
        
        if(success == 2) {
            Swal.fire({
                icon: 'success',
                title: 'Picture Updated!',
                text: 'Your profile picture has been changed.',
                timer: 2000
            }).then(function() {
                window.location = "settings.php";
            });
        }
        
        if(failed == 1) {
            Swal.fire({
                icon: 'error',
                title: 'Wrong Password',
                text: 'Your current password is incorrect.'
            });
        }
        
        if(failed == 2) {
            Swal.fire({
                icon: 'error',
                title: 'Passwords Don\'t Match',
                text: 'New password and confirmation must match.'
            });
        }
        
        if(failed == 3) {
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: 'Could not upload the file. Please try again.'
            });
        }
        
        if(failed == 4) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Please upload only JPG, PNG, or GIF images.'
            });
        }
    </script>
</body>
</html>

