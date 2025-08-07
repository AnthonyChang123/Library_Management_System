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

$query = "SELECT 
            r.Rental_ID,
            r.User_ID,
            r.Book_ID,
            r.Checked_Out_Date,
            r.Due_Date,
            r.Return_Date,
            r.Status,
            u.Renter_FirstName,
            u.Renter_LastName,
            u.Renter_Username,
            b.title,
            b.author
          FROM Rental r
          LEFT JOIN UserAccount u ON r.User_ID = u.User_ID
          LEFT JOIN Books b ON r.Book_ID = b.id
          ORDER BY r.Checked_Out_Date DESC";

$result = $connection->query($query);

if ($result) {
    $rentals = [];
    while ($row = $result->fetch_assoc()) {
        
        $today = date('Y-m-d');
        if ($row['Status'] === 'active' && $row['Due_Date'] < $today) {
            $row['Status'] = 'overdue';
            
            $updateStmt = $connection->prepare("UPDATE Rental SET Status = 'overdue' WHERE Rental_ID = ?");
            $updateStmt->bind_param("i", $row['Rental_ID']);
            $updateStmt->execute();
            $updateStmt->close();
            
            
            $updateBookStmt = $connection->prepare("UPDATE Books SET status = 'overdue' WHERE id = ?");
            $updateBookStmt->bind_param("s", $row['Book_ID']);
            $updateBookStmt->execute();
            $updateBookStmt->close();
        }
        
        $rentals[] = $row;
    }
    echo json_encode($rentals);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch rentals']);
}

$connection->close();
?>