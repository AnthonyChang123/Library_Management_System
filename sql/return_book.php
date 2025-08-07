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


$input = json_decode(file_get_contents('php://input'), true);
$rentalId = $input['rental_id'] ?? '';
$bookId = $input['book_id'] ?? '';

if (empty($rentalId) || empty($bookId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Rental ID and Book ID are required']);
    exit;
}

$returnDate = date('Y-m-d');


$updateRentalStmt = $connection->prepare("UPDATE Rental SET Return_Date = ?, Status = 'returned' WHERE Rental_ID = ?");
$updateRentalStmt->bind_param("si", $returnDate, $rentalId);

if ($updateRentalStmt->execute()) {
    
    $updateBookStmt = $connection->prepare("UPDATE Books SET status = 'available' WHERE id = ?");
    $updateBookStmt->bind_param("s", $bookId);
    
    if ($updateBookStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Book returned successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update book status']);
    }
    $updateBookStmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update rental record']);
}

$updateRentalStmt->close();
$connection->close();
?>