<?php
// admin-lista-pokoii.php – panel administracyjny z listą pokoi, checkboxami, usuń/aktualizuj/dodaj

// Połączenie z bazą danych
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// Zmienna na komunikaty błędów/powiadomień
$message = '';

// Obsługa formularza: Usuwanie lub przygotowanie do aktualizacji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tablica zaznaczonych ID pokoi
    $selected = isset($_POST['rooms']) ? $_POST['rooms'] : [];

    // Dodawanie pokoju
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $number = $conn->real_escape_string($_POST['new_numer']);
        $type   = $conn->real_escape_string($_POST['new_typ']);
        $status = $conn->real_escape_string($_POST['new_status']);
        if ($number === '' || $type === '' || $status === '') {
            $message = "Wszystkie pola muszą być wypełnione.";
        } else {
            $insSql = "INSERT INTO pokoj (numer, typ, status) VALUES ('$number', '$type', '$status')";
            if ($conn->query($insSql) === TRUE) {
                $message = "Dodano nowy pokój: $number";
            } else {
                $message = "Błąd przy dodawaniu: " . $conn->error;
            }
        }
    }

    // Usuwanie
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!empty($selected)) {
            $ids = array_map('intval', $selected);
            $idList = implode(',', $ids);
            $delSql = "DELETE FROM pokoj WHERE id_pokoj IN ($idList)";
            if ($conn->query($delSql) === TRUE) {
                $message = "Usunięto pokój/pokoje o ID: $idList";
            } else {
                $message = "Błąd przy usuwaniu pokoi: " . $conn->error;
            }
        } else {
            $message = "Nie zaznaczono żadnego pokoju do usunięcia.";
        }
    }

    // Przygotowanie do aktualizacji
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (count($selected) > 1) {
            $message = "Można jeden jednocześnie aktualizować.";
        } elseif (count($selected) === 1) {
            $editId = intval($selected[0]);
            header("Location: admin-lista-pokoii.php?edit=$editId");
            exit;
        } else {
            $message = "Nie zaznaczono pokoju do aktualizacji.";
        }
    }

    // Zapis aktualizacji
    if (isset($_POST['save_update'])) {
        $id      = intval($_POST['id_pokoj']);
        $number  = $conn->real_escape_string($_POST['numer']);
        $type    = $conn->real_escape_string($_POST['typ']);
        $status  = $conn->real_escape_string($_POST['status']);

        $updSql = "UPDATE pokoj 
                   SET numer = '$number', typ = '$type', status = '$status' 
                   WHERE id_pokoj = $id";
        if ($conn->query($updSql) === TRUE) {
            $message = "Pomyślnie zaktualizowano pokój o ID: $id";
        } else {
            $message = "Błąd przy aktualizacji: " . $conn->error;
        }
        header("Location: admin-lista-pokoii.php");
        exit;
    }
}

// Sprawdzenie trybu edycji
$editMode = false;
$editRoom = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editId = intval($_GET['edit']);
    $eSql = "SELECT id_pokoj, numer, typ, status FROM pokoj WHERE id_pokoj = $editId LIMIT 1";
    $eRes = $conn->query($eSql);
    if ($eRes && $eRes->num_rows === 1) {
        $editRoom = $eRes->fetch_assoc();
    } else {
        $message = "Nie znaleziono pokoju o ID: $editId";
        $editMode = false;
    }
}

