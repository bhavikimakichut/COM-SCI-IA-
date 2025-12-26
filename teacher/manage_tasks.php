<?php
session_start();
include("../config.php");

// Check if teacher is logged in
if(!isset($_SESSION['t_login'])){
    header("location: ../login.php");
    exit();
}

$user_check = $_SESSION['t_login'];

// Get teacher info
$teacher_query = mysqli_query($conn, "SELECT * FROM teacher WHERE email='$user_check'");
$teacher = mysqli_fetch_assoc($teacher_query);

// Handle Delete
if(isset($_GET['delete'])){
    $taskid = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Delete from student_task first (foreign key)
    mysqli_query($conn, "DELETE FROM student_task WHERE taskid='$taskid'");
    
    // Delete from task
    mysqli_query($conn, "DELETE FROM task WHERE taskid='$taskid'");
    
    $success = "Task deleted successfully!";
}

// Get all tasks with student count and completion stats
$tasks_query = "SELECT task.*, 
                COUNT(DISTINCT student_task.id) as total_students,
                SUM(CASE WHEN student_task.status = 2 THEN 1 ELSE 0 END) as completed_count
                FROM task 
                LEFT JOIN student_task ON task.taskid = student_task.taskid
                GROUP BY task.taskid
                ORDER BY task.taskid DESC";
$tasks_result = mysqli_query($conn, $tasks_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar .profile-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            color: #2ecc71;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.3);
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .task-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .progress {
            height: 25px;
            border-radius: 15px;
        }
        .btn-action {
            margin: 2px;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        .task-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.3rem;
        }
        .task-meta {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile">
            <div class="profile-circle">
                <?php echo strtoupper($teacher['initials']); ?>
            </div>
            <h5><?php echo $teacher['firstname'] . ' ' . $teacher['lastname']; ?></h5>
            <small><?php echo $teacher['email']; ?></small>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php">
                📊 Dashboard
            </a>
            <a class="nav-link" href="create_task.php">
                ➕ Create Task
            </a>
            <a class="nav-link active" href="manage_tasks.php">
                📋 Manage Tasks
            </a>
            <a class="nav-link" href="students.php">
                👥 Students
            </a>
            <a class="nav-link" href="reports.php">
                📈 Reports
            </a>
            <a class="nav-link" href="logout.php">
                🚪 Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>📋 All Tasks</h2>
                <a href="create_task.php" class="btn btn-success">➕ Create New Task</a>
            </div>

            <?php if(mysqli_num_rows($tasks_result) == 0): ?>
                <div class="alert alert-info text-center">
                    <h5>📭 No tasks created yet</h5>
                    <p>Click "Create New Task" to get started!</p>
                </div>
            <?php else: ?>
                <?php while($task = mysqli_fetch_assoc($tasks_result)): ?>
                    <?php
                        $completion_percent = 0;
                        if($task['total_students'] > 0){
                            $completion_percent = ($task['completed_count'] / $task['total_students']) * 100;
                        }
                        
                        // Determine progress bar color
                        $progress_color = 'bg-danger';
                        if($completion_percent >= 75) $progress_color = 'bg-success';
                        elseif($completion_percent >= 50) $progress_color = 'bg-warning';
                    ?>
                    
                    <div class="task-card">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="task-title"><?php echo htmlspecialchars($task['task_name']); ?></h4>
                                <p class="task-meta">
                                    🗓️ Created: <?php echo date('M j, Y', strtotime($task['date_set'])); ?> | 
                                    ⏰ Due: <?php echo date('M j, Y g:i A', strtotime($task['date_due'])); ?>
                                </p>
                                <p><?php echo substr(htmlspecialchars($task['task_desc']), 0, 150); ?>...</p>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        Completion: <?php echo $task['completed_count']; ?>/<?php echo $task['total_students']; ?> students 
                                        (<?php echo round($completion_percent); ?>%)
                                    </small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $progress_color; ?>" 
                                         style="width: <?php echo $completion_percent; ?>%">
                                        <?php echo round($completion_percent); ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-right d-flex flex-column justify-content-center">
                                <a href="view_task.php?taskid=<?php echo $task['taskid']; ?>" 
                                   class="btn btn-info btn-action">
                                    👁️ View Details
                                </a>
                                <a href="edit_task.php?taskid=<?php echo $task['taskid']; ?>" 
                                   class="btn btn-warning btn-action">
                                    ✏️ Edit Task
                                </a>
                                <a href="#" onclick="confirmDelete(<?php echo $task['taskid']; ?>)" 
                                   class="btn btn-danger btn-action">
                                    🗑️ Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if(isset($success)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $success; ?>',
            confirmButtonColor: '#2ecc71'
        });
    </script>
    <?php endif; ?>
    
    <script>
    function confirmDelete(taskid) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the task for ALL students!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'manage_tasks.php?delete=' + taskid;
            }
        });
    }
    </script>
</body>
</html>

