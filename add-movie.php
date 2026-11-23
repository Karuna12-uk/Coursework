<?php
require_once 'auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-movie-form.php");
    exit;
}
require_once 'auth.php';
// Connect to database
require_once 'db.php';

// Check form security token
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('Invalid security token.');
}

// Get submitted data
$movie_name = trim($_POST['movie_name'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$price = $_POST['price'] ?? '';
$date_of_release = trim($_POST['date_of_release'] ?? '');

// Validate required fields
if (
    $movie_name === '' ||
    $genre === '' ||
    $price === '' ||
    $date_of_release === ''
) {
    exit("Please fill in all fields.");
}

// Validate movie name and genre
if (strlen($movie_name) > 200 || strlen($genre) > 100) {
    exit("Movie name or genre is too long.");
}

// Validate price
if (
    !is_numeric($price) ||
    !is_finite((float)$price) ||
    (float)$price < 0
) {
    exit("Please enter a valid price.");
}

// Validate release date
$date = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $date_of_release
);

if (
    !$date ||
    $date->format('Y-m-d') !== $date_of_release
) {
    exit("Please enter a valid release date.");
}

// Prepared SQL statement
$sql = "INSERT INTO movies
        (movie_name, genre, price, date_of_release)
        VALUES (?, ?, ?, ?)";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    exit("Unable to prepare database query.");
}

// Bind parameters
$stmt->bind_param(
    "ssds",
    $movie_name,
    $genre,
    $price,
    $date_of_release
);

// Insert movie
if (!$stmt->execute()) {
    exit("Unable to add movie.");
}

// Close statement
$stmt->close();

// Redirect to movie list
header("Location: list-movies.php");
exit;
?>
