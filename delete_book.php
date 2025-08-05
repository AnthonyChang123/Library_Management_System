<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

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

// Get the book ID from POST data
$input = json_decode(file_get_contents('php://input'), true);
$bookId = $input['id'] ?? '';

if (empty($bookId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Book ID is required']);
    exit;
}

// Delete the book from database
$stmt = $connection->prepare("DELETE FROM Books WHERE id = ?");
$stmt->bind_param("s", $bookId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete book']);
}

$stmt->close();
$connection->close();
?>