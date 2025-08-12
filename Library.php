<?php
    include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Inventory</title>
</head>
<body>

    <div class="controls">
        <div class="search-box">
            <input type="text" placeholder="Search books..." id="search">
        </div>
        <div class="filter">
            <select id="filter">
                <option value="">All Books</option>
                <option value="available">Available</option>
                <option value="checked-out">Checked Out</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>
        <button class="add-btn" onclick="window.location.href='add_books.php'">Add Book</button>

    </div>

    <div class="stats">
        <div class="stat total">
            <div class="stat-number">0</div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat available">
            <div class="stat-number">0</div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat checked-out">
            <div class="stat-number">0</div>
            <div class="stat-label">Checked Out</div>
        </div>
        <div class="stat overdue">
            <div class="stat-number">0</div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>

    <div class="inventory">
        <h2>Book Inventory</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bookTable">
                <!-- Books will be loaded from database -->
            </tbody>
        </table>

        <div class="pagination">
            <button>Previous</button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <button>Next</button>
        </div>
    </div>

    <script>
        let booksData = []; // Will hold books from database

        // Load books from backend when page loads
        async function loadBooks() {
            try {
                const response = await fetch('http://localhost/Library_Management_System/get_books.php');
                if (!response.ok) {
                    throw new Error('Failed to fetch books');
                }
                booksData = await response.json();
                displayBooks();
                updateStats();
            } catch (error) {
                console.error('Error loading books:', error);
                alert('Failed to load books from database');
            }
        }

        function displayBooks() {
            const tbody = document.getElementById('bookTable');
            tbody.innerHTML = '';
            
            booksData.forEach(book => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${book.id}</td>
                    <td>${book.title}</td>
                    <td>${book.author}</td>
                    <td>${book.isbn}</td>
                    <td><span class="status ${book.status}">${capitalizeStatus(book.status)}</span></td>
                    <td>${book.location}</td>
                    <td>
                        <div class="actions">
                            <button class="btn edit" onclick="window.location.href='Book_Info.php?id=${book.id}'">View</button>
                            <button class="btn delete" onclick="deleteBook('${book.id}')">Delete</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function capitalizeStatus(status) {
            return status.replace('-', ' ').split(' ').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }

        function updateStats() {
            const total = booksData.length;
            const available = booksData.filter(book => book.status === 'available').length;
            const checkedOut = booksData.filter(book => book.status === 'checked-out').length;
            const overdue = booksData.filter(book => book.status === 'overdue').length;
            
            // Update the stat numbers in your HTML
            document.querySelector('.stat.total .stat-number').textContent = total;
            document.querySelector('.stat.available .stat-number').textContent = available;
            document.querySelector('.stat.checked-out .stat-number').textContent = checkedOut;
            document.querySelector('.stat.overdue .stat-number').textContent = overdue;
        }

        function search() {
            var input = document.getElementById('search');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('bookTable');
            var rows = table.getElementsByTagName('tr');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var found = false;
                for (var j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }

        function filterStatus() {
            var select = document.getElementById('filter');
            var filter = select.value;
            var table = document.getElementById('bookTable');
            var rows = table.getElementsByTagName('tr');

            for (var i = 0; i < rows.length; i++) {
                var statusCell = rows[i].getElementsByClassName('status')[0];
                if (!filter || statusCell.classList.contains(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Updated delete function that actually deletes from database
        async function deleteBook(id) {
            if (confirm('Are you sure you want to permanently delete book ' + id + '?')) {
                try {
                    const response = await fetch('http://localhost/Library_Management_System/delete_book.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // Success - reload the books to refresh the display
                        alert('Book deleted successfully!');
                        loadBooks(); // This will refresh the table and stats
                    } else {
                        alert('Error deleting book: ' + result.error);
                    }
                } catch (error) {
                    console.error('Error deleting book:', error);
                    alert('Failed to delete book. Please try again.');
                }
            }
        }

        // Load books when page loads
        window.addEventListener('load', loadBooks);

        // Add event listeners
        document.getElementById('search').addEventListener('input', search);
        document.getElementById('filter').addEventListener('change', filterStatus);
    </script>
</body>
</html>