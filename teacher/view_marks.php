<?php
session_start();
if ($_SESSION['role'] != 'teacher') {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks</title>
    <link rel="stylesheet" href="../assets/css/teacherstyle.css">
    <style>
        /* Back to Dashboard Button Styles */
        .back-btn {
            display: inline-block;
            padding: 14px 22px;
            background-color: white;
            color: black;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .back-btn:hover {
            background-color: #007bff;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        /* Center Heading */
        h2 {
            text-align: center;
            color: #333;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            margin-bottom: 20px;
        }

        /* Styling for Dashboard Links */
        .box-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .dashboard-links a.green-btn {
            display: inline-block;
            padding: 14px 22px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .dashboard-links a.green-btn:hover {
            background-color: #218838;
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, #14bbde, #c2c017);
            padding: 20px 0;
            border-bottom: 3px solid #c2c017;
            text-align: center;
        }

        .header-content {
            color: white;
        }

        .header-content h1 {
            font-size: 32px;
            margin: 0;
        }

        .header-content p {
            font-size: 18px;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <h1>Teacher Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a> <!-- Moved Logout Button to right -->
    </header>

    <!-- Main Content -->
    <div class="container">
        <h2>Select Marks Type</h2>

        <!-- Box Links -->
        <div class="box-container">
            <div class="dashboard-links">
                <a href="assignment_marks.php" class="green-btn">Assignment Marks</a>
                <a href="quiz_marks.php" class="green-btn">Quiz Marks</a>
            </div>
        </div>

        <!-- Back to Dashboard Button -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

    </div>

</body>
</html>
