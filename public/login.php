<?php
// Start de sessie om inlogstatus op te slaan
session_start();
include __DIR__ . '/database.php';

// Initialiseer foutmelding
$error = '';

// Controleer of het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Haal de gegevens op van het formulier
    $email = trim($_POST['emailadress'] ?? ''); // trim verwijdert spaties voor/na het emailadres
    $password = trim($_POST['password'] ?? '');

    // 2. Controleer of velden niet leeg zijn
    if (empty($email) || empty($password)) {
        $error = "Vul zowel je e-mailadres als wachtwoord in.";
    } else {
        // 3. Zoek de gebruiker op in de database
        $sql = "SELECT * FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Gebruiker gevonden: haal de data op
            $row = $result->fetch_assoc();

            // 4. Vergelijk het ingevoerde wachtwoord met de gehashte versie
            if (password_verify($password, $row['password'])) {
                // Login succesvol
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_email'] = $row['user_email'];

                // Doorsturen naar index.php
                header("Location: index.php");
                exit;
            } else {
                // Wachtwoord is onjuist
                $error = "Ongeldige combinatie e-mailadres/wachtwoord.";
            }
        } else {
            // Geen gebruiker gevonden met dit e-mailadres
            $error = "Geen account gevonden met dit e-mailadres.";
        }
    }
}

// Sluit de databaseverbinding
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PinterPal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            background-color: #0a7082;
            color: #000;
            padding: 2vw;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 2px solid #ffc107;
            border-radius: 5px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
            margin: 5vw auto;
        }
        .form-container input {
            width: 80%;
            max-width: 350px;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 2px solid #0a7082;
            font-size: 1rem;
        }
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: center;
        }
        .password-container input {
            flex: 1;
            max-width: 350px;
        }
        .password-toggle {
            position: absolute;
            right: 15%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        .password-toggle:hover {
            transform: scale(1.2);
        }
        .form-container button {
            background-color: #8c52ff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1vw;
            text-transform: uppercase;
            font-weight: bold;
        }
        .form-container button:hover {
            background-color: #7ae614;
        }
        .form-container a {
            color: #ffc107;
            font-weight: bold;
            text-decoration: none;
            margin-top: 15px;
            display: block;
        }
        .form-container a:hover {
            color: #7ae614;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php">
            <h1>PINTERPAL.</h1>
        </a>
        <div class="login-signup">
            <img src="img/pinterpal-logo.jpg" alt="PinterPal Logo" class="header-logo">
        </div>
    </div>

    <!-- Login Form -->
    <div class="form-container">
        <h2>Login to PinterPal</h2>

        <!-- Toon foutmelding -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="text" name="emailadress" placeholder="Email Address / Username" required>
            
            <div class="password-container">
                <input type="password" name="password" placeholder="Password" required>
                <span class="password-toggle" onclick="togglePassword(this)">👁️</span>
            </div>
            
            <button type="submit">Log In</button>
        </form>
        <a href="register.php">Don't have an account? Sign up here</a>
    </div>

    <script>
        function togglePassword(element) {
            const passwordInput = element.previousElementSibling;
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                element.textContent = "🙈"; // Change emoji to indicate "hide password"
            } else {
                passwordInput.type = "password";
                element.textContent = "👁️"; // Change emoji back to indicate "show password"
            }
        }
    </script>
</body>
</html>
