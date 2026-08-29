<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../config/db.php';

// Fetch user details for editing
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE user_id = :user_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    echo "User updated successfully!";
    header("Location: manage_users.php");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>

        <!-- Form to Edit User Details -->
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <input type="text" name="name" value="<?php echo $user['name']; ?>" required>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

            <!-- Dropdown to select user role -->
            <select name="role" required>
                <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="teacher" <?php echo ($user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>

            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
</body>
</html>
