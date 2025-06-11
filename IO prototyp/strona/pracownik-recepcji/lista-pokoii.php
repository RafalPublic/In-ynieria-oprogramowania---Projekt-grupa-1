<?php
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM pokoj ORDER BY numer ASC");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Lista pokoi</title>
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
    <button class="active">Lista pokoi</button>
    <a href="lista-gosci.php"><button>Lista gości</button></a>
    <a href="rezerwacje.php"><button>Rezerwacje</button></a>
  </div>

  <div class="content">
    <div class="filter-bar">
      <input type="text" placeholder="Szukaj" />
      <button class="filter-btn active">Nowe</button>
      <div class="sort-chip">Numer (rosnąco)</div>
      <div class="sort-chip">Status</div>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Numer pokoju</th>
          <th>Typ</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id_pokoj'] ?></td>
              <td><?= htmlspecialchars($row['numer']) ?></td>
              <td><?= htmlspecialchars($row['typ']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4">Brak danych o pokojach.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
