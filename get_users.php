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

$query = "SELECT * FROM UserAccount ORDER BY id";
$result = $connection->query($query);

if ($result) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row ['User_ID'],
        'username' => $row['Renter_Username'],
        'first_name' => $row['Renter_FirstName'],
        'last_name' => $row['Renter_LastName'],
        'status' => $row['Renter_Status'] ? 'available' : 'checked-out',
    }
    echo json_encode($books);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch books']);
}

$connection->close();
?>