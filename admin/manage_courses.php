<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch all teachers for the dropdown
$teachers = $conn->query("SELECT user_id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of courses per page
$offset = ($page - 1) * $limit;

// Get total number of courses
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_pages = ceil($total_courses / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    
        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
</style>
<body>
    <div class="container">
        <h2>Manage Courses</h2>

        <!-- Add a new "Back to Manage Courses" button -->
        <button id="back-to-manage" class="green-btn" style="display:none;">Back to Manage Courses</button>

        <!-- Add Course Form -->
        <form id="add-course-form">
            <input type="text" name="course_name" id="course_name" placeholder="Course Name" required>
            <textarea name="course_description" id="course_description" placeholder="Course Description" required></textarea>

            <!-- Dropdown to Select a Teacher -->
            <select name="teacher_id" id="teacher_id" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['user_id']; ?>"><?php echo $teacher['name']; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit"  class="blue-btn">Add Course</button>
        </form>

        <div id="success-message" style="display: none;">Course added successfully!</div>
        <div id="error-message" style="display: none;">Error adding course. Please try again.</div>

        <button id="view-courses-btn" class="green-btn">View Courses</button>

        <table id="course-table" border="1" style="display:none; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Teacher Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic course rows will be added here -->
            </tbody>
        </table>

        <!-- Pagination controls -->
        <div id="pagination" class="dataTables_wrapper">
            <div class="dataTables_info" id="entries-info" role="status" aria-live="polite">
                Showing <span id="start-entry">1</span> to <span id="end-entry">10</span> of <span id="total-entries">0</span> entries
            </div>
            <div class="dataTables_paginate paging_simple_numbers">
                <a class="paginate_button previous disabled" id="prev-page" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="-1">Previous</a>
                <span id="page-numbers"></span>
                <a class="paginate_button next" id="next-page" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0">Next</a>
            </div>
        </div>

        <!-- Edit Course Form (Hidden by default) -->
        <div id="edit-course-modal" style="display:none;">
            <h2>Edit Course</h2>
            <form id="edit-course-form">
                <input type="hidden" name="course_id" id="edit-course-id">
                <input type="text" name="course_name" id="edit-course-name" placeholder="Course Name" required>
                <textarea name="course_description" id="edit-course-description" placeholder="Course Description" required></textarea>

                <!-- Dropdown to Select a Teacher -->
                <select name="teacher_id" id="edit-teacher-id" required>
                    <option value="">Select Teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['user_id']; ?>"><?php echo $teacher['name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="blue-btn">Update Course</button>
                <button type="button" id="cancel-edit" class="red-btn">Cancel</button>
            </form>
        </div>

        <div id="edit-success-message" style="display: none;">Course updated successfully!</div>
        <div id="edit-error-message" style="display: none;">Error updating course. Please try again.</div>
    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var totalPages = <?php echo $total_pages; ?>;

            // Add Course Form Submission
            $('#add-course-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_course_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#success-message').show();
                            $('#error-message').hide();
                            $('#add-course-form')[0].reset();
                            $('#view-courses-btn').trigger('click'); // Refresh the course list
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

            // View Courses Button Click
            $('#view-courses-btn').on('click', function () {
                loadCourses(currentPage);
                $(this).hide();
                $('#back-to-manage').show();
            });

            // Add new event handler for "Back to Manage Courses" button
            $('#back-to-manage').on('click', function() {
                $('#course-table').hide();
                $('#pagination').hide();
                $('#add-course-form').show();
                $('#view-courses-btn').show();
                $(this).hide();
            });

            function loadCourses(page) {
                $('#add-course-form').hide();
                // Remove this line: $('#edit-course-modal').hide();
                $.ajax({
                    url: 'view_courses_ajax.php',
                    method: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#course-table').show();
                            $('#pagination').show();
                            var courses = response.courses;
                            var courseTable = $('#course-table tbody');
                            courseTable.empty();

                            courses.forEach(function (course) {
                                var row = `
                                    <tr data-id="${course.course_id}">
                                        <td>${course.course_id}</td>
                                        <td>${course.course_name}</td>
                                        <td>${course.teacher_name}</td>
                                        <td>
                                            <button class="edit-course blue-btn" data-id="${course.course_id}">Edit</button>
                                            <button class="delete-course delete-button" data-id="${course.course_id}">Delete</button>
                                        </td>
                                    </tr>
                                `;
                                courseTable.append(row);
                            });

                            updatePagination(page, response.total_pages, response.total_entries, response.entries_per_page);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error loading courses.');
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

            $(document).on('click', '.paginate_button:not(.disabled)', function() {
                var pageNum = $(this).data('page');
                if (pageNum) {
                    loadCourses(pageNum);
                } else if ($(this).hasClass('previous')) {
                    loadCourses(currentPage - 1);
                } else if ($(this).hasClass('next')) {
                    loadCourses(currentPage + 1);
                }
            });

            // Edit Course
            $(document).on('click', '.edit-course', function () {
                var courseId = $(this).data('id');
                $.ajax({
                    url: 'get_course_details_ajax.php',
                    method: 'GET',
                    data: { course_id: courseId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var course = response.course;
                            $('#edit-course-id').val(course.course_id);
                            $('#edit-course-name').val(course.course_name);
                            $('#edit-course-description').val(course.course_description);
                            $('#edit-teacher-id').val(course.teacher_id);
                            
                            // Hide the Manage Courses section
                            $('#add-course-form').hide();
                            $('#course-table').hide();
                            $('#pagination').hide();
                            $('#view-courses-btn').hide();
                            
                            // Show Edit Course form
                            $('#edit-course-modal').show();
                            
                            // Scroll to the edit form
                            $('html, body').animate({
                                scrollTop: $("#edit-course-modal").offset().top
                            }, 500);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error fetching course details.');
                    }
                });
            });

            // Cancel Edit
            $('#cancel-edit').on('click', function() {
                $('#edit-course-modal').hide();
                $('#view-courses-btn').show();
                $('#view-courses-btn').trigger('click'); // Refresh course list
            });

            // Edit Course Form Submission
            $('#edit-course-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_course_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#edit-success-message').text(response.message).show();
                            $('#edit-error-message').hide();
                            $('#edit-course-modal').hide();
                            
                            // Update the course in the table
                            updateCourseInTable(response.course);
                            
                            // Show the Manage Courses section again
                            $('#view-courses-btn').show();
                            $('#course-table').show();
                            $('#pagination').show();
                        } else {
                            $('#edit-error-message').text(response.message).show();
                            $('#edit-success-message').hide();
                        }
                    },
                    error: function () {
                        $('#edit-error-message').text('An error occurred while updating the course.').show();
                        $('#edit-success-message').hide();
                    }
                });
            });

            function updateCourseInTable(course) {
                var row = $(`#course-table tr[data-id="${course.course_id}"]`);
                if (row.length) {
                    row.find('td:eq(1)').text(course.course_name);
                    row.find('td:eq(2)').text(course.teacher_name);
                } else {
                    // If the row doesn't exist (e.g., on a different page), reload the entire table
                    loadCourses(currentPage);
                }
            }

            // Delete Course
            $(document).on('click', '.delete-course', function () {
                if (confirm('Are you sure you want to delete this course?')) {
                    var courseId = $(this).data('id');
                    $.ajax({
                        url: 'delete_course_ajax.php',
                        method: 'POST',
                        data: { course_id: courseId },
                        success: function (response) {
                            if (response === 'success') {
                                $('#view-courses-btn').trigger('click'); // Refresh course list
                            } else {
                                alert('Error deleting course.');
                            }
                        },
                        error: function () {
                            alert('Error deleting course.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>