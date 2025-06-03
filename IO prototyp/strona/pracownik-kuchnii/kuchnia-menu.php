<?php
$host = 'localhost';
$db = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// Aktualizacja dostępności
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $id = intval($_POST['toggle_id']);
    $newStatus = intval($_POST['new_status']);
    $conn->query("UPDATE menu SET dostepnosc = $newStatus WHERE id_danie = $id");
    exit; // Zakończ żądanie Ajax
}

// Pobieranie dań
$query = "SELECT * FROM menu ORDER BY id_danie DESC";
$result = $conn->query($query);
$dishes = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Atlantica - Menu</title>
  <link rel="stylesheet" href="styles_kuchnia.css" />
  <script>
    function toggleAvailability(id, checkbox) {
      const newStatus = checkbox.checked ? 1 : 0;
      fetch("kuchnia-menu.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "toggle_id=" + id + "&new_status=" + newStatus
      });
    }
  </script>
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
    <a href="kuchnia-zamowienia.php"><button class="menu-button">Zamówienia</button></a>
    <button class="menu-button active">Menu</button>
  </aside>

  <section class="content">
    <div class="filters">
      <div class="tags">
        <span class="tag">Kurczak ✕</span>
        <span class="tag">Frytki ✕</span>
        <span class="tag">Fit ✕</span>
      </div>
      <label class="price-label">Cena</label>
      <input type="range" min="10" max="100" />
      <div class="filter-group">
        <p>Rodzaj dania</p>
        <label><input type="checkbox" checked /> Śniadania</label>
        <label><input type="checkbox" checked /> Obiad!</label>
        <label><input type="checkbox" checked /> Kolacja</label>
      </div>
      <div class="filter-group">
        <p>Wielkość</p>
        <label><input type="checkbox" checked /> Małe</label>
        <label><input type="checkbox" checked /> Średnie</label>
        <label><input type="checkbox" checked /> Grubas edition</label>
      </div>
    </div>

    <div class="menu-panel">
      <div class="menu-bar">
        <input type="text" placeholder="Szukaj" class="search-input" />
        <button class="search-button">🔍</button>
        <button class="filter-button active">Nowe</button>
        <button class="filter-button">Cena rosnąco</button>
        <button class="filter-button">Cena malejąco</button>
        <button class="filter-button">Oceny</button>
      </div>

      <div class="dishes">
        <?php foreach ($dishes as $dish): ?>
          <div class="dish">
            <img src="<?= strtolower(str_replace(' ', '_', $dish['nazwa'])) ?>.jpg" alt="<?= htmlspecialchars($dish['nazwa']) ?>" />
            <p><?= htmlspecialchars($dish['nazwa']) ?></p>
            <strong><?= number_format($dish['cena'], 2) ?>zł</strong>
            <label class="switch">
              <input type="checkbox"
                     <?= $dish['dostepnosc'] ? 'checked' : '' ?>
                     onchange="toggleAvailability(<?= $dish['id_danie'] ?>, this)">
              <span class="slider <?= $dish['dostepnosc'] ? 'green' : 'red' ?>"></span>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
</body>
</html>
