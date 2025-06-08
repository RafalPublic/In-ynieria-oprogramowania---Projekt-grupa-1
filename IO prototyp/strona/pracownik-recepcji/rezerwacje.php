<?php
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$sql = "
SELECT 
  r.id_rezerwacja,
  p.numer AS pokoj,
  u.imie,
  u.nazwisko,
  r.data_od,
  r.data_do
FROM rezerwacja r
JOIN pokoj p ON r.id_pokoj = p.id_pokoj
JOIN user u ON r.id_user = u.id_user
ORDER BY r.data_od ASC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rezerwacje</title>
  <link rel="stylesheet" href="styles_recepcja.css" />
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
      background-color: #f2f2f2;
    }
  </style>
</head>
<body>
<div class="header">
  <h1>Hotel Atlantica</h1>
  <a href="../main/main.html"><button class="logout-btn">Wyloguj się</button></a>
</div>

<div class="main">
  <div class="sidebar">
    <a href="lista-pokoii.php"><button>Lista pokoi</button></a>
    <a href="lista-gosci.php"><button>Lista gości</button></a>
    <a href="rezerwacje.php"><button class="active">Rezerwacje</button></a>
  </div>

  <div class="content">
    <div class="filter-bar">
      <input type="text" placeholder="Szukaj" />
      <button class="filter-btn active">Nowe</button>
      <div class="sort-chip">Numer pokoju (rosnąco)</div>
      <div class="sort-chip">Data od najstarszej (rosnąco)</div>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Pokój</th>
          <th>Gość</th>
          <th>Data od</th>
          <th>Data do</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id_rezerwacja'] ?></td>
              <td><?= htmlspecialchars($row['pokoj']) ?></td>
              <td><?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) ?></td>
              <td><?= $row['data_od'] ?></td>
              <td><?= $row['data_do'] ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">Brak rezerwacji do wyświetlenia.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
