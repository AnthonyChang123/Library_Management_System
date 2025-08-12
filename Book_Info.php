<?php
include 'header.php';

$host = "localhost";
$database = "LibraryDatabase";
$username = "root";
$password = "";

$connection = new mysqli($host, $username, $password, $database);
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Validate book ID from GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p style='color:red;'>Invalid book ID</p>";
    exit;
}

$book_id = $_GET['id'];

// Fetch book data
$query = "SELECT * FROM Books WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red;'>Book not found</p>";
    exit;
}

$book = $result->fetch_assoc();
$stmt->close();

// Get current rental if book is checked out
$currentRental = null;
if ($book['status'] === 'checked-out' || $book['status'] === 'overdue') {
    $currentQuery = "
        SELECT r.*, u.Renter_FirstName, u.Renter_LastName, u.Renter_Username, u.User_ID
        FROM Rental r
        JOIN UserAccount u ON r.User_ID = u.User_ID
        WHERE r.Book_ID = ? AND r.Status IN ('active', 'overdue')
        ORDER BY r.Checked_Out_Date DESC
        LIMIT 1";
    
    $currentStmt = $connection->prepare($currentQuery);
    $currentStmt->bind_param("s", $book_id);
    $currentStmt->execute();
    $currentResult = $currentStmt->get_result();
    
    if ($currentResult->num_rows > 0) {
        $currentRental = $currentResult->fetch_assoc();
    }
    $currentStmt->close();
}

// Get rental history
$historyQuery = "
    SELECT r.*, u.Renter_FirstName, u.Renter_LastName, u.Renter_Username
    FROM Rental r
    JOIN UserAccount u ON r.User_ID = u.User_ID
    WHERE r.Book_ID = ?
    ORDER BY r.Checked_Out_Date DESC";

$historyStmt = $connection->prepare($historyQuery);
$historyStmt->bind_param("s", $book_id);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

$rentalHistory = [];
while ($row = $historyResult->fetch_assoc()) {
    $rentalHistory[] = $row;
}
$historyStmt->close();

// Calculate statistics
$totalRentals = count($rentalHistory);
$timesOverdue = 0;
foreach ($rentalHistory as $rental) {
    if ($rental['Status'] === 'overdue' || ($rental['Return_Date'] && $rental['Return_Date'] > $rental['Due_Date'])) {
        $timesOverdue++;
    }
}

