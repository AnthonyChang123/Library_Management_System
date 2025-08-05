<?php
include 'header.php';

// Connect to database
$host = "localhost";
$database = "LibraryDatabase";
$username = "root";
$password = "";

$connection = new mysqli($host, $username, $password, $database);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Validate user ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>Invalid user ID</p>";
    exit;
}

$user_id = $_GET['id'];

// Fetch user data
$query = "SELECT * FROM UserAccount WHERE User_ID = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red;'>User not found</p>";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Info</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .user-info {
            background: white;
            padding: 20px;
            border-left: 5px solid #2c5282;
            width: 400px;
        }
        .user-info h2 {
            color: #2c5282;
            margin-top: 0;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="user-info">
    <h2>User Details</h2>
    <div class="info-row"><span class="info-label">User ID:</span> <?= $user['User_ID'] ?></div>
    <div class="info-row"><span class="info-label">Username:</span> <?= $user['Renter_Username'] ?></div>
    <div class="info-row"><span class="info-label">First Name:</span> <?= $user['Renter_FirstName'] ?></div>
    <div class="info-row"><span class="info-label">Last Name:</span> <?= $user['Renter_LastName'] ?></div>
    <div class="info-row"><span class="info-label">Address:</span> <?= $user['Renter_Address'] ?></div>
    <div class="info-row"><span class="info-label">Email:</span> <?= $user['Renter_Email'] ?></div>
    <div class="info-row"><span class="info-label">Status:</span> 
        <?= $user['Renter_Status'] ? 'Eligible' : 'Non-Eligible' ?>
    </div>
</div>

</body>
</html>
