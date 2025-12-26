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
$classid = $row['classid'];
$picture = $row['picture'];

// Get class name
$class_sql = "SELECT classname FROM class WHERE classid='$classid'";
$class_result = mysqli_query($conn, $class_sql);
$class_row = mysqli_fetch_assoc($class_result);
$classname = $class_row['classname'] ?? 'Unassigned';

// Count total tasks
$task_count_sql = "SELECT COUNT(*) as total FROM student_task WHERE id='$student_id'";
$task_count_result = mysqli_query($conn, $task_count_sql);
$task_count_row = mysqli_fetch_assoc($task_count_result);
$total_tasks = $task_count_row['total'];

// Count completed tasks
$completed_sql = "SELECT COUNT(*) as completed FROM student_task WHERE id='$student_id' AND status=1";
$completed_result = mysqli_query($conn, $completed_sql);
$completed_row = mysqli_fetch_assoc($completed_result);
$completed_tasks = $completed_row['completed'];

// Count pending tasks
$pending_tasks = $total_tasks - $completed_tasks;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .stat-card {
            border-left: 4px solid #667eea;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="profile.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tasks.php">
                                <i class="fas fa-tasks"></i> My Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
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
                <h1 class="mb-4">Welcome back, <?php echo $firstname; ?>! 👋</h1>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Tasks</h6>
                                        <h2 class="mb-0"><?php echo $total_tasks; ?></h2>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-clipboard-list fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card" style="border-left-color: #28a745;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Completed</h6>
                                        <h2 class="mb-0"><?php echo $completed_tasks; ?></h2>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Pending</h6>
                                        <h2 class="mb-0"><?php echo $pending_tasks; ?></h2>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-clock fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Your Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200">Full Name:</th>
                                        <td><?php echo $firstname . ' ' . $lastname; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo $email; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Class:</th>
                                        <td><span class="badge badge-success"><?php echo $classname; ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Student ID:</th>
                                        <td><?php echo $student_id; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

