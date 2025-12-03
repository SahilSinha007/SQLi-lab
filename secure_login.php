<?php
/**
 * SECURE LOGIN SCRIPT
 * This script uses prepared statements to prevent SQL Injection
 * This is the recommended approach for database queries with user input
 */

// Database configuration
$host = 'localhost';
$dbname = 'sqli_lab_db';
$db_username = 'root';  // Default XAMPP MySQL username
$db_password = 'root';      // Default XAMPP MySQL password (empty)

// Establish database connection
$conn = mysqli_connect($host, $db_username, $db_password, $dbname);

// Check connection
if (!$conn) {
    die("<div class='error'>Database connection failed: " . mysqli_connect_error() . "</div>");
}

// Get user input from POST request
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// SECURE QUERY - Using prepared statements with parameter binding
$stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ? AND password = ?");

if ($stmt) {
    // Bind parameters (s = string)
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);

    // Execute the prepared statement
    mysqli_stmt_execute($stmt);

    // Get result
    $result = mysqli_stmt_get_result($stmt);

    // Check if user exists
    if (mysqli_num_rows($result) > 0) {
        // User found - login successful
        $user = mysqli_fetch_assoc($result);
        echo "<div class='success'>Login Successful! Welcome, <strong>" . htmlspecialchars($user['username']) . "</strong> (SECURE)</div>";
        echo "<div class='info'>✓ This login is protected against SQL injection attacks.</div>";
    } else {
        // No user found
        echo "<div class='error'>Login Failed! Invalid credentials.</div>";
    }

    // Close statement
    mysqli_stmt_close($stmt);
} else {
    echo "<div class='error'>Error preparing statement: " . mysqli_error($conn) . "</div>";
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="result-container">
            <h2>🔒 Secure Login Result</h2>
            <!-- PHP output appears above -->
            <div class="back-link">
                <a href="index.html" class="btn">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
