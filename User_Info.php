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

// Validate user ID from GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>Invalid user ID</p>";
    exit;
}

$user_id = $_GET['id'];

// Fetch user data
$query = "SELECT * FROM UserAccount WHERE User_ID = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red;'>User not found</p>";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();


$sqlgetrental = "
Select
r.Rental_ID,
b.title,
b.author,
r.Checked_Out_Date,
r.Due_Date,
r.Status
From Rental r
Join Books b on r.Book_ID = b.id
Where r.User_ID = ?
Order by r.Checked_Out_Date Desc";


$rstmt = $connection->prepare($sqlgetrental);
$rstmt->bind_param("i", $user_id);
$rstmt->execute();
$rentalsRes = $rstmt->get_result();

$rentals = [];
while ($row = $rentalsRes->fetch_assoc()) {
    $rentals[] = $row;
}
$rstmt->close();


// Fines = $10 per overdue rental for THIS user (status-based)
$sqlFines = "SELECT COUNT(*) AS overdue_count
             FROM Rental
             WHERE Status = 'overdue' AND User_ID = ?";

$fstmt = $connection->prepare($sqlFines);
$fstmt->bind_param("i", $user_id);
$fstmt->execute();
$fstmt->bind_result($overdueCount);
$fstmt->fetch();
$fstmt->close();

$fineTotal = ((int)$overdueCount) * 10;



$connection->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Info</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .user-info {
            background: white;
            padding: 20px;
            border-left: 5px solid #2c5282;
            width: 400px;
        }
        .user-info h2 {
            color: #2c5282;
            margin-top: 0;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
        }

        .user-sections {
            margin-top: 30px;
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

        /* Status styles matching inventory page */
        .status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status.eligible {
            background: #d4edda;
            color: #155724;
        }
        
        .status.non-eligible {
            background: #f8d7da;
            color: #721c24;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.overdue {
            background: #f8d7da;
            color: #721c24;
        }

        .status.returned {
            background: #e7f3ff;
            color: #004085;
        }

        /* Table styles */
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

    </style>
</head>
<body>

<div class="user-info">
    <h2>User Details</h2>
    <div class="info-row">
        <span class="info-label">User ID:</span> <?= $user['User_ID'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">Username:</span> <?= $user['Renter_Username'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">First Name:</span> <?= $user['Renter_FirstName'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">Last Name:</span> <?= $user['Renter_LastName'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">Address:</span> <?= $user['Renter_Address'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">Email:</span> <?= $user['Renter_Email'] ?>
    </div>
    <div class="info-row">
        <span class="info-label">Status:</span> 
        <?php if ($user['Renter_Status']): ?>
            <span class="status eligible">Eligible</span>
        <?php else: ?>
            <span class="status non-eligible">Non-Eligible</span>
        <?php endif; ?>
    </div>
</div>


<!-- work in progress -->
<div class="user-sections">

    <div class="section">
        <h3>Rentals</h3>
        <?php if (!empty($rentals)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rental ID</th>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Checked Out</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rentals as $r): ?>
                    <tr>
                        <td><?= (int)$r['Rental_ID'] ?></td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><?= htmlspecialchars($r['author']) ?></td>
                        <td><?= htmlspecialchars(date('m/d/Y', strtotime($r['Checked_Out_Date']))) ?></td>
                        <td><?= htmlspecialchars(date('m/d/Y', strtotime($r['Due_Date']))) ?></td>
                        <td>
                            <span class="status <?= strtolower($r['Status']) ?>">
                                <?= htmlspecialchars(ucfirst($r['Status'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No rentals found for this user.</p>
        <?php endif; ?>
    </div>

<div class="section">
    <h3>Outstanding Fines</h3>
    <?php if ($fineTotal > 0): ?>
        <p><strong>Total Due:</strong> $<?= number_format($fineTotal, 2) ?></p>
        <small>$10 per overdue book.</small>
    <?php else: ?>
        <p>No fines found.</p>
    <?php endif; ?>
</div>


</div>


</body>
</html>
