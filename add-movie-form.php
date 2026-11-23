<?php
require_once 'auth.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Movie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 650px;">

    <h2 class="mb-4">Add New Movie</h2>

    <form action="add-movie.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
            <label class="form-label">Movie Name</label>
            <input type="text" name="movie_name"
                   class="form-control" maxlength="200" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Genre</label>
            <input type="text" name="genre"
                   class="form-control" maxlength="100" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (£)</label>
            <input type="number" name="price"
                   class="form-control"
                   min="0" step="0.01" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Release Date</label>
            <input type="date" name="date_of_release"
                   class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">
            Add Movie
        </button>

        <a href="list-movies.php" class="btn btn-secondary">
            Back to Movies
        </a>

    </form>
</div>

</body>
</html>
