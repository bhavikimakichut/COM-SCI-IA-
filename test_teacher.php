<?php
include("config.php");

echo "<h2>Teacher Login Debug</h2>";

$email = 'j.ford@gmail.com';
$password = sha1('password');

echo "Looking for teacher with:<br>";
echo "Email: " . $email . "<br>";
echo "Password (encrypted): " . $password . "<br><br>";

$sql = "SELECT * FROM teacher WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0) {
    echo "✅ Teacher FOUND in database!<br><br>";
    $row = mysqli_fetch_assoc($result);
    echo "Teacher Name: " . $row['firstname'] . " " . $row['lastname'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Password in DB: " . $row['password'] . "<br>";
    echo "Password we're trying: " . $password . "<br><br>";
    
    if($row['password'] == $password) {
        echo "✅ PASSWORDS MATCH! Login should work!";
    } else {
        echo "❌ PASSWORDS DON'T MATCH!<br>";
        echo "We need to fix the password in the database.";
    }
} else {
    echo "❌ Teacher NOT FOUND in database!<br>";
    echo "We need to INSERT the teacher into the database.";
}
?>


