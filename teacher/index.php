<?php
session_start();
include("../config.php");

// Check if logged in as teacher
if(!isset($_SESSION['t_login'])) {
    header("location: ../login.php");
    exit();
}

$email = $_SESSION['t_login'];

// Get teacher info
$sql = "SELECT * FROM teacher WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$teacher_id = $row['teacherid'];
$firstname = $row['firstname'];
$lastname = $row['lastname'];
$initials = $row['initials'];

// Count total students
$student_count_sql = "SELECT COUNT(*) as total FROM users";
$student_count_result = mysqli_query($conn, $student_count_sql);
$student_count_row = mysqli_fetch_assoc($student_count_result);
$total_students = $student_count_row['total'];

// Count total tasks
$task_count_sql = "SELECT COUNT(*) as total FROM task";
$task_count_result = mysqli_query($conn, $task_count_sql);
$task_count_row = mysqli_fetch_assoc($task_count_result);
$total_tasks = $task_count_row['total'];

// Count total classes
$class_count_sql = "SELECT COUNT(*) as total FROM class WHERE classid != 0";
$class_count_result = mysqli_query($conn, $class_count_sql);
$class_count_row = mysqli_fetch_assoc($class_count_result);
$total_classes = $class_count_row['total'];

// Get recent tasks
$recent_tasks_sql = "SELECT * FROM task ORDER BY date_set DESC LIMIT 5";
$recent_tasks_result = mysqli_query($conn, $recent_tasks_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        .profile-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 40px;
            color: #11998e;
            font-weight: bold;
        }
        .stat-card {
            border-left: 4px solid #11998e;
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
                    <div class="profile-icon"><?php echo $initials; ?></div>
                    <h5 class="mt-3"><?php echo $firstname . ' ' . $lastname; ?></h5>
                    <small>Teacher</small>
                </div>
                <div class="p-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_task.php">
                                <i class="fas fa-plus-circle"></i> Create Task
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_tasks.php">
                                <i class="fas fa-tasks"></i> Manage Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="students.php">
                                <i class="fas fa-users"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
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
                <h1 class="mb-4">Welcome back, <?php echo $firstname; ?>! 👨‍🏫</h1>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Students</h6>
                                        <h2 class="mb-0"><?php echo $total_students; ?></h2>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-users fa-3x"></i>
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
                                        <h6 class="text-muted">Total Tasks</h6>
                                        <h2 class="mb-0"><?php echo $total_tasks; ?></h2>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-clipboard-list fa-3x"></i>
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
                                        <h6 class="text-muted">Total Classes</h6>
                                        <h2 class="mb-0"><?php echo $total_classes; ?></h2>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-graduation-cap fa-3x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Tasks</h5>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($recent_tasks_result) == 0): ?>
                                    <p class="text-muted">No tasks created yet. <a href="create_task.php">Create your first task!</a></p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Task Name</th>
                                                    <th>Description</th>
                                                    <th>Date Set</th>
                                                    <th>Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($task = mysqli_fetch_assoc($recent_tasks_result)): ?>
                                                <tr>
                                                    <td><strong><?php echo $task['task_name']; ?></strong></td>
                                                    <td><?php echo substr($task['task_desc'], 0, 50) . '...'; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($task['date_set'])); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($task['date_due'])); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5><i class="fas fa-lightbulb text-warning"></i> Quick Actions</h5>
                                <div class="btn-group mt-3" role="group">
                                    <a href="create_task.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Create New Task
                                    </a>
                                    <a href="students.php" class="btn btn-primary">
                                        <i class="fas fa-users"></i> View Students
                                    </a>
                                    <a href="reports.php" class="btn btn-info">
                                        <i class="fas fa-chart-bar"></i> View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

