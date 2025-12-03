<?php
/**
 * VULNERABLE LOGIN SCRIPT
 * WARNING: This script is intentionally vulnerable to SQL Injection
 * DO NOT use this code in production environments!
 */

// Database configuration
$host = 'localhost';
$dbname = 'sqli_lab_db';
$db_username = 'root';  // Default XAMPP MySQL username
$db_password = 'root';      // Default XAMPP MySQL password (empty)

// Establish database connection
$conn = @mysqli_connect($host, $db_username, $db_password, $dbname);

// Check connection
if (!$conn) {
    die("<div class='error'>Database connection failed: " . mysqli_connect_error() . "</div>");
}

// Get user input from POST request
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// VULNERABLE QUERY - Direct string concatenation (DO NOT USE IN REAL APPLICATIONS)
$query = "SELECT id, username, password FROM users WHERE username = '$username' AND password = '$password'";

// Execute query with error suppression for blind SQLi scenarios
$result = @mysqli_query($conn, $query);

// Check if query execution was successful
if ($result === false) {
    // Query failed - could be due to syntax error from malicious input
    echo "<div class='error'>Login Failed! Invalid credentials.</div>";
} else {
    // Query executed successfully
    if (mysqli_num_rows($result) > 0) {
        // User found - login successful
        $user = mysqli_fetch_assoc($result);
        echo "<div class='success'>Login Successful! Welcome, <strong>" . htmlspecialchars($user['username']) . "</strong> (VULNERABLE)</div>";

        // Display all returned data (useful for UNION-based attacks)
        echo "<div class='data-display'>";
        echo "<h4>Retrieved Data:</h4>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        echo "</div>";
    } else {
        // No user found
        echo "<div class='error'>Login Failed! Invalid credentials.</div>";
    }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="result-container">
            <h2>🔓 Vulnerable Login Result</h2>
            <!-- PHP output appears above -->
            <div class="back-link">
                <a href="index.html" class="btn">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
