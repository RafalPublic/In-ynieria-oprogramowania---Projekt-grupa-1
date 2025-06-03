<?php
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// Pobierz szczegóły zamówień
$sql = "
SELECT 
  z.id_zamowienie,
  m.nazwa AS danie,
  p.numer AS pokoj,
  u.imie,
  u.nazwisko,
  z.data_zamowienia,
  z.status
FROM zamowienie z
JOIN menu m ON z.id_danie = m.id_danie
JOIN rezerwacja r ON z.id_rezerwacji = r.id_rezerwacja
JOIN pokoj p ON r.id_pokoj = p.id_pokoj
JOIN user u ON r.id_user = u.id_user
ORDER BY z.data_zamowienia DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Atlantica - Zamówienia</title>
  <link rel="stylesheet" href="styles_kuchnia.css" />
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 12px;
      text-align: left;
    }
    th {
      background-color: #333;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #2c2c2c;
    }
  </style>
</head>
<body>
<header class="header">
  <div class="logo">
    <span class="icon">🏨</span>
    <span class="title">Hotel Atlantica</span>
  </div>
  <div class="header-actions">
    <span class="notification-dot"></span>
    <a href="../main/main.html"><button class="logout-button">Wyloguj się</button></a>
  </div>
</header>

<main class="main">
  <aside class="sidebar">
    <button class="menu-button active">Zamówienia</button>
    <a href="kuchnia-menu.php"><button class="menu-button">Menu</button></a>
  </aside>

  <section class="content">
    <div class="filters">
      <input type="text" placeholder="Szukaj" class="search-input" />
      <button class="search-button">🔍</button>
      <button class="filter-button active">Nowe</button>
      <button class="filter-button">Data rozpoczęcia (najstarsze)</button>
      <button class="filter-button">Numer pokoju (malejąco)</button>
      <button class="menu-toggle">☰</button>
    </div>

    <div class="orders">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Gość</th>
            <th>Pokój</th>
            <th>Danie</th>
            <th>Data zamówienia</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['id_zamowienie'] ?></td>
                <td><?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) ?></td>
                <td><?= htmlspecialchars($row['pokoj']) ?></td>
                <td><?= htmlspecialchars($row['danie']) ?></td>
                <td><?= $row['data_zamowienia'] ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">Brak zamówień.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
</html>