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
            <input type="text" placeholder="Search users..." id="search">
        </div>
        <div class="filter">
            <select id="filter">
                <option value="">All Users</option>
                <option value="available">Eligible</option>
                <option value="checked-out">Non-Eligible</option>
            </select>
        </div>
        <button class="add-btn" onclick="window.location.href='register_user.php'">Register User</button>

    </div>

    <div class="stats">
        <div class="stat total">
            <div class="stat-number">0</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat available">
            <div class="stat-number">0</div>
            <div class="stat-label">Eligible</div>
        </div>
        <div class="stat checked-out">
            <div class="stat-number">0</div>
            <div class="stat-label">Non-Eligible</div>
        </div>
        <!--
        <div class="stat overdue">
            <div class="stat-number">0</div>
            <div class="stat-label">Overdue</div>
        </div>
        -->
    </div>

    <div class="Users">
        <h2>User List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <!-- Users will be loaded from database -->
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
        let usersData = [];

        // Load users from backend when page loads
        async function loadUsers() {
            try {
                const response = await fetch('http://localhost/Library_Management_System/get_users.php');
                if (!response.ok) {
                    throw new Error('Failed to fetch users');
                }
                usersData = await response.json();
                displayUsers();
                updateStats();
            } catch (error) {
                console.error('Error loading Users:', error);
                alert('Failed to load Users from database');
            }
        }

        function displayUsers() {
            const tbody = document.getElementById('userTable');
            tbody.innerHTML = '';
            
            usersData.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.first_name}</td>
                    <td>${user.last_name}</td>
                    <td><span class="status ${user.status}">${capitalizeStatus(user.status)}</span></td>
                    <td>
                        <div class="actions">
                            <button class="btn edit" onclick="window.location.href='User_Info.php?id=${user.id}'">Edit</button>
                            <button class="btn delete" onclick="deleteUser('${user.id}')">Delete</button>
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
            const total = usersData.length;
            const available = usersData.filter(user => user.status === 'available').length;
            const checkedOut = usersData.filter(user => user.status === 'checked-out').length;
            
            document.querySelector('.stat.total .stat-number').textContent = total;
            document.querySelector('.stat.available .stat-number').textContent = available;
            document.querySelector('.stat.checked-out .stat-number').textContent = checkedOut;
        }

        function search() {
            var input = document.getElementById('search');
            var filter = input.value.trim().toLowerCase();
            var table = document.getElementById('userTable');
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
            var table = document.getElementById('userTable');
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

        function editUser(id) {
            alert('Edit user ' + id);
        }

        function deleteUser(id) {
            if (confirm('Delete user ' + id + '?')) {
                // Find and remove the row
                var rows = document.getElementById('userTable').getElementsByTagName('tr');
                for (var i = 0; i < rows.length; i++) {
                    if (rows[i].getElementsByTagName('td')[0].textContent === id) {
                        rows[i].remove();
                        break;
                    }
                }
            }
        }

        // Load books when page loads
        window.addEventListener('load', loadUsers);

        // Add event listeners
        document.getElementById('search').addEventListener('input', search);
        document.getElementById('filter').addEventListener('change', filterStatus);
    </script>
</body>
</html>