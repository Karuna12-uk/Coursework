<?php
require_once 'auth.php';
require_once 'db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid movie ID.');
}
$stmt = $mysqli->prepare('SELECT movie_name, genre, price, date_of_release FROM movies WHERE movie_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$movie) {
    http_response_code(404);
    exit('Movie not found.');
}
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8"><title>Movie Details</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
<h1><?= e($movie['movie_name']) ?></h1>
<p>Genre: <?= e($movie['genre']) ?></p>
<p>Price: £<?= e($movie['price']) ?></p>
<p>Release date: <?= e($movie['date_of_release']) ?></p>
<a href="list-movies.php">Back to list</a>
</body></html>
