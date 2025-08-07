<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = "localhost";
    $database = "LibraryDatabase";
    $username = "root";
    $password = "";

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $userId = $_POST['user_id'];
    $bookId = $_POST['book_id'];
    $dueDate = $_POST['due_date'];
    $checkoutDate = date('Y-m-d');

    
    $checkBookStmt = $connection->prepare("SELECT status FROM Books WHERE id = ?");
    $checkBookStmt->bind_param("s", $bookId);
    $checkBookStmt->execute();
    $bookResult = $checkBookStmt->get_result();
    
    if ($bookResult->num_rows === 0) {
        echo "<p style='color: red;'>Error: Book not found!</p>";
    } else {
        $book = $bookResult->fetch_assoc();
        if ($book['status'] !== 'available') {
            echo "<p style='color: red;'>Error: Book is not available for checkout!</p>";
        } else {
            // Create rental record
            $rentalStmt = $connection->prepare("INSERT INTO Rental (User_ID, Book_ID, Checked_Out_Date, Due_Date, Status) VALUES (?, ?, ?, ?, 'active')");
            $rentalStmt->bind_param("isss", $userId, $bookId, $checkoutDate, $dueDate);
            
            if ($rentalStmt->execute()) {
                // Update book status
                $updateBookStmt = $connection->prepare("UPDATE Books SET status = 'checked-out' WHERE id = ?");
                $updateBookStmt->bind_param("s", $bookId);
                $updateBookStmt->execute();
                
                echo "<p style='color: green;'>Book checked out successfully!</p>";
                $updateBookStmt->close();
            } else {
                echo "<p style='color: red;'>Error: " . $rentalStmt->error . "</p>";
            }
            $rentalStmt->close();
        }
    }
    $checkBookStmt->close();
    $connection->close();
}
?>

<!DOCTYPE html>
<?php include 'header.php'; ?>
<html>
<head>
    <title>Check Out Book</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .checkoutform {
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
        #checkouttitle {
            margin: 0;
            color: #2c5282;
            font-size: 24px;
        }
    </style>
</head>
<body>

<form class='checkoutform' method="POST">
    <h2 id='checkouttitle'>Check Out Book</h2>
    
    <label>User:</label>
    <select name="user_id" id="userSelect" required>
        <option value="">Select User...</option>
    </select>
    
    <label>Book:</label>
    <select name="book_id" id="bookSelect" required>
        <option value="">Select Book...</option>
    </select>
    
    <label>Due Date:</label>
    <input type="date" name="due_date" id="dueDate" required>
    
    <div class="button-container">
        <button type="submit">Check Out Book</button>
        <button type="button" class="back-btn" onclick="window.location.href='rentals_dashboard.php'">Back to Rentals</button>
    </div>
</form>

<script>
    
    const today = new Date();
    const twoWeeksLater = new Date(today.getTime() + (14 * 24 * 60 * 60 * 1000));
    document.getElementById('dueDate').value = twoWeeksLater.toISOString().split('T')[0];

    
    async function loadUsersAndBooks() {
        try {
            
            const usersResponse = await fetch('http://localhost/Library_Management_System/get_users.php');
            const users = await usersResponse.json();
            
            const userSelect = document.getElementById('userSelect');
            users.forEach(user => {
                if (user.Renter_Status) { 
                    const option = document.createElement('option');
                    option.value = user.User_ID;
                    option.textContent = `${user.Renter_FirstName} ${user.Renter_LastName} (${user.Renter_Username})`;
                    userSelect.appendChild(option);
                }
            });

            
            const booksResponse = await fetch('http://localhost/Library_Management_System/get_books.php');
            const books = await booksResponse.json();
            
            const bookSelect = document.getElementById('bookSelect');
            books.forEach(book => {
                if (book.status === 'available') { 
                    const option = document.createElement('option');
                    option.value = book.id;
                    option.textContent = `${book.id} - ${book.title} by ${book.author}`;
                    bookSelect.appendChild(option);
                }
            });

        } catch (error) {
            console.error('Error loading data:', error);
            alert('Failed to load users and books');
        }
    }

    window.addEventListener('load', loadUsersAndBooks);
</script>

</body>
</html>