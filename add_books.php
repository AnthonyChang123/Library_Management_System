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

    // Check if book with this ID already exists
    $checkStmt = $connection->prepare("SELECT id FROM Books WHERE id = ?");
    $checkStmt->bind_param("s", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Book already exists - show JavaScript alert
        echo "<script>alert('Error: A book with ID \"$id\" already exists!');</script>";
        echo "<p style='color: red;'>Error: Book ID already exists!</p>";
    } else {
        // No duplicate found, proceed with insertion
        $stmt = $connection->prepare("INSERT INTO Books (id, title, author, isbn, status, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $id, $title, $author, $isbn, $status, $location);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Book added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }

    $checkStmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<?php include 'header.php';?>
<html>
<head>
    <title>Add Book</title>
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
<h2 id='addbooktitle'>Add New Book</h2>

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