// Pobranie wszystkich pokoi
$sql = "SELECT id_pokoj, numer, typ, status FROM pokoj ORDER BY numer ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel administracyjny – Hotel Atlantica</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <!-- Nagłówek -->
  <header class="top-bar">
    <div class="logo">Hotel Atlantica</div>
    <div class="center-icon">🔔<span class="dot"></span></div>
    <button class="logout">Wyloguj się</button>
  </header>

  <div class="main-content">
    <!-- Sidebar -->
    <aside class="sidebar">
      <a href="admin-lista-pokoii.php"><button class="sidebar-btn active">Lista pokoi</button></a>
      <a href="users.php"><button class="sidebar-btn">Użytkownicy</button></a>
      <a href="admin-rezerwacje.php"><button class="sidebar-btn">Rezerwacje</button></a>
      <a href="platnosci.php"><button class="sidebar-btn">Płatności</button></a>
      <a href="raporty.php"><button class="sidebar-btn">Raporty</button></a>
      <a href="system.php"><button class="sidebar-btn">System</button></a>

      <div class="action-buttons">
        <!-- Przyciski Usuń i Aktualizuj -->
        <form method="post" id="actionForm">
          <button type="submit" name="action" value="delete" class="action-btn delete">Usuń</button>
          <button type="submit" name="action" value="update" class="action-btn update">Aktualizuj</button>
        </form>
        <!-- Przycisk Dodaj wyświetla modal -->
        <button class="action-btn add" id="openAddModal">Dodaj</button>
      </div>
    </aside>

    <!-- Główna zawartość -->
    <div class="content-area">
      <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <!-- Tryb edycji pokoju -->
      <?php if ($editMode && $editRoom): ?>
        <div class="edit-form">
          <h2>Aktualizuj pokój ID: <?= $editRoom['id_pokoj'] ?></h2>
          <form method="post" action="admin-lista-pokoii.php">
            <input type="hidden" name="id_pokoj" value="<?= $editRoom['id_pokoj'] ?>">
            <label for="numer">Numer pokoju</label>
            <input type="text" id="numer" name="numer" required
                   value="<?= htmlspecialchars($editRoom['numer']) ?>">

            <label for="typ">Typ</label>
            <input type="text" id="typ" name="typ" required
                   value="<?= htmlspecialchars($editRoom['typ']) ?>">

            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="wolny"   <?= $editRoom['status'] === 'wolny'   ? 'selected' : '' ?>>wolny</option>
              <option value="zajety"  <?= $editRoom['status'] === 'zajety'  ? 'selected' : '' ?>>zajęty</option>
              <option value="serwis"  <?= $editRoom['status'] === 'serwis'  ? 'selected' : '' ?>>serwis</option>
            </select>

            <button type="submit" name="save_update">Zapisz zmiany</button>
          </form>
        </div>
      <?php endif; ?>

      <!-- Pasek filtrów -->
      <div class="filter-bar">
        <input type="text" placeholder="Szukaj" class="search-box" />
        <button class="filter-btn active">Nowe</button>
        <button class="filter-btn">Numer pokoju (rosnąco)</button>
        <button class="filter-btn">Maks. ilość gości (rosnąco)</button>
      </div>

      <!-- Tabela pokoi wraz z checkboxami -->
      <form method="post" action="admin-lista-pokoii.php" id="roomsForm">
        <div class="table-container">
          <table class="rooms-table">
            <thead>
              <tr>
                <th class="checkbox-cell"><input type="checkbox" id="selectAll" /></th>
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
                    <td class="checkbox-cell">
                      <input type="checkbox" name="rooms[]" value="<?= $row['id_pokoj'] ?>" />
                    </td>
                    <td><?= htmlspecialchars($row['id_pokoj']) ?></td>
                    <td><?= htmlspecialchars($row['numer']) ?></td>
                    <td><?= htmlspecialchars($row['typ']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align:center;">Brak danych o pokojach.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Dodaj pokój -->
  <div class="modal-overlay" id="addModalOverlay">
    <div class="modal">
      <button class="close-btn" id="closeAddModal">&times;</button>
      <h2>Dodaj nowy pokój</h2>
      <form method="post" action="admin-lista-pokoii.php">
        <input type="hidden" name="action" value="add">
        <label for="new_numer">Numer pokoju</label>
        <input type="text" id="new_numer" name="new_numer" required>

        <label for="new_typ">Typ</label>
        <input type="text" id="new_typ" name="new_typ" required>

        <label for="new_status">Status</label>
        <select id="new_status" name="new_status" required>
          <option value="wolny">wolny</option>
          <option value="zajety">zajęty</option>
          <option value="serwis">serwis</option>
        </select>

        <div class="btn-group">
          <button type="submit" class="save-btn">Dodaj</button>
          <button type="button" class="cancel-btn" id="cancelAddModal">Anuluj</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Select All / Deselect All
    document.getElementById('selectAll').addEventListener('change', function() {
      var checked = this.checked;
      document.querySelectorAll('input[name="rooms[]"]').forEach(function(checkbox) {
        checkbox.checked = checked;
      });
    });

    // Obsługa przycisków Usuń/Aktualizuj
    var actionForm = document.getElementById('actionForm');
    var roomsForm  = document.getElementById('roomsForm');
    var deleteBtn  = actionForm.querySelector('button[value="delete"]');
    var updateBtn  = actionForm.querySelector('button[value="update"]');

    deleteBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'action';
      inp.value = 'delete';
      roomsForm.appendChild(inp);
      roomsForm.submit();
    });
    updateBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'action';
      inp.value = 'update';
      roomsForm.appendChild(inp);
      roomsForm.submit();
    });

    // Modal Dodaj
    var addModalOverlay = document.getElementById('addModalOverlay');
    var openAddModal    = document.getElementById('openAddModal');
    var closeAddModal   = document.getElementById('closeAddModal');
    var cancelAddModal  = document.getElementById('cancelAddModal');

    openAddModal.addEventListener('click', function() {
      addModalOverlay.style.display = 'flex';
    });
    closeAddModal.addEventListener('click', function() {
      addModalOverlay.style.display = 'none';
    });
    cancelAddModal.addEventListener('click', function() {
      addModalOverlay.style.display = 'none';
    });
    // Kliknięcie poza modalem też zamknie
    addModalOverlay.addEventListener('click', function(e) {
      if (e.target === addModalOverlay) {
        addModalOverlay.style.display = 'none';
      }
    });
  </script>
</body>
</html>

