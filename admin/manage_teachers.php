<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of teachers per page
$offset = ($page - 1) * $limit;

// Get total number of teachers
$total_teachers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$total_pages = ceil($total_teachers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Teachers</h2>

        <!-- Add Teacher Form -->
        <form id="add-teacher-form">
            <input type="text" name="name" id="name" placeholder="Name" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="submit"  class="blue-btn">Add Teacher</button>
        </form>

        <div id="success-message" style="display:none; color:green;">Teacher added successfully!</div>
        <div id="error-message" style="display:none; color:red;">Error adding teacher. Please try again.</div>

        <!-- View Teachers Button -->
        <button id="view-teachers-btn"  class="green-btn">View Teachers</button>

        <!-- Teacher Table -->
        <table id="teacher-table" border="1" style="display:none; margin-top:20px;">
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Teacher data will be injected here via AJAX -->
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

        <!-- Edit Teacher Modal (Hidden by default) -->
        <div id="edit-teacher-modal" style="display:none;">
            <h2>Edit Teacher</h2>
            <form id="edit-teacher-form">
                <input type="hidden" name="teacher_id" id="edit-teacher-id">
                <input type="text" name="name" id="edit-teacher-name" placeholder="Name" required>
                <input type="email" name="email" id="edit-teacher-email" placeholder="Email" required>
                <button type="submit"  class="green-btn">Update Teacher</button>
            </form>
        </div>

        <div id="edit-success-message" style="display:none; color:green;">Teacher updated successfully!</div>
        <div id="edit-error-message" style="display:none; color:red;">Error updating teacher. Please try again.</div>

    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var totalPages = <?php echo $total_pages; ?>;

            // Add Teacher Form Submission
            $('#add-teacher-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_teacher_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#success-message').show();
                            $('#error-message').hide();
                            $('#add-teacher-form')[0].reset();
                            $('#view-teachers-btn').trigger('click'); // Refresh the teacher list
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

            // View Teachers Button Click
            $('#view-teachers-btn').on('click', function () {
                loadTeachers(currentPage); // Load page 1 initially
            });

            // Function to load teachers with pagination
            function loadTeachers(page) {
                $.ajax({
                    url: 'view_teachers_ajax.php',
                    method: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#teacher-table').show();
                            $('#pagination').show();
                            var teachers = response.teachers;
                            var teacherTable = $('#teacher-table tbody');
                            teacherTable.empty();

                            // Populate the teacher table
                            teachers.forEach(function (teacher) {
                                var row = `
                                    <tr>
                                        <td>${teacher.user_id}</td>
                                        <td>${teacher.name}</td>
                                        <td>${teacher.email}</td>
                                        <td>
                                            <button class="edit-teacher edit-button" data-id="${teacher.user_id}">Edit</button>
                                            <button class="delete-teacher delete-button" data-id="${teacher.user_id}">Delete</button>
                                        </td>
                                    </tr>
                                `;
                                teacherTable.append(row);
                            });

                            // Update pagination controls
                            updatePagination(page, response.total_pages, response.total_entries, response.entries_per_page);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log('Error:', textStatus, errorThrown);
                        alert('Error loading teachers.');
                    }
                });
            }

            // Function to update pagination controls
            function updatePagination(page, totalPages, totalEntries, entriesPerPage) {
                currentPage = page;
                let startEntry = (page - 1) * entriesPerPage + 1;
                let endEntry = Math.min(page * entriesPerPage, totalEntries);

                $('#entries-info').html(
                    'Showing ' + startEntry + ' to ' + endEntry + ' of ' + totalEntries + ' entries'
                );

                $('#prev-page').toggleClass('disabled', page <= 1);
                $('#next-page').toggleClass('disabled', page >= totalPages);

                var pageNumbers = '';
                for (var i = 1; i <= totalPages; i++) {
                    pageNumbers += '<a class="paginate_button ' + (i === page ? 'current' : '') + '" data-page="' + i + '">' + i + '</a>';
                }
                $('#page-numbers').html(pageNumbers);
            }

            // Pagination buttons click handler
            $(document).on('click', '.paginate_button:not(.disabled)', function () {
                var pageNum = $(this).data('page');
                if (pageNum) {
                    loadTeachers(pageNum);
                } else if ($(this).hasClass('previous')) {
                    loadTeachers(currentPage - 1);
                } else if ($(this).hasClass('next')) {
                    loadTeachers(currentPage + 1);
                }
            });

            // Edit Teacher
            $(document).on('click', '.edit-teacher', function () {
                var teacherId = $(this).data('id');
                $.ajax({
                    url: 'get_teacher_details.php',
                    method: 'GET',
                    data: { teacher_id: teacherId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var teacher = response.teacher;
                            $('#edit-teacher-id').val(teacher.user_id);
                            $('#edit-teacher-name').val(teacher.name);
                            $('#edit-teacher-email').val(teacher.email);

                            // Hide the teacher table and show the edit form
                            $('#teacher-table').hide();
                            $('#edit-teacher-modal').show(); // Show edit form
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error fetching teacher details.');
                    }
                });
            });

            // Edit Teacher Form Submission
            $('#edit-teacher-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_teacher_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#edit-success-message').show();
                            $('#edit-error-message').hide();
                            $('#edit-teacher-modal').hide(); // Hide edit form
                            $('#view-teachers-btn').trigger('click'); // Refresh teacher list
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

            // Delete Teacher
            $(document).on('click', '.delete-teacher', function () {
                if (confirm('Are you sure you want to delete this teacher?')) {
                    var teacherId = $(this).data('id');
                    $.ajax({
                        url: 'delete_teacher_ajax.php',
                        method: 'POST',
                        data: { teacher_id: teacherId },
                        success: function (response) {
                            if (response === 'success') {
                                $('#view-teachers-btn').trigger('click'); // Refresh teacher list
                            } else {
                                alert('Error deleting teacher.');
                            }
                        },
                        error: function () {
                            alert('Error deleting teacher.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
