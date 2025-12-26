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

// Initialize variables
$student_data = null;
$student_complete = 0;
$student_pending = 0;
$student_awaiting = 0;
$student_failed = 0;

// If student is selected
if(isset($_GET['student_id']) || isset($_POST['student_id'])){
    $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : $_POST['student_id'];
    $student_id = mysqli_real_escape_string($conn, $student_id);
    
    // Get student info
    $student_query = mysqli_query($conn, "SELECT users.*, class.classname FROM users 
                                          LEFT JOIN class ON users.classid = class.classid 
                                          WHERE users.id='$student_id'");
    $student_data = mysqli_fetch_assoc($student_query);
    
    // Get task statistics
    $complete_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_task WHERE id='$student_id' AND status=2");
    $student_complete = mysqli_fetch_assoc($complete_query)['count'];
    
    $pending_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_task WHERE id='$student_id' AND status=0");
    $student_pending = mysqli_fetch_assoc($pending_query)['count'];
    
    $awaiting_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_task WHERE id='$student_id' AND status=1");
    $student_awaiting = mysqli_fetch_assoc($awaiting_query)['count'];
    
    $failed_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_task WHERE id='$student_id' AND status=3");
    $student_failed = mysqli_fetch_assoc($failed_query)['count'];
}

// Get all students for dropdown
$all_students_query = mysqli_query($conn, "SELECT id, firstname, lastname FROM users ORDER BY firstname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-box h3 {
            font-size: 2.5rem;
            margin: 0;
            font-weight: bold;
        }
        .stat-box.completed { border-left: 5px solid #2ecc71; }
        .stat-box.pending { border-left: 5px solid #f39c12; }
        .stat-box.awaiting { border-left: 5px solid #3498db; }
        .stat-box.failed { border-left: 5px solid #e74c3c; }
        .student-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
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
            <a class="nav-link" href="students.php">
                👥 Students
            </a>
            <a class="nav-link active" href="reports.php">
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
            <h2 class="mb-4">📈 Student Performance Report</h2>
            
            <!-- Student Selection -->
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="student_id"><b>Select Student</b></label>
                    <select class="form-control" name="student_id" id="student_id" required>
                        <option value="">-- Choose a Student --</option>
                        <?php while($s = mysqli_fetch_assoc($all_students_query)): ?>
                            <option value="<?php echo $s['id']; ?>" 
                                    <?php echo (isset($student_id) && $student_id == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?> (ID: <?php echo $s['id']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">📊 Generate Report</button>
            </form>

            <?php if($student_data): ?>
                <!-- Student Info -->
                <div class="student-info">
                    <h4>👤 <?php echo htmlspecialchars($student_data['firstname'] . ' ' . $student_data['lastname']); ?></h4>
                    <p class="mb-0">
                        📧 <?php echo htmlspecialchars($student_data['email']); ?> | 
                        📚 Class: <?php echo $student_data['classname'] ? htmlspecialchars($student_data['classname']) : 'No Class'; ?>
                    </p>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-box completed">
                            <h3 style="color: #2ecc71;"><?php echo $student_complete; ?></h3>
                            <p class="mb-0">✅ Completed</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box pending">
                            <h3 style="color: #f39c12;"><?php echo $student_pending; ?></h3>
                            <p class="mb-0">⏳ Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box awaiting">
                            <h3 style="color: #3498db;"><?php echo $student_awaiting; ?></h3>
                            <p class="mb-0">🕒 Awaiting Review</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box failed">
                            <h3 style="color: #e74c3c;"><?php echo $student_failed; ?></h3>
                            <p class="mb-0">❌ Failed</p>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div id="chart_div" style="width: 100%; height: 400px;"></div>

                <script type="text/javascript">
                    google.charts.load('current', {'packages':['corechart']});
                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                        var data = google.visualization.arrayToDataTable([
                            ['Status', 'Number of Tasks'],
                            ['Completed (<?php echo $student_complete; ?>)', <?php echo $student_complete; ?>],
                            ['Pending (<?php echo $student_pending; ?>)', <?php echo $student_pending; ?>],
                            ['Awaiting Review (<?php echo $student_awaiting; ?>)', <?php echo $student_awaiting; ?>],
                            ['Failed (<?php echo $student_failed; ?>)', <?php echo $student_failed; ?>]
                        ]);

                        var options = {
                            title: 'Task Status Distribution for <?php echo htmlspecialchars($student_data['firstname'] . ' ' . $student_data['lastname']); ?>',
                            pieHole: 0.4,
                            colors: ['#2ecc71', '#f39c12', '#3498db', '#e74c3c'],
                            chartArea: {width: '90%', height: '80%'},
                            legend: {position: 'bottom'}
                        };

                        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                        chart.draw(data, options);
                    }
                </script>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <h5>📊 Select a student to view their report</h5>
                    <p>Choose a student from the dropdown above to generate their performance report</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

