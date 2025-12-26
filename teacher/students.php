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

// Get all students with their class info and task stats
$students_query = "SELECT users.*, class.classname,
                   COUNT(DISTINCT student_task.taskid) as total_tasks,
                   SUM(CASE WHEN student_task.status = 2 THEN 1 ELSE 0 END) as completed_tasks
                   FROM users 
                   LEFT JOIN class ON users.classid = class.classid
                   LEFT JOIN student_task ON users.id = student_task.id
                   GROUP BY users.id
                   ORDER BY users.firstname ASC";
$students_result = mysqli_query($conn, $students_query);

// Get total counts
$total_students = mysqli_num_rows($students_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - EE Hub</title>
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
        .stat-card {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge-class {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
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
            <a class="nav-link" href="manage_tasks.php">
                📋 Manage Tasks
            </a>
            <a class="nav-link active" href="students.php">
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
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stat-card">
                    <h3>👥 <?php echo $total_students; ?> Total Students</h3>
                    <p class="mb-0">All registered students in the EE Hub system</p>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="content-card">
            <h2 class="mb-4">📋 All Students</h2>
            
            <?php if($total_students == 0): ?>
                <div class="alert alert-info text-center">
                    <h5>📭 No students registered yet</h5>
                    <p>Students need to register at the registration page!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Class</th>
                                <th>Tasks Progress</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($students_result, 0); // Reset pointer
                            while($student = mysqli_fetch_assoc($students_result)): 
                                $completion_percent = 0;
                                if($student['total_tasks'] > 0){
                                    $completion_percent = ($student['completed_tasks'] / $student['total_tasks']) * 100;
                                }
                            ?>
                            <tr>
                                <td>
                                    <img src="../user_uploads/<?php echo $student['image']; ?>" 
                                         class="student-avatar" 
                                         onerror="this.src='../user_uploads/0.jpg'">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <span class="badge badge-class bg-primary text-white">
                                        <?php echo $student['classname'] ? htmlspecialchars($student['classname']) : 'No Class'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo $student['completed_tasks']; ?>/<?php echo $student['total_tasks']; ?> 
                                        (<?php echo round($completion_percent); ?>%)
                                    </small>
                                </td>
                                <td>
                                    <a href="reports.php?student_id=<?php echo $student['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        📊 View Report
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

