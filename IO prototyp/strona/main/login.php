<?php
session_start();

// Połączenie z bazą danych
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = ''; // lub Twoje hasło
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // W prawdziwym systemie hasło powinno być haszowane!

    $query = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Tu możesz sprawdzać hasło, np. password_verify($password, $user['haslo'])

        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['rola'] = $user['rola'];

        switch ($user['rola']) {
            case 'gosc':
                header("Location: gosc/pokoje.html"); // np. gosc/pokoje.php
                break;
            case 'pracownik_kuchnii':
                header("Location: pracownik-kucnii/kuchnia-zamowienia.html"); // np. kuchnia/dashboard.php
                break;
            case 'pracownik_recepcji':
                header("Location: pracownik-recepcji/rezerwacje.html");
                break;
            case 'pracownik_sprzatajacy':
                header("Location: sprzatanie/sprzatanie.html");
                break;
            case 'admin':
                header("Location: admin/system.html");
                break;
            default:
                echo "Nieznana rola.";
        }
        exit;
    } else {
        $error = "Nieprawidłowy email lub hasło.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Logowanie</title>
  <link rel="stylesheet" href="styles_login.css">
</head>
<body>
  <div class="container">
    <main class="main">
      <div class="login-box">
        <?php if (!empty($error)): ?>
          <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="input" required>
          </div>
          <div class="form-group">
            <label for="password">Hasło</label>
            <input type="password" id="password" name="password" class="input" required>
          </div>
          <div class="button-group">
            <button type="submit" class="btn-primary">Zaloguj</button>
          </div>
        </form>
        <div class="forgot-password">
          <a href="reset-hasla.html">Zapomniałeś hasła?</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
