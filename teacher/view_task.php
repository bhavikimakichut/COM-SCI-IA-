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

$firstname = $row['firstname'];
$lastname = $row['lastname'];
$initials = $row['initials'];

// Get task ID
$taskid = isset($_GET['id']) ? $_GET['id'] : 0;

// Get task details
$task_sql = "SELECT * FROM task WHERE taskid='$taskid'";
$task_result = mysqli_query($conn, $task_sql);
$task = mysqli_fetch_assoc($task_result);

if(!$task) {
    header("location: manage_tasks.php");
    exit();
}

// Get students assigned to this task
$students_sql = "SELECT u.*, st.status 
                 FROM users u
                 INNER JOIN student_task st ON u.id = st.id
                 WHERE st.taskid = '$taskid'
                 ORDER BY u.firstname ASC";
$students_result = mysqli_query($conn, $students_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Task - EE Hub</title>
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
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_task.php">
                                <i class="fas fa-plus-circle"></i> Create Task
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_tasks.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-eye"></i> Task Details</h1>
                    <a href="manage_tasks.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Tasks
                    </a>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0"><?php echo $task['task_name']; ?></h4>
                            </div>
                            <div class="card-body">
                                <h6 class="text-muted">Description:</h6>
                                <p><?php echo nl2br($task['task_desc']); ?></p>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Date Set:</strong><br>
                                        <?php echo date('l, F j, Y g:i A', strtotime($task['date_set'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Due Date:</strong><br>
                                        <?php echo date('l, F j, Y g:i A', strtotime($task['date_due'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-users"></i> Assigned Students</h5>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($students_result) == 0): ?>
                                    <p class="text-muted">No students assigned to this task.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                                                <tr>
                                                    <td><?php echo $student['firstname'] . ' ' . $student['lastname']; ?></td>
                                                    <td><?php echo $student['email']; ?></td>
                                                    <td>
                                                        <?php if($student['status'] == 1): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Completed
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6>Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <a href="edit_task.php?id=<?php echo $taskid; ?>" class="btn btn-warning btn-block mb-2">
                                        <i class="fas fa-edit"></i> Edit Task
                                    </a>
                                    <a href="manage_tasks.php" class="btn btn-secondary btn-block">
                                        <i class="fas fa-arrow-left"></i> Back to List
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


