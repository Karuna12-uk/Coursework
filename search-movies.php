
<?php
require_once 'auth.php';
require_once 'db.php';

// Function to safely display HTML
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get text from search bar
$keywords = trim($_GET['keywords'] ?? '');

// Check search input
if ($keywords === '' || strlen($keywords) > 300) {
    exit("Please enter a valid search.");
}

// Split search into terms.
// Quoted phrases are kept together.
preg_match_all(
    '/[^\s"]+:"[^"]+"|"[^"]+"|\S+/',
    $keywords,
    $matches
);

$terms = $matches[0];

// Start SQL query
$sql = "SELECT * FROM movies WHERE 1=1";

$params = [];
$types = "";

// Check each search term
foreach ($terms as $term) {

    // Search movie name
    if (preg_match('/^name:(.+)$/i', $term, $m)) {

        $value = trim($m[1], '"');

        $sql .= " AND movie_name LIKE ?";
        $params[] = "%" . $value . "%";
        $types .= "s";

    }

    // Search genre
    elseif (preg_match('/^genre:(.+)$/i', $term, $m)) {

        $value = trim($m[1], '"');

        $sql .= " AND genre LIKE ?";
        $params[] = "%" . $value . "%";
        $types .= "s";

    }

    // Search price range, e.g. price:5-15
    elseif (preg_match(
        '/^price:(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?)$/i',
        $term,
        $m
    )) {

        $min_price = (float)$m[1];
        $max_price = (float)$m[2];

        if (
            !is_finite($min_price) ||
            !is_finite($max_price) ||
            $min_price > $max_price
        ) {
            exit("Invalid price range.");
        }

        $sql .= " AND price BETWEEN ? AND ?";

        $params[] = $min_price;
        $params[] = $max_price;

        $types .= "dd";

    }

    // Search exact price, e.g. price:10
    elseif (preg_match(
        '/^price:(\d+(?:\.\d+)?)$/i',
        $term,
        $m
    )) {

        $price = (float)$m[1];

        if (!is_finite($price)) {
            exit("Invalid price.");
        }

        $sql .= " AND price = ?";
        $params[] = $price;
        $types .= "d";

    }

    // Search release year, e.g. year:2024
    elseif (preg_match('/^year:(\d{4})$/i', $term, $m)) {

        $year = (int)$m[1];

        $sql .= " AND YEAR(date_of_release) = ?";
        $params[] = $year;
        $types .= "i";

    }

    // Search exact release date
    elseif (preg_match(
        '/^date:(\d{4}-\d{2}-\d{2})$/i',
        $term,
        $m
    )) {

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $m[1]
        );

        if (
            !$date ||
            $date->format('Y-m-d') !== $m[1]
        ) {
            exit("Invalid release date.");
        }

        $sql .= " AND date_of_release = ?";
        $params[] = $m[1];
        $types .= "s";

    }

    // Search movie ID, e.g. id:3
    elseif (preg_match('/^id:(\d+)$/i', $term, $m)) {

        $id = filter_var(
            $m[1],
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id < 1) {
            exit("Invalid movie ID.");
        }

        $sql .= " AND movie_id = ?";
        $params[] = $id;
        $types .= "i";

    }

    // Reject incorrect search commands
    elseif (strpos($term, ':') !== false) {

        exit("Invalid search command: " . e($term));

    }

    // NORMAL SEARCH: Search all five fields
    else {

        $value = trim($term, '"');
        $search = "%" . $value . "%";

        $sql .= " AND (
            movie_name LIKE ?
            OR genre LIKE ?
            OR CAST(price AS CHAR) LIKE ?
            OR CAST(date_of_release AS CHAR) LIKE ?
            OR CAST(movie_id AS CHAR) LIKE ?
        )";

        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;

        $types .= "sssss";

    }
}

// Order search results
$sql .= " ORDER BY date_of_release";

// Prepare SQL statement
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    exit("Unable to prepare search query.");
}

// Bind parameters
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute search
$stmt->execute();

$results = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Search Results</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1>Movie Search Results</h1>

<p>
    You searched for:
    <strong><?= e($keywords) ?></strong>
</p>

<a href="list-movies.php">
    Back to Movie List
</a>

<br><br>

<p>
    Total movies found:
    <?= $results->num_rows ?>
</p>

<table border="1" cellpadding="10" cellspacing="0">

    <tr>
        <th>Movie ID</th>
        <th>Movie Name</th>
        <th>Genre</th>
        <th>Price</th>
        <th>Release Date</th>
        <th>Actions</th>
    </tr>

    <?php if ($results->num_rows === 0): ?>

    <tr>
        <td colspan="6">
            No movies found.
        </td>
    </tr>

    <?php else: ?>

        <?php while ($movie = $results->fetch_assoc()): ?>

        <tr>
            <td><?= (int)$movie['movie_id'] ?></td>

            <td><?= e($movie['movie_name']) ?></td>

            <td><?= e($movie['genre']) ?></td>

            <td>£<?= e($movie['price']) ?></td>

            <td><?= e($movie['date_of_release']) ?></td>

            <td>
                <a href="movie-details.php?id=<?= (int)$movie['movie_id'] ?>">
                    View
                </a>

                |

                <a href="edit-movie-form.php?id=<?= (int)$movie['movie_id'] ?>">
                    Edit
                </a>
            </td>
        </tr>

        <?php endwhile; ?>

    <?php endif; ?>

</table>

</body>
</html>

<?php
$stmt->close();
?>