$connection->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Information</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .book-info {
            background: white;
            padding: 20px;
            border-left: 5px solid #2c5282;
            margin-bottom: 20px;
        }
        .book-info h2 {
            color: #2c5282;
            margin-top: 0;
        }
        .info-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        .status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status.available {
            background: #d4edda;
            color: #155724;
        }
        .status.checked-out {
            background: #fff3cd;
            color: #856404;
        }
        .status.overdue {
            background: #f8d7da;
            color: #721c24;
        }
        .section {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #4a5568;
        }
        .section h3 {
            margin-top: 0;
            color: #4a5568;
        }
        .current-rental {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #d69e2e;
            margin-bottom: 15px;
        }
        .current-rental h3 {
            margin-top: 0;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .btn.edit {
            background: #007bff;
            color: white;
        }
        .btn.edit:hover {
            background: #0056b3;
        }
        .btn.delete {
            background: #dc3545;
            color: white;
        }
        .btn.delete:hover {
            background: #c82333;
        }
        .btn.delete:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .btn.checkout {
            background: #28a745;
            color: white;
        }
        .btn.checkout:hover {
            background: #218838;
        }
        .btn.return {
            background: #ffc107;
            color: #212529;
        }
        .btn.return:hover {
            background: #e0a800;
        }
        .btn.back {
            background: #6c757d;
            color: white;
        }
        .btn.back:hover {
            background: #5a6268;
        }
        .stats-box {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .stat-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            flex: 1;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c5282;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="book-info">
    <h2>Book Details</h2>
    <div class="info-row">
        <span class="info-label">Book ID:</span> <?= htmlspecialchars($book['id']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Title:</span> <?= htmlspecialchars($book['title']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Author:</span> <?= htmlspecialchars($book['author']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">ISBN:</span> <?= htmlspecialchars($book['isbn']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span> 
        <span class="status <?= $book['status'] ?>"><?= ucwords(str_replace('-', ' ', $book['status'])) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Location:</span> <?= htmlspecialchars($book['location']) ?>
    </div>
    
    <div class="stats-box">
        <div class="stat-item">
            <div class="stat-number"><?= $totalRentals ?></div>
            <div class="stat-label">Total Checkouts</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?= $timesOverdue ?></div>
            <div class="stat-label">Times Overdue</div>
        </div>
    </div>
</div>

<?php if ($currentRental): ?>
<div class="current-rental">
    <h3>Currently Checked Out</h3>
    <div class="info-row">
        <span class="info-label">Borrower:</span> 
        <a href="User_Info.php?id=<?= $currentRental['User_ID'] ?>" style="color: #2c5282;">
            <?= htmlspecialchars($currentRental['Renter_FirstName'] . ' ' . $currentRental['Renter_LastName']) ?> 
            (<?= htmlspecialchars($currentRental['Renter_Username']) ?>)
        </a>
    </div>
    <div class="info-row">
        <span class="info-label">Checked Out:</span> <?= date('M d, Y', strtotime($currentRental['Checked_Out_Date'])) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Due Date:</span> 
        <?= date('M d, Y', strtotime($currentRental['Due_Date'])) ?>
        <?php 
        $dueDate = new DateTime($currentRental['Due_Date']);
        $today = new DateTime();
        if ($dueDate < $today): 
            $daysOverdue = $today->diff($dueDate)->days;
        ?>
            <span style="color: red; font-weight: bold;"> (<?= $daysOverdue ?> days overdue)</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="section">
    <h3>Rental History</h3>
    <?php if (!empty($rentalHistory)): ?>
        <table>
            <thead>
                <tr>
                    <th>Borrower</th>
                    <th>Checked Out</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rentalHistory as $rental): ?>
                <tr>
                    <td>
                        <a href="User_Info.php?id=<?= $rental['User_ID'] ?>" style="color: #2c5282;">
                            <?= htmlspecialchars($rental['Renter_FirstName'] . ' ' . $rental['Renter_LastName']) ?>
                        </a>
                    </td>
                    <td><?= date('M d, Y', strtotime($rental['Checked_Out_Date'])) ?></td>
                    <td><?= date('M d, Y', strtotime($rental['Due_Date'])) ?></td>
                    <td><?= $rental['Return_Date'] ? date('M d, Y', strtotime($rental['Return_Date'])) : '-' ?></td>
                    <td>
                        <span class="status <?= $rental['Status'] ?>">
                            <?= ucfirst($rental['Status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No rental history for this book.</p>
    <?php endif; ?>
</div>

<div class="section">
    <h3>Actions</h3>
    <div class="action-buttons">
        <button class="btn edit" onclick="editBook('<?= $book['id'] ?>')">Edit Book Details</button>
        
        <?php if ($book['status'] === 'available'): ?>
            <button class="btn delete" onclick="deleteBook('<?= $book['id'] ?>')">Delete Book</button>
            <button class="btn checkout" onclick="window.location.href='checkout_book.php?book_id=<?= $book['id'] ?>'">Check Out Book</button>
        <?php else: ?>
            <button class="btn delete" disabled title="Cannot delete while checked out">Delete Book</button>
            <?php if ($currentRental): ?>
                <button class="btn return" onclick="returnBook('<?= $currentRental['Rental_ID'] ?>', '<?= $book['id'] ?>')">Mark as Returned</button>
            <?php endif; ?>
        <?php endif; ?>
        
        <button class="btn back" onclick="window.location.href='Library.php'">Back to Inventory</button>
    </div>
</div>

<script>
function editBook(id) {
    // For now, just alert. You can create an edit_book.php page later
    alert('Edit functionality for book ' + id + ' coming soon!');
    // window.location.href = 'edit_book.php?id=' + id;
}

function deleteBook(id) {
    if (confirm('Are you sure you want to permanently delete this book?')) {
        fetch('delete_book.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Book deleted successfully!');
                window.location.href = 'Library.php';
            } else {
                alert('Error deleting book: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete book.');
        });
    }
}

function returnBook(rentalId, bookId) {
    if (confirm('Mark this book as returned?')) {
        fetch('return_book.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                rental_id: rentalId,
                book_id: bookId 
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Book returned successfully!');
                location.reload();
            } else {
                alert('Error returning book: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to return book.');
        });
    }
}
</script>

</body>
</html>