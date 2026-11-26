
<?php
session_start();

// Connect to database
require_once 'db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login-form.php");
    exit;
}

// Get submitted values
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$captcha = trim((string)($_POST['captcha'] ?? ''));

// Check required fields
if (
    $username === '' ||
    $password === '' ||
    $captcha === ''
) {
    $_SESSION['login_error'] = "Please fill in all fields.";
    header("Location: login-form.php");
    exit;
}

// Get expected CAPTCHA answer
$expected = $_SESSION['captcha_answer'] ?? null;

// Remove CAPTCHA after one attempt
unset($_SESSION['captcha_answer']);

// Validate CAPTCHA
if (
    $expected === null ||
    !ctype_digit($captcha) ||
    (int)$captcha !== $expected
) {
    $_SESSION['login_error'] = "Incorrect CAPTCHA answer.";
    header("Location: login-form.php");
    exit;
}

// Find user using prepared SQL statement
$sql = "SELECT user_id, username, password_hash
        FROM users
        WHERE username = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Verify password
if (
    $user &&
    password_verify($password, $user['password_hash'])
) {

    // Regenerate session ID after login
    session_regenerate_id(true);

    // Save login information
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];

    // Redirect to movie list
    header("Location: list-movies.php");
    exit;

} else {

    $_SESSION['login_error'] =
        "Invalid username or password.";

    header("Location: login-form.php");
    exit;
}
?>
