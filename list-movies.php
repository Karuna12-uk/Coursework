
<?php
// Protect page using login session
require_once 'auth.php';

// Connect to database
require_once 'db.php';

// Create security token for Delete
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all movies
$sql = "SELECT * FROM movies ORDER BY date_of_release";

$results = mysqli_query($mysqli, $sql);

// Function to safely display text
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Movie Management System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <h1>Movie Management System</h1>

    <p>
        Welcome,
        <?= e($_SESSION['username'] ?? 'User') ?>!
    </p>

    <a href="logout.php">Logout</a>

    <br><br>

    <!-- ADD MOVIE BUTTON -->
    <a href="add-movie-form.php"
       class="btn"
       style="background:#198754;">
        + Add New Movie
    </a>

    <br><br>

    <h2>Search Movies</h2>

    <!-- ONE SEARCH BAR -->
    <form action="search-movies.php"
          method="get"
          id="searchForm">

        <input type="text"
               name="keywords"
               id="searchInput"
               placeholder="Search movies..."
               style="width:350px;"
               maxlength="300"
               autocomplete="off"
               required>

        <input type="submit" value="Search">

    </form>


    <p id="searchStatus" aria-live="polite"></p>

    <h2>All Movies</h2>

    <!-- MOVIE TABLE -->
    <table border="1"
           cellpadding="10"
           cellspacing="0"
           id="movieTable">

        <thead>
            <tr>
                <th>Movie ID</th>
                <th>Movie Name</th>
                <th>Genre</th>
                <th>Price</th>
                <th>Release Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="movieTableBody">

            <?php while ($movie = mysqli_fetch_assoc($results)): ?>

            <tr>

                <td>
                    <?= (int)$movie['movie_id'] ?>
                </td>

                <td>
                    <a href="movie-details.php?id=<?= (int)$movie['movie_id'] ?>">
                        <?= e($movie['movie_name']) ?>
                    </a>
                </td>

                <td>
                    <?= e($movie['genre']) ?>
                </td>

                <td>
                    £<?= e($movie['price']) ?>
                </td>

                <td>
                    <?= e($movie['date_of_release']) ?>
                </td>

                <td>

                    <a href="movie-details.php?id=<?= (int)$movie['movie_id'] ?>">
                        View
                    </a>

                    |

                    <a href="edit-movie-form.php?id=<?= (int)$movie['movie_id'] ?>">
                        Edit
                    </a>

                    |

                    <form action="delete-movie.php"
                          method="post"
                          style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to delete this movie?');">

                        <input type="hidden"
                               name="movie_id"
                               value="<?= (int)$movie['movie_id'] ?>">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']) ?>">

                        <input type="submit" value="Delete">

                    </form>

                </td>

            </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>

<!-- AJAX LIVE SEARCH -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("searchInput");

    const tableBody =
        document.getElementById("movieTableBody");

    const searchStatus =
        document.getElementById("searchStatus");

    // Save the original movie list
    const originalHTML = tableBody.innerHTML;

    // CSRF token for Delete
    const csrfToken =
        <?= json_encode(
            $_SESSION['csrf_token'],
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ) ?>;

    // Escape text before adding to HTML
    function escapeHTML(value) {

        return String(value ?? "").replace(
            /[&<>"']/g,
            function (character) {
                return {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;"
                }[character];
            }
        );
    }

    let timer;
    let controller;

    // Search automatically when typing
    searchInput.addEventListener("input", function () {

        clearTimeout(timer);

        if (controller) {
            controller.abort();
        }

        const keyword = searchInput.value.trim();

        // Show all movies when search is empty
        if (keyword === "") {
            tableBody.innerHTML = originalHTML;
            searchStatus.textContent = "";
            return;
        }

        // Advanced commands use the Search button
        if (keyword.includes(":")) {
            tableBody.innerHTML = originalHTML;
            searchStatus.textContent =
                "Press Search to apply advanced criteria.";
            return;
        }

        searchStatus.textContent = "Searching...";

        // Delay requests while typing
        timer = setTimeout(async function () {

            controller = new AbortController();

            try {

                const response = await fetch(
                    "ajax-search.php?keyword=" +
                    encodeURIComponent(keyword),
                    {
                        signal: controller.signal,
                        credentials: "same-origin"
                    }
                );

                if (!response.ok) {
                    throw new Error("AJAX request failed");
                }

                const movies = await response.json();

                tableBody.innerHTML = "";

                if (movies.length === 0) {

                    tableBody.innerHTML =
                        '<tr><td colspan="6">No movies found.</td></tr>';

                    searchStatus.textContent =
                        "No matching movies.";

                    return;
                }

                movies.forEach(function (movie) {

                    const id = Number(movie.movie_id);

                    if (!Number.isSafeInteger(id) || id < 1) {
                        return;
                    }

                    const row = document.createElement("tr");

                    row.innerHTML = `
                        <td>${id}</td>

                        <td>
                            <a href="movie-details.php?id=${id}">
                                ${escapeHTML(movie.movie_name)}
                            </a>
                        </td>

                        <td>${escapeHTML(movie.genre)}</td>

                        <td>£${escapeHTML(movie.price)}</td>

                        <td>${escapeHTML(movie.date_of_release)}</td>

                        <td>
                            <a href="movie-details.php?id=${id}">
                                View
                            </a>

                            |

                            <a href="edit-movie-form.php?id=${id}">
                                Edit
                            </a>

                            |

                            <form action="delete-movie.php"
                                  method="post"
                                  style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this movie?');">

                                <input type="hidden"
                                       name="movie_id"
                                       value="${id}">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="${escapeHTML(csrfToken)}">

                                <input type="submit" value="Delete">
                            </form>
                        </td>
                    `;

                    tableBody.appendChild(row);

                });

                searchStatus.textContent =
                    movies.length + " movie(s) found.";

            } catch (error) {

                if (error.name !== "AbortError") {

                    searchStatus.textContent =
                        "Live search unavailable. Press Search instead.";

                    console.error(error);
                }
            }

        }, 300);

    });

});
</script>

</body>
</html>
