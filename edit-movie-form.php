<?php
// Connect to database
require_once 'auth.php';
require_once 'db.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Validate movie ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid movie ID.");
}

// Fetch movie using a prepared statement
$sql = "SELECT * FROM movies WHERE movie_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    exit("Movie not found.");
}

// Escape output
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Movie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:650px;">

    <h2 class="mb-4">Edit Movie</h2>

    <form action="update-movie.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <input type="hidden"
               name="movie_id"
               value="<?= e($movie['movie_id']) ?>">

        <div class="mb-3">
            <label class="form-label">Movie Name</label>
            <input type="text"
                   name="movie_name"
                   class="form-control"
                   value="<?= e($movie['movie_name']) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Genre</label>
            <input type="text"
                   name="genre"
                   class="form-control"
                   value="<?= e($movie['genre']) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (£)</label>
            <input type="number"
                   name="price"
                   class="form-control"
                   min="0"
                   step="0.01"
                   value="<?= e($movie['price']) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Release Date</label>
            <input type="date"
                   name="date_of_release"
                   class="form-control"
                   value="<?= e($movie['date_of_release']) ?>"
                   required>
        </div>

        <button type="submit" class="btn btn-primary">
            Update Movie
        </button>

        <a href="list-movies.php" class="btn btn-secondary">
            Cancel
        </a>

    </form>
</div>

</body>
</html>
