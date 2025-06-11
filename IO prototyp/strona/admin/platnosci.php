<?php
// platnosci.php – wyświetla dane z tabeli „platnosci” w formie tabeli

// Połączenie z bazą danych
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// Pobieramy płatności wraz z imieniem i nazwiskiem użytkownika
$sql = "
  SELECT 
    p.id_platnosc,
    u.imie,
    u.nazwisko,
    p.id_rezerwacja,
    p.id_zamowienie,
    p.kwota,
    p.data_platnosci,
    p.status,
    p.metoda_platnosci
  FROM platnosci p
  JOIN user u ON p.id_user = u.id_user
  ORDER BY p.data_platnosci DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Atlantica – Płatności</title>
  <link rel="stylesheet" href="styles_platnosci.css">
</head>
<body>
  <header class="top-bar">
    <div class="logo">
      <span class="name">Hotel Atlantica</span>
      <link rel="stylesheet" href="styles.css" />
    </div>
    <div class="icons">
      <span class="notif-icon">🔔<span class="notif-dot"></span></span>
      <form method="post" action="logout.php" style="display:inline;">
        <a href="../main/main.html" class="logout-link">
        <button class="logout">Wyloguj się</button>
        </a>
      </form>
    </div>
  </header>

  <div class="main-container">
    <nav class="sidebar">
        <a href="admin-lista-pokoii.php"><button>Lista pokoi</button></a>
        <a href="users.php"><button >Użytkownicy</button></a>
        <a href="admin-rezerwacje.php"><button >Rezerwacje</button></a>
        <a href="platnosci.php"><button class="active">Płatności</button></a>
        <a href="raporty.php"><button>Raporty</button></a>
        <a href="system.php"><button>System</button></a>
    </nav>

    <div class="content-wrapper">
      <!-- Pasek filtrów / wyszukiwania -->
      <div class="filter-bar">
        <input type="text" class="search-input" placeholder="Szukaj..." />
        <button class="search-button">🔍</button>
        <button class="filter-button active">Nowe</button>
        <button class="filter-button">Kwota (malejąco)</button>
        <button class="filter-button">Płatnik (alfabetycznie)</button>
        <button class="menu-toggle">☰</button>
      </div>

      <!-- Obszar treści z szarym tłem, w którym znajduje się nagłówek i tabela -->
      <div class="content-area">
        <!-- Nagłówek z tytułem i przyciskiem -->
        <div class="content-header">
          <h2>Lista płatności</h2>
          <a href="wyciag.php">
            <button class="generate-button">Generuj wyciąg</button>
          </a>
        </div>

        <!-- Tabela płatności -->
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Użytkownik</th>
              <th>ID Rezerwacji</th>
              <th>ID Zamówienia</th>
              <th>Kwota (PLN)</th>
              <th>Data płatności</th>
              <th>Status</th>
              <th>Metoda płatności</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id_platnosc'] ?></td>
                  <td><?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) ?></td>
                  <td><?= htmlspecialchars($row['id_rezerwacja'] ?? '–') ?></td>
                  <td><?= htmlspecialchars($row['id_zamowienie'] ?? '–') ?></td>
                  <td><?= number_format($row['kwota'], 2) ?> zł</td>
                  <td><?= $row['data_platnosci'] ?></td>
                  <td><?= htmlspecialchars($row['status']) ?></td>
                  <td><?= htmlspecialchars($row['metoda_platnosci']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center;">Brak danych o płatnościach.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
