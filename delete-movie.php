
<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

$movie_id = filter_input(
    INPUT_POST,
    'movie_id',
    FILTER_VALIDATE_INT
);

$token = $_POST['csrf_token'] ?? '';

if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

if (!$movie_id || $movie_id < 1) {
    exit("Invalid movie ID.");
}

$stmt = $mysqli->prepare(
    "DELETE FROM movies WHERE movie_id = ?"
);

$stmt->bind_param("i", $movie_id);
$stmt->execute();
$stmt->close();

header("Location: list-movies.php");
exit;
?>
