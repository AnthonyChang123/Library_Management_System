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

$query = "SELECT * FROM UserAccount ORDER BY User_ID";
$result = $connection->query($query);

if ($result) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'User_ID' => $row['User_ID'],
            'Renter_Username' => $row['Renter_Username'],
            'Renter_FirstName' => $row['Renter_FirstName'],
            'Renter_LastName' => $row['Renter_LastName'],
            'Renter_Address' => $row['Renter_Address'],
            'Renter_Status' => $row['Renter_Status'] ? true : false
        ];
    }
    echo json_encode($users);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch users']);
}

$connection->close();
?>