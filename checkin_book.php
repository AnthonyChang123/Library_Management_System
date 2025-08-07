<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = "localhost";
    $database = "LibraryDatabase";
    $username = "root";
    $password = "";

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $rentalId = $_POST['rental_id'];
    $returnDate = date('Y-m-d');

    // Get book ID from rental
    $getRentalStmt = $connection->prepare("SELECT Book_ID FROM Rental WHERE Rental_ID = ? AND Status IN ('active', 'overdue')");
    $getRentalStmt->bind_param("i", $rentalId);
    $getRentalStmt->execute();
    $rentalResult = $getRentalStmt->get_result();
    
    if ($rentalResult->num_rows === 0) {
        echo "<p style='color: red;'>Error: Active rental not found!</p>";
    } else {
        $rental = $rentalResult->fetch_assoc();
        $bookId = $rental['Book_ID'];
        
        // Update rental record
        $updateRentalStmt = $connection->prepare("UPDATE Rental SET Return_Date = ?, Status = 'returned' WHERE Rental_ID = ?");
        $updateRentalStmt->bind_param("si", $returnDate, $rentalId);
        
        if ($updateRentalStmt->execute()) {
            // Update book status
            $updateBookStmt = $connection->prepare("UPDATE Books SET status = 'available' WHERE id = ?");
            $updateBookStmt->bind_param("s", $bookId);
            $updateBookStmt->execute();
            
            echo "<p style='color: green;'>Book checked in successfully!</p>";
            $updateBookStmt->close();
        } else {
            echo "<p style='color: red;'>Error: " . $updateRentalStmt->error . "</p>";
        }
        $updateRentalStmt->close();
    }
    $getRentalStmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<?php include 'header.php'; ?>
<html>
<head>
    <title>Check In Book</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .checkinform {
            background: white;
            padding: 20px;
            border-left: 5px solid #2c5282;
            width: 400px;
        }
        input, select {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background: #2c5282;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #24476b;
        }
        .back-btn {
            background: #6c757d;
        }
        .back-btn:hover {
            background: #5a6268;
        }
        .button-container {
            display: flex;
            gap: 10px;
        }
        #checkintitle {
            margin: 0;
            color: #2c5282;
            font-size: 24px;
        }
    </style>
</head>
<body>

<form class='checkinform' method="POST">
    <h2 id='checkintitle'>Check In Book</h2>
    
    <label>Select Active Rental:</label>
    <select name="rental_id" id="rentalSelect" required>
        <option value="">Select Rental to Return...</option>
    </select>
    
    <div class="button-container">
        <button type="submit">Check In Book</button>
        <button type="button" class="back-btn" onclick="window.location.href='rentals_dashboard.php'">Back to Rentals</button>
    </div>
</form>

<script>
    // Load active rentals
    async function loadActiveRentals() {
        try {
            const response = await fetch('http://localhost/Library_Management_System/get_rentals.php');
            const rentals = await response.json();
            
            const rentalSelect = document.getElementById('rentalSelect');
            rentals.forEach(rental => {
                if (rental.Status === 'active' || rental.Status === 'overdue') {
                    const option = document.createElement('option');
                    option.value = rental.Rental_ID;
                    option.textContent = `${rental.Book_ID} - ${rental.title} (${rental.Renter_FirstName} ${rental.Renter_LastName})`;
                    rentalSelect.appendChild(option);
                }
            });

        } catch (error) {
            console.error('Error loading rentals:', error);
            alert('Failed to load active rentals');
        }
    }

    // Load data when page loads
    window.addEventListener('load', loadActiveRentals);
</script>

</body>
</html>