<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of users per page
$offset = ($page - 1) * $limit;

// Get total number of users
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>

        <!-- Add User Form -->
        <form id="add-user-form">
            <input type="text" name="name" id="name" placeholder="Name" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <select name="role" id="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" class="blue-btn">Add User</button>
        </form>

        <div id="success-message" style="display:none; color:green;">User added successfully!</div>
        <div id="error-message" style="display:none; color:red;">Error adding user. Please try again.</div>

        <!-- View Users Button -->
        <button id="view-users-btn"  class="green-btn">View Users</button>

        <!-- Users Table -->
        <table id="user-table" border="1" style="display:none; margin-top:20px;">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- User data will be injected here via AJAX -->
            </tbody>
        </table>

        <!-- Pagination controls -->
        <div id="pagination" class="dataTables_wrapper" style="display:none; margin-top: 20px;">
            <div class="dataTables_info" id="entries-info" role="status" aria-live="polite">
                Showing <span id="start-entry">1</span> to <span id="end-entry">10</span> of <span id="total-entries">0</span> entries
            </div>
            <div class="dataTables_paginate paging_simple_numbers">
                <a class="paginate_button previous disabled" id="prev-page" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="-1">Previous</a>
                <span id="page-numbers"></span>
                <a class="paginate_button next" id="next-page" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0">Next</a>
            </div>
        </div>

        <!-- Edit User Modal (Hidden by default) -->
        <div id="edit-user-modal" style="display:none;">
            <h2>Edit User</h2>
            <form id="edit-user-form">
                <input type="hidden" name="user_id" id="edit-user-id">
                <input type="text" name="name" id="edit-user-name" placeholder="Name" required>
                <input type="email" name="email" id="edit-user-email" placeholder="Email" required>
                <select name="role" id="edit-user-role" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit"  class="green-btn">Update User</button>
            </form>
        </div>

        <div id="edit-success-message" style="display:none; color:green;">User updated successfully!</div>
        <div id="edit-error-message" style="display:none; color:red;">Error updating user. Please try again.</div>

    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var totalPages = <?php echo $total_pages; ?>;
            var entriesPerPage = 10;

            // Add User Form Submission
            $('#add-user-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_user_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#success-message').show();
                            $('#error-message').hide();
                            $('#add-user-form')[0].reset();
                            $('#view-users-btn').trigger('click'); // Refresh the user list
                        } else {
                            $('#error-message').show();
                            $('#success-message').hide();
                        }
                    },
                    error: function () {
                        $('#error-message').show();
                        $('#success-message').hide();
                    }
                });
            });

            // View Users Button Click
            $('#view-users-btn').on('click', function () {
                loadUsers(currentPage);
                $(this).hide();
            });

            function loadUsers(page) {
                $('#add-user-form').hide();
                $.ajax({
                    url: 'view_users_ajax.php',
                    method: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#user-table').show();
                            $('#pagination').show();
                            var users = response.users;
                            var userTable = $('#user-table tbody');
                            userTable.empty();

                            users.forEach(function (user) {
                                var row = `
                                    <tr>
                                        <td>${user.user_id}</td>
                                        <td>${user.name}</td>
                                        <td>${user.email}</td>
                                        <td>${user.role}</td>
                                        <td>
                                            <button class="edit-user edit-button" data-id="${user.user_id}">Edit</button>
                                            <button class="delete-user delete-button" data-id="${user.user_id}">Delete</button>
                                        </td>
                                    </tr>
                                `;
                                userTable.append(row);
                            });

                            updatePagination(page, response.total_pages, response.total_entries, response.entries_per_page);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error loading users.');
                    }
                });
            }

            function updatePagination(page, totalPages, totalEntries, entriesPerPage) {
                currentPage = page;
                var startEntry = (page - 1) * entriesPerPage + 1;
                var endEntry = Math.min(page * entriesPerPage, totalEntries);

                $('#entries-info').html(
                    'Showing <span id="start-entry">' + startEntry + '</span> to <span id="end-entry">' + endEntry + '</span> of <span id="total-entries">' + totalEntries + '</span> entries'
                );

                $('#prev-page').toggleClass('disabled', page <= 1);
                $('#next-page').toggleClass('disabled', page >= totalPages);

                var pageNumbers = '';
                for (var i = 1; i <= totalPages; i++) {
                    pageNumbers += '<a class="paginate_button ' + (i === page ? 'current' : '') + '" data-page="' + i + '">' + i + '</a>';
                }
                $('#page-numbers').html(pageNumbers);
            }

            $(document).on('click', '.paginate_button:not(.disabled)', function () {
                var pageNum = $(this).data('page');
                if (pageNum) {
                    loadUsers(pageNum);
                } else if ($(this).hasClass('previous')) {
                    loadUsers(currentPage - 1);
                } else if ($(this).hasClass('next')) {
                    loadUsers(currentPage + 1);
                }
            });

            

            // Edit User
            $(document).on('click', '.edit-user', function () {
                var userId = $(this).data('id');
                $.ajax({
                    url: 'get_user_details.php',
                    method: 'GET',
                    data: { user_id: userId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var user = response.user;
                            $('#edit-user-id').val(user.user_id);
                            $('#edit-user-name').val(user.name);
                            $('#edit-user-email').val(user.email);
                            $('#edit-user-role').val(user.role);

                            // Hide the user table and show the edit form
                            $('#user-table').hide();
                            $('#edit-user-modal').show(); // Show edit form
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error fetching user details.');
                    }
                });
            });

            // Edit User Form Submission
            $('#edit-user-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_user_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#edit-success-message').show();
                            $('#edit-error-message').hide();
                            $('#edit-user-modal').hide(); // Hide edit form
                            $('#view-users-btn').trigger('click'); // Refresh user list
                        } else {
                            $('#edit-error-message').show();
                            $('#edit-success-message').hide();
                        }
                    },
                    error: function () {
                        $('#edit-error-message').show();
                        $('#edit-success-message').hide();
                    }
                });
            });

            // Delete User
            $(document).on('click', '.delete-user', function () {
                if (confirm('Are you sure you want to delete this user?')) {
                    var userId = $(this).data('id');
                    $.ajax({
                        url: 'delete_user_ajax.php',
                        method: 'POST',
                        data: { user_id: userId },
                        success: function (response) {
                            if (response === 'success') {
                                $('#view-users-btn').trigger('click'); // Refresh user list
                            } else {
                                alert('Error deleting user.');
                            }
                        },
                        error: function () {
                            alert('Error deleting user.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
