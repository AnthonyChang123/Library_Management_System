<?php
    include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Rentals</title>
</head>
<body>

    <div class="controls">
        <div class="search-box">
            <input type="text" placeholder="Search rentals..." id="search">
        </div>
        <div class="filter">
            <select id="filter">
                <option value="">All Rentals</option>
                <option value="active">Active</option>
                <option value="overdue">Overdue</option>
                <option value="returned">Returned</option>
            </select>
        </div>
        <button class="add-btn" onclick="window.location.href='checkout_book.php'">Check Out Book</button>
        <button class="add-btn" onclick="window.location.href='checkin_book.php'">Check In Book</button>
    </div>

    <div class="stats">
        <div class="stat total">
            <div class="stat-number">0</div>
            <div class="stat-label">Total Rentals</div>
        </div>
        <div class="stat available">
            <div class="stat-number">0</div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat checked-out">
            <div class="stat-number">0</div>
            <div class="stat-label">Overdue</div>
        </div>
        <div class="stat overdue">
            <div class="stat-number">0</div>
            <div class="stat-label">Returned</div>
        </div>
    </div>

    <div class="inventory">
        <h2>Current Rentals</h2>
        <table>
            <thead>
                <tr>
                    <th>Rental ID</th>
                    <th>User</th>
                    <th>Book</th>
                    <th>Author</th>
                    <th>Checked Out</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="rentalsTable">
                <!-- Rentals will be loaded from database -->
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
        let rentalsData = []; // Will hold rentals from database

        // Load rentals from backend when page loads
        async function loadRentals() {
            try {
                const response = await fetch('http://localhost/Library_Management_System/get_rentals.php');
                if (!response.ok) {
                    throw new Error('Failed to fetch rentals');
                }
                rentalsData = await response.json();
                displayRentals();
                updateStats();
            } catch (error) {
                console.error('Error loading rentals:', error);
                alert('Failed to load rentals from database');
            }
        }

        function displayRentals() {
            const tbody = document.getElementById('rentalsTable');
            tbody.innerHTML = '';
            
            rentalsData.forEach(rental => {
                const row = document.createElement('tr');
                const dueDate = new Date(rental.Due_Date);
                const checkoutDate = new Date(rental.Checked_Out_Date);
                
                row.innerHTML = `
                    <td>${rental.Rental_ID}</td>
                    <td>${rental.Renter_FirstName} ${rental.Renter_LastName}</td>
                    <td>${rental.title}</td>
                    <td>${rental.author}</td>
                    <td>${checkoutDate.toLocaleDateString()}</td>
                    <td>${dueDate.toLocaleDateString()}</td>
                    <td><span class="status ${rental.Status}">${capitalizeStatus(rental.Status)}</span></td>
                    <td>
                        <div class="actions">
                            ${rental.Status === 'active' || rental.Status === 'overdue' ? 
                                `<button class="btn edit" onclick="returnBook('${rental.Rental_ID}', '${rental.Book_ID}')">Return</button>` : 
                                '<span style="color: #666;">Returned</span>'
                            }
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
            const total = rentalsData.length;
            const active = rentalsData.filter(rental => rental.Status === 'active').length;
            const overdue = rentalsData.filter(rental => rental.Status === 'overdue').length;
            const returned = rentalsData.filter(rental => rental.Status === 'returned').length;
            
            document.querySelector('.stat.total .stat-number').textContent = total;
            document.querySelector('.stat.available .stat-number').textContent = active;
            document.querySelector('.stat.checked-out .stat-number').textContent = overdue;
            document.querySelector('.stat.overdue .stat-number').textContent = returned;
        }

        function search() {
            var input = document.getElementById('search');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('rentalsTable');
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
            var table = document.getElementById('rentalsTable');
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

        
        async function returnBook(rentalId, bookId) {
            if (confirm('Mark this book as returned?')) {
                try {
                    const response = await fetch('http://localhost/Library_Management_System/return_book.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            rental_id: rentalId,
                            book_id: bookId 
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        alert('Book returned successfully!');
                        loadRentals(); 
                    } else {
                        alert('Error returning book: ' + result.error);
                    }
                } catch (error) {
                    console.error('Error returning book:', error);
                    alert('Failed to return book. Please try again.');
                }
            }
        }

        
        window.addEventListener('load', loadRentals);

        
        document.getElementById('search').addEventListener('input', search);
        document.getElementById('filter').addEventListener('change', filterStatus);
    </script>
</body>
</html>