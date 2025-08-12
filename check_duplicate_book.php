<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Get the book data from POST request
$input = json_decode(file_get_contents('php://input'), true);
$bookId = $input['id'] ?? '';
$isbn = $input['isbn'] ?? '';

$response = [
    'duplicate_id' => false,
    'duplicate_isbn' => false,
    'existing_book' => null
];

// Check if book ID already exists
if (!empty($bookId)) {
    $checkIdStmt = $connection->prepare("SELECT id FROM Books WHERE id = ?");
    $checkIdStmt->bind_param("s", $bookId);
    $checkIdStmt->execute();
    $idResult = $checkIdStmt->get_result();
    
    if ($idResult->num_rows > 0) {
        $response['duplicate_id'] = true;
    }
    $checkIdStmt->close();
}

// Check if ISBN already exists (only if ID is not duplicate)
if (!$response['duplicate_id'] && !empty($isbn)) {
    $checkIsbnStmt = $connection->prepare("SELECT id, title, author FROM Books WHERE isbn = ?");
    $checkIsbnStmt->bind_param("s", $isbn);
    $checkIsbnStmt->execute();
    $isbnResult = $checkIsbnStmt->get_result();
    
    if ($isbnResult->num_rows > 0) {
        $response['duplicate_isbn'] = true;
        $response['existing_book'] = $isbnResult->fetch_assoc();
    }
    $checkIsbnStmt->close();
}

echo json_encode($response);
$connection->close();
?>