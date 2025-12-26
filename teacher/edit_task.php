<?php
session_start();
include("../config.php");

// Check if teacher is logged in
if(!isset($_SESSION['t_login'])){
    header("location: ../login.php");
    exit();
}

$user_check = $_SESSION['t_login'];

// Get task ID from URL
if(!isset($_GET['taskid'])){
    header("location: manage_tasks.php");
    exit();
}

$taskid = mysqli_real_escape_string($conn, $_GET['taskid']);

// Fetch task details
$task_query = mysqli_query($conn, "SELECT * FROM task WHERE taskid='$taskid'");
$task = mysqli_fetch_assoc($task_query);

if(!$task){
    header("location: manage_tasks.php");
    exit();
}

// Update task
if(isset($_POST['submit'])){
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $task_desc = mysqli_real_escape_string($conn, $_POST['task_desc']);
    $date_due = mysqli_real_escape_string($conn, $_POST['date_due']);
    
    $update_query = "UPDATE task SET task_name='$task_name', task_desc='$task_desc', date_due='$date_due' WHERE taskid='$taskid'";
    
    if(mysqli_query($conn, $update_query)){
        $success = "Task updated successfully!";
    } else {
        $error = "Error updating task: " . mysqli_error($conn);
    }
    
    // Refresh task data
    $task_query = mysqli_query($conn, "SELECT * FROM task WHERE taskid='$taskid'");
    $task = mysqli_fetch_assoc($task_query);
}

// Get teacher info
$teacher_query = mysqli_query($conn, "SELECT * FROM teacher WHERE email='$user_check'");
$teacher = mysqli_fetch_assoc($teacher_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - EE Hub</title>
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
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .btn-save {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
            color: white;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-cancel:hover {
            transform: translateY(-2px);
            background: #7f8c8d;
            color: white;
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
            <h2 class="mb-4">✏️ Edit Task</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="task_name">Task Name</label>
                    <input type="text" class="form-control" id="task_name" name="task_name" 
                           value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="task_desc">Task Description</label>
                    <textarea class="form-control" id="task_desc" name="task_desc" rows="6" required><?php echo htmlspecialchars($task['task_desc']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="date_due">Due Date</label>
                    <input type="datetime-local" class="form-control" id="date_due" name="date_due" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($task['date_due'])); ?>" required>
                </div>

                <div class="form-group">
                    <label>Date Created</label>
                    <input type="text" class="form-control" 
                           value="<?php echo date('F j, Y g:i A', strtotime($task['date_set'])); ?>" disabled>
                </div>

                <div class="mt-4">
                    <button type="submit" name="submit" class="btn btn-save">
                        💾 Save Changes
                    </button>
                    <a href="manage_tasks.php" class="btn btn-cancel ml-2">
                        ❌ Cancel
                    </a>
                </div>
            </form>
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
    
    <?php if(isset($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#e74c3c'
        });
    </script>
    <?php endif; ?>
</body>
</html>

