
<?php
session_start();

// Generate CAPTCHA numbers
$number1 = random_int(1, 10);
$number2 = random_int(1, 10);

// Store correct answer in session
$_SESSION['captcha_answer'] = $number1 + $number2;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Management Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h1>Movie Management System</h1>
    <h2>Login</h2>

    <?php
    // Display login error
    if (isset($_SESSION['login_error'])) {
        echo "<p style='color:red;'>" .
             htmlspecialchars(
                 $_SESSION['login_error'],
                 ENT_QUOTES,
                 'UTF-8'
             ) .
             "</p>";

        unset($_SESSION['login_error']);
    }
    ?>

    <form action="login.php" method="post">

        <label>Username:</label><br>
        <input type="text"
               name="username"
               required>
        <br><br>

        <label>Password:</label><br>
        <input type="password"
               name="password"
               required>
        <br><br>

        <label>
            CAPTCHA: What is
            <?php echo $number1; ?> +
            <?php echo $number2; ?>?
        </label>
        <br>

        <input type="number"
               name="captcha"
               required>
        <br><br>

        <input type="submit" value="Login">

    </form>

</body>
</html>
