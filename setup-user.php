
<?php
require_once 'db.php';

// Only allow setup from the command line
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Access denied.");
}

// Ask for username and password
echo "Enter username: ";
$username = trim(fgets(STDIN));

echo "Enter password: ";
$password = rtrim(fgets(STDIN), "\r\n");

// Validate inputs
if (
    $username === '' ||
    strlen($username) > 100 ||
    strlen($password) < 12
) {
    exit("Invalid username or password too short.\n");
}

// Hash the password securely
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Insert user into database
$sql = "INSERT INTO users (username, password_hash)
        VALUES (?, ?)";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $username, $password_hash);

try {
    $stmt->execute();
    echo "User account created successfully.\n";
} catch (mysqli_sql_exception $e) {
    echo "Unable to create account. Username may already exist.\n";
}

$stmt->close();
?>
