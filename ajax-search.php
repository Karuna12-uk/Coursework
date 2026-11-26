
<?php
require_once 'auth.php';
require_once 'db.php';

// Tell browser this is a JSON response
header('Content-Type: application/json; charset=UTF-8');

// Read search keyword
$keyword = trim($_GET['keyword'] ?? '');

if (strlen($keyword) > 100) {
    http_response_code(400);
    echo json_encode(['error' => 'Search is too long']);
    exit;
}

// Search all five movie fields
$sql = "SELECT movie_id, movie_name, genre,
               price, date_of_release
        FROM movies
        WHERE movie_name LIKE ?
           OR genre LIKE ?
           OR CAST(price AS CHAR) LIKE ?
           OR CAST(date_of_release AS CHAR) LIKE ?
           OR CAST(movie_id AS CHAR) LIKE ?
        ORDER BY date_of_release
        LIMIT 50";

$search = "%" . $keyword . "%";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param(
    "sssss",
    $search,
    $search,
    $search,
    $search,
    $search
);

$stmt->execute();

$result = $stmt->get_result();

$movies = [];

while ($row = $result->fetch_assoc()) {
    $movies[] = $row;
}

$stmt->close();

echo json_encode($movies);
?>
