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

$success = 0;
$failed = 0;

// Handle task creation
if(isset($_POST['create_task'])) {
    $task_name = sanitizeInput(mysqli_real_escape_string($conn, $_POST['task_name']));
    $task_desc = sanitizeInput(mysqli_real_escape_string($conn, $_POST['task_desc']));
    $date_due = mysqli_real_escape_string($conn, $_POST['date_due']);
    $date_set = date('Y-m-d H:i:s');
    
    // Get selected students
    $selected_students = isset($_POST['students']) ? $_POST['students'] : array();
    
    if(empty($selected_students)) {
        $failed = 1; // No students selected
    } else {
        // Insert task
        $insert_task = "INSERT INTO task (task_name, task_desc, date_set, date_due) 
                        VALUES ('$task_name', '$task_desc', '$date_set', '$date_due')";
        
        if(mysqli_query($conn, $insert_task)) {
            $taskid = mysqli_insert_id($conn);
            
            // Assign task to selected students
            foreach($selected_students as $student_id) {
                $assign_sql = "INSERT INTO student_task (id, taskid, status) VALUES ('$student_id', '$taskid', 0)";
                mysqli_query($conn, $assign_sql);
            }
            
            $success = 1;
        } else {
            $failed = 2; // Database error
        }
    }
}

// Get all students
$students_sql = "SELECT * FROM users ORDER BY firstname ASC";
$students_result = mysqli_query($conn, $students_sql);

// Get all classes
$classes_sql = "SELECT * FROM class WHERE classid != 0 ORDER BY classname ASC";
$classes_result = mysqli_query($conn, $classes_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
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
        .student-checkbox {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }
        .student-checkbox:hover {
            background-color: #f8f9fa;
        }
        .student-checkbox input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
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
                            <a class="nav-link active" href="create_task.php">
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
                <h1 class="mb-4"><i class="fas fa-plus-circle"></i> Create New Task</h1>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Task Details</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="create_task.php">
                                    <div class="form-group">
                                        <label for="task_name"><strong>Task Name</strong></label>
                                        <input type="text" class="form-control" id="task_name" name="task_name" required placeholder="e.g., Build LED Circuit">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="task_desc"><strong>Task Description</strong></label>
                                        <textarea class="form-control" id="task_desc" name="task_desc" rows="5" required placeholder="Describe the task in detail..."></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="date_due"><strong>Due Date</strong></label>
                                        <input type="datetime-local" class="form-control" id="date_due" name="date_due" required>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="form-group">
                                        <label><strong>Assign to Students</strong></label>
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                                        </div>
                                        
                                        <?php if(mysqli_num_rows($students_result) == 0): ?>
                                            <div class="alert alert-warning">
                                                No students found. Please register students first.
                                            </div>
                                        <?php else: ?>
                                            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                                <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                                                    <div class="student-checkbox">
                                                        <label class="mb-0">
                                                            <input type="checkbox" name="students[]" value="<?php echo $student['id']; ?>" class="student-check">
                                                            <strong><?php echo $student['firstname'] . ' ' . $student['lastname']; ?></strong>
                                                            <small class="text-muted">(<?php echo $student['email']; ?>)</small>
                                                        </label>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="submit" name="create_task" class="btn btn-success btn-lg btn-block mt-4">
                                        <i class="fas fa-check"></i> Create Task
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5><i class="fas fa-info-circle text-info"></i> Tips</h5>
                                <ul>
                                    <li>Give your task a clear, descriptive name</li>
                                    <li>Provide detailed instructions in the description</li>
                                    <li>Set a reasonable due date</li>
                                    <li>Select which students should complete this task</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6><i class="fas fa-graduation-cap"></i> Available Classes</h6>
                                <?php if(mysqli_num_rows($classes_result) == 0): ?>
                                    <p class="text-muted small">No classes available</p>
                                <?php else: ?>
                                    <ul class="list-unstyled">
                                        <?php while($class = mysqli_fetch_assoc($classes_result)): ?>
                                            <li><span class="badge badge-success"><?php echo $class['classname']; ?></span></li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php endif; ?>
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
                title: 'Task Created!',
                text: 'The task has been created and assigned to students.',
                confirmButtonText: 'View Tasks'
            }).then(function() {
                window.location = "manage_tasks.php";
            });
        }
        
        if(failed == 1) {
            Swal.fire({
                icon: 'error',
                title: 'No Students Selected',
                text: 'Please select at least one student to assign this task to.'
            });
        }
        
        if(failed == 2) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not create task. Please try again.'
            });
        }
        
        function selectAll() {
            var checkboxes = document.getElementsByClassName('student-check');
            for(var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        }
        
        function deselectAll() {
            var checkboxes = document.getElementsByClassName('student-check');
            for(var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
        }
        
        // Set minimum date to today
        document.getElementById('date_due').min = new Date().toISOString().slice(0, 16);
    </script>
</body>
</html>

