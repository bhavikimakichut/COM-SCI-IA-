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

// Handle task completion
$success = 0;
if(isset($_POST['complete_task'])) {
    $taskid = $_POST['taskid'];
    $update_sql = "UPDATE student_task SET status=1 WHERE id='$student_id' AND taskid='$taskid'";
    if(mysqli_query($conn, $update_sql)) {
        $success = 1;
    }
}

// Handle task uncomplete
if(isset($_POST['uncomplete_task'])) {
    $taskid = $_POST['taskid'];
    $update_sql = "UPDATE student_task SET status=0 WHERE id='$student_id' AND taskid='$taskid'";
    if(mysqli_query($conn, $update_sql)) {
        $success = 2;
    }
}

// Get all tasks for this student
$tasks_sql = "SELECT t.*, st.status 
              FROM task t 
              INNER JOIN student_task st ON t.taskid = st.taskid 
              WHERE st.id = '$student_id' 
              ORDER BY t.date_due ASC";
$tasks_result = mysqli_query($conn, $tasks_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - EE Hub</title>
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
        .task-card {
            transition: transform 0.3s;
            border-left: 4px solid #667eea;
        }
        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .task-card.completed {
            border-left-color: #28a745;
            opacity: 0.8;
        }
        .task-card.overdue {
            border-left-color: #dc3545;
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
                            <a class="nav-link active" href="tasks.php">
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
                <h1 class="mb-4"><i class="fas fa-tasks"></i> My Tasks</h1>
                
                <?php if(mysqli_num_rows($tasks_result) == 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You have no tasks assigned yet. Check back later!
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php while($task = mysqli_fetch_assoc($tasks_result)): 
                            $due_date = strtotime($task['date_due']);
                            $current_date = time();
                            $is_overdue = ($due_date < $current_date && $task['status'] == 0);
                            $card_class = $task['status'] == 1 ? 'completed' : ($is_overdue ? 'overdue' : '');
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card task-card <?php echo $card_class; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0"><?php echo $task['task_name']; ?></h5>
                                        <?php if($task['status'] == 1): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Completed
                                            </span>
                                        <?php elseif($is_overdue): ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Overdue
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="card-text"><?php echo nl2br($task['task_desc']); ?></p>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-plus"></i> 
                                            Set: <?php echo date('M d, Y', strtotime($task['date_set'])); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-check"></i> 
                                            Due: <?php echo date('M d, Y', strtotime($task['date_due'])); ?>
                                        </small>
                                    </div>
                                    
                                    <form method="POST" action="tasks.php" style="display: inline;">
                                        <input type="hidden" name="taskid" value="<?php echo $task['taskid']; ?>">
                                        <?php if($task['status'] == 0): ?>
                                            <button type="submit" name="complete_task" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Mark as Complete
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="uncomplete_task" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-undo"></i> Mark as Incomplete
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        var success = <?php echo $success; ?>;
        if(success == 1) {
            Swal.fire({
                icon: 'success',
                title: 'Great Job!',
                text: 'Task marked as completed!',
                timer: 2000,
                showConfirmButton: false
            }).then(function() {
                window.location = "tasks.php";
            });
        }
        if(success == 2) {
            Swal.fire({
                icon: 'info',
                title: 'Task Updated',
                text: 'Task marked as incomplete.',
                timer: 2000,
                showConfirmButton: false
            }).then(function() {
                window.location = "tasks.php";
            });
        }
    </script>
</body>
</html>


