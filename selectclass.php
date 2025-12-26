<?php
session_start();
require_once('../config.php');

// Check if user is logged in
if(!isset($_SESSION['login_user'])){
    header("location: ../login.php");
    exit();
}

$user_check = $_SESSION['login_user'];

// Get user information
$query = "SELECT * FROM users WHERE email = '$user_check'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if(!$user) {
    session_destroy();
    header("location: ../index.php");
    exit();
}

$user_id = $user['id'];
$user_firstname = $user['firstname'];
$user_classid = $user['classid'];

// Handle class selection
$success = "";
$error = "";

if(isset($_POST['select_class'])) {
    $selected_class = mysqli_real_escape_string($conn, $_POST['classid']);
    
    $updateQuery = "UPDATE users SET classid='$selected_class' WHERE id='$user_id'";
    
    if(mysqli_query($conn, $updateQuery)) {
        $success = "Class selected successfully!";
        // Redirect to profile after 2 seconds
        header("refresh:2;url=profile.php");
    } else {
        $error = "Error selecting class. Please try again.";
    }
}

// Get all available classes
$classes = mysqli_query($conn, "SELECT * FROM class WHERE classid != 0 ORDER BY classid");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Class - EE Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <style>
        .class-card {
            border: 3px solid #ddd;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
        }
        .class-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .class-card.beginner {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        .class-card.beginner:hover {
            border-color: #28a745;
            background: linear-gradient(135deg, #c3e6cb 0%, #b1dfbb 100%);
        }
        .class-card.intermediate {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }
        .class-card.intermediate:hover {
            border-color: #ffc107;
            background: linear-gradient(135deg, #ffeaa7 0%, #fddc8b 100%);
        }
        .class-card.advanced {
            border-color: #dc3545;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }
        .class-card.advanced:hover {
            border-color: #dc3545;
            background: linear-gradient(135deg, #f5c6cb 0%, #f1b0b7 100%);
        }
        .class-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .selected {
            border: 5px solid #007bff !important;
            box-shadow: 0 0 20px rgba(0,123,255,0.5);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="profile.php">PBIS Electrical Engineering</a>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo $user_firstname; ?>! 👋</h1>
            <p class="lead">Please select your skill level to get started</p>
        </div>
    </div>

    <div class="container">
        <?php if($success) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Success!</strong> <?php echo $success; ?> Redirecting to your dashboard...
            </div>
        <?php } ?>
        
        <?php if($error) { ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> <?php echo $error; ?>
            </div>
        <?php } ?>

        <form method="POST" id="classForm">
            <div class="row">
                <?php 
                $icons = ['🌱', '⚡', '🚀'];
                $colors = ['beginner', 'intermediate', 'advanced'];
                $descriptions = [
                    'Perfect for those just starting their electrical engineering journey. Learn the basics of circuits, Microbits, and simple projects.',
                    'Build on your foundation! Work with Arduinos, sensors, and more complex circuits. Prior basic knowledge recommended.',
                    'For experienced students ready for challenges! Advanced robotics, programming, and complex engineering projects.'
                ];
                $index = 0;
                
                while($class = mysqli_fetch_assoc($classes)) { 
                    $selected = ($user_classid == $class['classid']) ? 'selected' : '';
                ?>
                <div class="col-md-4">
                    <label for="class<?php echo $class['classid']; ?>" style="width: 100%;">
                        <input type="radio" 
                               name="classid" 
                               id="class<?php echo $class['classid']; ?>" 
                               value="<?php echo $class['classid']; ?>" 
                               style="display: none;" 
                               required
                               <?php echo ($user_classid == $class['classid']) ? 'checked' : ''; ?>>
                        
                        <div class="class-card <?php echo $colors[$index]; ?> <?php echo $selected; ?>" 
                             onclick="selectClass(<?php echo $class['classid']; ?>)">
                            <div class="text-center">
                                <div class="class-icon"><?php echo $icons[$index]; ?></div>
                                <h3><?php echo $class['classname']; ?></h3>
                                <p class="text-muted"><?php echo $descriptions[$index]; ?></p>
                                
                                <div class="mt-4">
                                    <h5>What you'll learn:</h5>
                                    <ul class="text-left">
                                        <?php if($index == 0) { ?>
                                            <li>Basic circuits & electricity</li>
                                            <li>Introduction to Microbits</li>
                                            <li>Simple LED projects</li>
                                            <li>Safety fundamentals</li>
                                        <?php } elseif($index == 1) { ?>
                                            <li>Arduino programming</li>
                                            <li>Sensor integration</li>
                                            <li>Motor control</li>
                                            <li>Project design</li>
                                        <?php } else { ?>
                                            <li>Advanced robotics</li>
                                            <li>Complex circuits</li>
                                            <li>System integration</li>
                                            <li>Competition preparation</li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                <?php 
                    $index++;
                } 
                ?>
            </div>

            <div class="text-center mt-4 mb-5">
                <button type="submit" name="select_class" class="btn btn-success btn-lg px-5">
                    Confirm Selection
                </button>
                <?php if($user_classid != 0) { ?>
                    <a href="profile.php" class="btn btn-secondary btn-lg px-5 ml-3">
                        Skip for Now
                    </a>
                <?php } ?>
            </div>
        </form>

        <div class="alert alert-info mb-5">
            <strong>💡 Don't worry!</strong> You can change your class anytime from your settings page.
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectClass(classid) {
            // Remove selected from all cards
            document.querySelectorAll('.class-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected to clicked card
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById('class' + classid).checked = true;
        }

        <?php if($success) { ?>
        Swal.fire({
            icon: 'success',
            title: 'Class Selected!',
            text: 'Redirecting to your dashboard...',
            timer: 2000,
            showConfirmButton: false
        });
        <?php } ?>
    </script>
</body>
</html>

