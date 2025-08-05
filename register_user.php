<?php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
$host = "localhost";
$database = "LibraryDatabase";
$username = "root";
$password = "";

// make connection
$connection = new mysqli($host, $username, $password, $database);

// check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

    // Get form values
    $renter_username = $_POST['username'];
    $renter_firstname = $_POST['first_name'];
    $renter_lastname = $_POST['last_name'];
    $renter_address = $_POST['address'];
    $renter_status = $_POST['status'];

    // Prepare and bind
    $stmt = $connection->prepare("INSERT INTO UserAccount (Renter_Username, Renter_FirstName, Renter_LastName, Renter_Address, Renter_Status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $renter_username, $renter_firstname, $renter_lastname, $renter_address, $renter_status);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>User added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<?php include 'header.php';?>
<html>
<head>
    <title>Register User</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .addbookform {
            background: white;
            padding: 20px;
            border-left: 5px solid #2c5282;
            width: 400px;
        }
        input, select {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            font-size: 14px;
        }
        button {
            background: #2c5282;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        button:hover {
            background: #24476b;
        }
        #addbooktitle {
            margin: 0;
            color: #2c5282;
            font-size: 24px;
        }
    </style>
</head>
<body>

<form class='addbookform' method="POST">
<h2 id='addbooktitle'>Register User</h2>

    <label>Username:</label>
    <input type="text" name="username" required>

    <label>First Name:</label>
    <input type="text" name="first_name" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" required>

    <label>Address:</label>
    <input type="text" name="address" required>

    <label>Status:</label>
    <select name="status">
        <option value="1">Eligible</option>
        <option value="0">Non-Eligible</option>
    </select>

    <button type="submit">Register</button>
</form>

</body>
</html>