<?php
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// 1) Pobierz nagłówki zamówień
$sqlHdr = "
SELECT 
  h.id_order,
  u.imie, u.nazwisko,
  p.numer       AS pokoj,
  h.data_zamowienia,
  h.status
FROM zamowienie_header h
JOIN rezerwacja        r ON h.id_rezerwacja = r.id_rezerwacja
JOIN pokoj             p ON r.id_pokoj      = p.id_pokoj
JOIN user              u ON r.id_user       = u.id_user
ORDER BY h.data_zamowienia DESC
";
$hdrs = $conn->query($sqlHdr);

// 2) Przygotuj zapytanie pozycji
$stmtItems = $conn->prepare("
  SELECT m.nazwa, i.quantity
  FROM zamowienie_item i
  JOIN menu m ON i.id_danie = m.id_danie
  WHERE i.id_order = ?
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Atlantica - Zamówienia</title>
  <link rel="stylesheet" href="styles_kuchnia.css" />
  <style>
    /* filtrowanie input */
    #search {
      width: 100%;
      padding: 8px;
      margin-bottom: 12px;
      border: 1px solid #444;
      border-radius: 4px;
      background: #2c2c2c;
      color: #fff;
    }
    details {
      background: #2c2c2c;
      margin-bottom: 10px;
      border-radius: 4px;
      padding: 8px;
      color: #fff;
    }
    summary {
      font-weight: bold;
      cursor: pointer;
      outline: none;
    }
    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    table.items th, table.items td {
      border: 1px solid #444;
      padding: 6px;
      background: #3a3a3a;
    }
    table.items th {
      background: #444;
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
    <a href="../main/main.html"><button class="logout-button">Wyloguj się</button></a>
  </div>
</header>

<main class="main">
  <aside class="sidebar">
    <button class="menu-button active">Zamówienia</button>
    <a href="kuchnia-menu.php"><button class="menu-button">Menu</button></a>
  </aside>

  <section class="content">
    <h2 class="section-title">Zamówienia z restauracji</h2>
    <!-- Pole do filtrowania -->
    <input type="text" id="search" placeholder="Szukaj zamówienia...">

    <?php if ($hdrs && $hdrs->num_rows): ?>
      <?php while ($hdr = $hdrs->fetch_assoc()): ?>
        <details class="order-block">
          <summary>
            Zamówienie #<?= $hdr['id_order'] ?>
            – <?= htmlspecialchars($hdr['imie'].' '.$hdr['nazwisko']) ?>
            (pokój <?= htmlspecialchars($hdr['pokoj']) ?>)
            | <?= $hdr['data_zamowienia'] ?>
            | status: <?= htmlspecialchars($hdr['status']) ?>
          </summary>
          
          <table class="items">
            <thead>
              <tr>
                <th>Danie</th>
                <th>Ilość</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $stmtItems->bind_param("i", $hdr['id_order']);
                $stmtItems->execute();
                $resItems = $stmtItems->get_result();
                while ($item = $resItems->fetch_assoc()):
              ?>
                <tr>
                  <td><?= htmlspecialchars($item['nazwa']) ?></td>
                  <td><?= (int)$item['quantity'] ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </details>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Brak zamówień.</p>
    <?php endif; ?>
  </section>
</main>

<script>
// JavaScript do filtrowania listy <details>
document.getElementById('search').addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.order-block').forEach(block => {
    const summary = block.querySelector('summary').textContent.toLowerCase();
    block.style.display = summary.includes(term) ? '' : 'none';
  });
});
</script>
</body>
</html>
