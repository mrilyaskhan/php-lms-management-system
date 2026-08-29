<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Enroll Student</h2>

        <!-- Enroll Student Form -->
        <form id="enroll-student-form">
            <select name="student_id" id="student_id" required>
                <option value="">Select Student</option>
            </select>
            <select name="course_id" id="course_id" required>
                <option value="">Select Course</option>
            </select>
            <button type="submit"  class="blue-btn">Enroll Student</button>
        </form>

        <div id="success-message" style="display:none; color:green;">Student enrolled successfully!</div>
        <div id="error-message" style="display:none; color:red;">Error enrolling student. Please try again.</div>

        <!-- View Enrollments Button -->
        <button id="view-enrollments-btn"  class="green-btn">View Enrollments</button>

        <!-- Enrollments Table -->
        <table id="enrollments-table" border="1" style="display:none; margin-top:20px;">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Enrollments data will be injected here via AJAX -->
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

        <!-- Edit Enrollment Modal (Hidden by default) -->
        <div id="edit-enrollment-modal" style="display:none;">
            <h2>Edit Enrollment</h2>
            <form id="edit-enrollment-form">
                <input type="hidden" name="enrollment_id" id="edit-enrollment-id">
                <select name="student_id" id="edit-student-id" required>
                    <option value="">Select Student</option>
                </select>
                <select name="course_id" id="edit-course-id" required>
                    <option value="">Select Course</option>
                </select>
                <button type="submit"  class="green-btn">Update Enrollment</button>
            </form>
        </div>

        <div id="edit-success-message" style="display:none; color:green;">Enrollment updated successfully!</div>
        <div id="edit-error-message" style="display:none; color:red;">Error updating enrollment. Please try again.</div>
    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var totalPages = 1;

            // Load students and courses into the form dropdowns
            function loadStudentsAndCourses() {
                $.ajax({
                    url: 'get_students_and_courses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var students = response.students;
                            var courses = response.courses;

                            $('#student_id').empty().append('<option value="">Select Student</option>');
                            $('#edit-student-id').empty().append('<option value="">Select Student</option>');
                            students.forEach(function (student) {
                                $('#student_id, #edit-student-id').append(`<option value="${student.user_id}">${student.name}</option>`);
                            });

                            $('#course_id').empty().append('<option value="">Select Course</option>');
                            $('#edit-course-id').empty().append('<option value="">Select Course</option>');
                            courses.forEach(function (course) {
                                $('#course_id, #edit-course-id').append(`<option value="${course.course_id}">${course.course_name}</option>`);
                            });
                        }
                    }
                });
            }

            // Load students and courses when the page is ready
            loadStudentsAndCourses();

            // Enroll Student Form Submission
            $('#enroll-student-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'enroll_student_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#success-message').show();
                            $('#error-message').hide();
                            $('#enroll-student-form')[0].reset();
                            $('#view-enrollments-btn').trigger('click'); // Refresh the enrollment list
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

            // View Enrollments Button Click
            $('#view-enrollments-btn').on('click', function () {
                loadEnrollments(currentPage); // Load page 1 initially
            });

            // Load enrollments with pagination
            function loadEnrollments(page) {
                $.ajax({
                    url: 'view_enrollments_ajax.php',
                    method: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#enrollments-table').show();
                            $('#pagination').show();
                            var enrollments = response.enrollments;
                            var enrollmentTable = $('#enrollments-table tbody');
                            enrollmentTable.empty();

                            enrollments.forEach(function (enrollment) {
                                var row = `
                                    <tr>
                                        <td>${enrollment.student_name}</td>
                                        <td>${enrollment.course_name}</td>
                                        <td>
                                            <button class="edit-enrollment blue-btn" data-id="${enrollment.enrollment_id}">Edit</button>
                                            <button class="delete-enrollment delete-button" data-id="${enrollment.enrollment_id}">Delete</button>
                                        </td>
                                    </tr>
                                `;
                                enrollmentTable.append(row);
                            });

                            // Update pagination controls
                            updatePagination(page, response.total_pages, response.total_entries, response.entries_per_page);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error loading enrollments.');
                    }
                });
            }

            // Update pagination controls
            function updatePagination(page, totalPages, totalEntries, entriesPerPage) {
                currentPage = page;
                let startEntry = (page - 1) * entriesPerPage + 1;
                let endEntry = Math.min(page * entriesPerPage, totalEntries);

                $('#entries-info').html(
                    'Showing ' + startEntry + ' to ' + endEntry + ' of ' + totalEntries + ' entries'
                );

                $('#prev-page').toggleClass('disabled', page <= 1);
                $('#next-page').toggleClass('disabled', page >= totalPages);

                let pageNumbers = '';
                for (let i = 1; i <= totalPages; i++) {
                    pageNumbers += '<a class="paginate_button ' + (i === page ? 'current' : '') + '" data-page="' + i + '">' + i + '</a>';
                }
                $('#page-numbers').html(pageNumbers);
            }

            // Pagination buttons click handler
            $(document).on('click', '.paginate_button:not(.disabled)', function () {
                var pageNum = $(this).data('page');
                if (pageNum) {
                    loadEnrollments(pageNum);
                } else if ($(this).hasClass('previous')) {
                    loadEnrollments(currentPage - 1);
                } else if ($(this).hasClass('next')) {
                    loadEnrollments(currentPage + 1);
                }
            });

            // Edit Enrollment
            $(document).on('click', '.edit-enrollment', function () {
                var enrollmentId = $(this).data('id');
                $.ajax({
                    url: 'get_enrollment_details.php',
                    method: 'GET',
                    data: { enrollment_id: enrollmentId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            var enrollment = response.enrollment;
                            $('#edit-enrollment-id').val(enrollment.enrollment_id);
                            $('#edit-student-id').val(enrollment.student_id);
                            $('#edit-course-id').val(enrollment.course_id);

                            // Hide the enrollment table and show the edit form
                            $('#enrollments-table').hide();
                            $('#edit-enrollment-modal').show(); // Show edit form
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Error fetching enrollment details.');
                    }
                });
            });

            // Edit Enrollment Form Submission
            $('#edit-enrollment-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'edit_enrollment_ajax.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response === 'success') {
                            $('#edit-success-message').show();
                            $('#edit-error-message').hide();
                            $('#edit-enrollment-modal').hide(); // Hide edit form
                            $('#view-enrollments-btn').trigger('click'); // Refresh enrollment list
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

            // Delete Enrollment
            $(document).on('click', '.delete-enrollment', function () {
                if (confirm('Are you sure you want to delete this enrollment?')) {
                    var enrollmentId = $(this).data('id');
                    $.ajax({
                        url: 'delete_enrollment_ajax.php',
                        method: 'POST',
                        data: { enrollment_id: enrollmentId },
                        success: function (response) {
                            if (response === 'success') {
                                $('#view-enrollments-btn').trigger('click'); // Refresh enrollment list
                            } else {
                                alert('Error deleting enrollment.');
                            }
                        },
                        error: function () {
                            alert('Error deleting enrollment.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
