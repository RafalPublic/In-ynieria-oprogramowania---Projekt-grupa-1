<?php
// Połączenie z bazą danych
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = ''; // Twoje hasło jeśli masz
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $rola = 'gosc'; // domyślnie każdy nowy użytkownik to gość

    // Sprawdź, czy użytkownik już istnieje
    $check = $conn->query("SELECT id_user FROM user WHERE email = '$email'");
    if ($check && $check->num_rows > 0) {
        $error = "Użytkownik o tym adresie e-mail już istnieje.";
    } else {
        // Wstaw nowego użytkownika
        $conn->query("INSERT INTO user (imie, nazwisko, rola, email, haslo, telefon) 
        VALUES ('$fname', '$lname', '$rola', '$email', '$password', '$phone')");
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hotel Atlantica - Rejestracja</title>
<link rel="stylesheet" href="styles_rejestracja.css">
</head>
<body>
<header>
<div class="logo">🅱</div>
<h1>Hotel Atlantica</h1>
</header>
 
<main>
<div class="form-container">
<?php if (!empty($error)): ?>
  <p style="color: red;"><?= $error ?></p>
<?php endif; ?>
<form method="post" action="rejestracja.php">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" placeholder="Email" required>

  <label for="password">Hasło</label>
  <input type="password" id="password" name="password" placeholder="Hasło" required>

  <label for="fname">Imię</label>
  <input type="text" id="fname" name="fname" placeholder="Imię" required>

  <label for="lname">Nazwisko</label>
  <input type="text" id="lname" name="lname" placeholder="Nazwisko" required>

  <label for="phone">Nr. telefonu</label>
  <input type="tel" id="phone" name="phone" placeholder="Tel +48" required>

  <button type="submit">Zarejestruj się</button>
</form>
</div>
</main>
 
<footer>
<div class="footer-left">
<div class="logo">🅱</div>
<div class="socials">
<a href="#">X</a>
<a href="#">IG</a>
<a href="#">YT</a>
<a href="#">IN</a>
</div>
</div>
<div class="footer-columns">
  <!-- kolumny -->
</div>
</footer>
</body>
</html>