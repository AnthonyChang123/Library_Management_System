<?php

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

// get form inputs
$userInput = $_POST['username'] ?? '';
$passInput = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // prepare and execute statement
    $stmt = $connection->prepare("SELECT * FROM LibrarianAccount WHERE username=? AND password=?");
    $stmt->bind_param("ss", $userInput, $passInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo "Login successful! Redirecting...";
        header("Refresh:2; url=http://localhost/LibraryManagementSystem/Library.html");
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
}

$connection->close();
?>
