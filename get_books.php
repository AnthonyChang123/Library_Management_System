<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = "localhost";
$database = "LibraryDatabase";
$username = "root";
$password = "";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$query = "SELECT * FROM Books ORDER BY id";
$result = $connection->query($query);

if ($result) {
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    echo json_encode($books);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch books']);
}

$connection->close();
?>