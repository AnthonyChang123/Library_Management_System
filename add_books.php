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
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $status = $_POST['status'];
    $location = $_POST['location'];

    // Prepare and bind
    $stmt = $connection->prepare("INSERT INTO Books (id, title, author, isbn, status, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $id, $title, $author, $isbn, $status, $location);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Book added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        form {
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
    </style>
</head>
<body>

<h2>Add New Book</h2>

<form method="POST">
    <label>Book ID:</label>
    <input type="text" name="id" required>

    <label>Title:</label>
    <input type="text" name="title" required>

    <label>Author:</label>
    <input type="text" name="author" required>

    <label>ISBN:</label>
    <input type="text" name="isbn" required>

    <label>Status:</label>
    <select name="status">
        <option value="available">Available</option>
        <option value="checked-out">Checked Out</option>
        <option value="overdue">Overdue</option>
    </select>

    <label>Location:</label>
    <input type="text" name="location" required>

    <button type="submit">Add Book</button>
</form>

</body>
</html>
