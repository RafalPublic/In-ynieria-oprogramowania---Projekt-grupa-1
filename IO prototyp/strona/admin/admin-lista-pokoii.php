<?php
// admin-lista-pokoii.php – panel administracyjny z filtrowaniem/sortowaniem i CRUD

// 1) Połączenie z bazą
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// 2) GET: odbiór parametrów filtrowania i sortowania
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort   = isset($_GET['sort'])   ? $_GET['sort']   : 'new';

// 3) POST: CRUD
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['rooms'] ?? [];
    // DODAJ
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $num  = $conn->real_escape_string($_POST['new_numer']);
        $typ  = $conn->real_escape_string($_POST['new_typ']);
        $stat = $conn->real_escape_string($_POST['new_status']);
        if ($num === '' || $typ === '' || $stat === '') {
            $message = "Wszystkie pola muszą być wypełnione.";
        } else {
            $ins = "INSERT INTO pokoj(numer,typ,status) VALUES('$num','$typ','$stat')";
            if ($conn->query($ins)) {
                $message = "Dodano pokój $num";
            } else {
                $message = "Błąd przy dodawaniu: " . $conn->error;
            }
        }
    }
    // USUŃ
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!empty($selected)) {
            $ids = implode(',', array_map('intval', $selected));
            if ($conn->query("DELETE FROM pokoj WHERE id_pokoj IN($ids)")) {
                $message = "Usunięto pokoje ID: $ids";
            } else {
                $message = "Błąd przy usuwaniu: " . $conn->error;
            }
        } else {
            $message = "Nie zaznaczono żadnego pokoju.";
        }
    }
    // PRZYGOTUJ AKTUALIZACJĘ
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (count($selected) > 1) {
            $message = "Zaznacz tylko jeden pokój do aktualizacji.";
        } elseif (count($selected) === 1) {
            $eid = intval($selected[0]);
            header("Location: admin-lista-pokoii.php?edit=$eid&search=" . urlencode($search) . "&sort=" . urlencode($sort));
            exit;
        } else {
            $message = "Nie zaznaczono pokoju.";
        }
    }
    // ZAPIS AKTUALIZACJI
    if (isset($_POST['save_update'])) {
        $id   = intval($_POST['id_pokoj']);
        $num  = $conn->real_escape_string($_POST['numer']);
        $typ  = $conn->real_escape_string($_POST['typ']);
        $stat = $conn->real_escape_string($_POST['status']);
        $upd  = "UPDATE pokoj SET numer='$num', typ='$typ', status='$stat' WHERE id_pokoj=$id";
        if ($conn->query($upd)) {
            header("Location: admin-lista-pokoii.php?search=" . urlencode($search) . "&sort=" . urlencode($sort));
            exit;
        } else {
            $message = "Błąd przy aktualizacji: " . $conn->error;
        }
    }
}

// 4) Tryb edycji?
$editMode = false;
$editRoom = [];
if (isset($_GET['edit'])) {
    $editMode = true;
    $eid = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM pokoj WHERE id_pokoj=$eid LIMIT 1");
    if ($res && $res->num_rows) {
        $editRoom = $res->fetch_assoc();
    } else {
        $message = "Nie znaleziono pokoju ID $eid";
        $editMode = false;
    }
}

// 5) Budujemy WHERE dla filtrowania
$where = '';
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where = "WHERE numer LIKE '%$s%' OR typ LIKE '%$s%'";
}

// 6) Budujemy ORDER BY dla sortowania
switch ($sort) {
    case 'numer':
        $orderBy = 'ORDER BY CAST(numer AS UNSIGNED) ASC';
        break;
    case 'gosci':
        // wymaga kolumny maks_ilosc_gosci
        $orderBy = 'ORDER BY maks_ilosc_gosci ASC';
        break;
    default:
        $orderBy = 'ORDER BY id_pokoj ASC';
        break;
}

// 7) Pobieramy pokoje
$sql    = "SELECT id_pokoj, numer, typ, status FROM pokoj $where $orderBy";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin – Lista pokoi</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="top-bar">
  <div class="logo">Hotel Atlantica</div>
  <div class="center-icon">🔔<span class="dot"></span></div>
  <a href="../main/main.html"><button class="logout">Wyloguj się</button></a>
</header>

<div class="main-content">
  <aside class="sidebar">
    <a href="admin-lista-pokoii.php"><button class="sidebar-btn active">Lista pokoi</button></a>
    <a href="users.php"><button class="sidebar-btn">Użytkownicy</button></a>
    <a href="admin-rezerwacje.php"><button class="sidebar-btn">Rezerwacje</button></a>
    <a href="platnosci.php"><button class="sidebar-btn">Płatności</button></a>
    <a href="raporty.php"><button class="sidebar-btn">Raporty</button></a>
    <a href="system.php"><button class="sidebar-btn">System</button></a>
  </aside>

  <div class="content-area">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- FILTR i SORT (GET) -->
    <form method="get" class="filter-bar">
      <input type="text" name="search" class="search-box" placeholder="Szukaj numer/typ…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" name="sort" value="new" class="filter-btn <?= $sort === 'new' ? 'active' : '' ?>">Nowe</button>
      <button type="submit" name="sort" value="numer" class="filter-btn <?= $sort === 'numer' ? 'active' : '' ?>">Numer pokoju</button>
      <button type="submit" name="sort" value="gosci" class="filter-btn <?= $sort === 'gosci' ? 'active' : '' ?>">Maks. ilość gości</button>
      <?php if ($editMode): ?>
        <input type="hidden" name="edit" value="<?= intval($editRoom['id_pokoj']) ?>">
      <?php endif; ?>
    </form>

    <!-- TRYB EDYCJI -->
    <?php if ($editMode): ?>
      <div class="edit-form">
        <h2>Edytuj pokój ID <?= $editRoom['id_pokoj'] ?></h2>
        <form method="post" action="admin-lista-pokoii.php">
          <input type="hidden" name="id_pokoj" value="<?= $editRoom['id_pokoj'] ?>">
          <label>Numer</label>
          <input type="text" name="numer" required value="<?= htmlspecialchars($editRoom['numer']) ?>">
          <label>Typ</label>
          <input type="text" name="typ" required value="<?= htmlspecialchars($editRoom['typ']) ?>">
          <label>Status</label>
          <select name="status" required>
            <option value="wolny" <?= $editRoom['status'] === 'wolny' ? 'selected' : '' ?>>wolny</option>
            <option value="zajety" <?= $editRoom['status'] === 'zajety' ? 'selected' : '' ?>>zajęty</option>
            <option value="serwis" <?= $editRoom['status'] === 'serwis' ? 'selected' : '' ?>>serwis</option>
          </select>
          <button type="submit" name="save_update">Zapisz</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- CRUD + Tabela pokoi -->
    <form method="post" id="roomsForm">
      <!-- zachowujemy GET jako hidden przy POST -->
      <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
      <?php if ($editMode): ?>
        <input type="hidden" name="edit" value="<?= intval($editRoom['id_pokoj']) ?>">
      <?php endif; ?>

      <div class="action-buttons">
        <button type="button" class="action-btn add" id="openAddModal">Dodaj</button>
        <button type="submit" name="action" value="delete" class="action-btn delete">Usuń</button>
        <button type="submit" name="action" value="update" class="action-btn update">Aktualizuj</button>
      </div>

      <div class="table-container">
        <table class="rooms-table">
          <thead>
            <tr>
              <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
              <th>ID</th><th>Numer pokoju</th><th>Typ</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows): ?>
              <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                  <td class="checkbox-cell">
                    <input type="checkbox" name="rooms[]" value="<?= $r['id_pokoj'] ?>">
                  </td>
                  <td><?= $r['id_pokoj'] ?></td>
                  <td><?= htmlspecialchars($r['numer']) ?></td>
                  <td><?= htmlspecialchars($r['typ']) ?></td>
                  <td><?= htmlspecialchars($r['status']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" style="text-align:center">Brak pokoi</td></tr>
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
    <h2>Dodaj pokój</h2>
    <form method="post" action="admin-lista-pokoii.php">
      <input type="hidden" name="action" value="add">
      <label>Numer</label>
      <input type="text" name="new_numer" required>
      <label>Typ</label>
      <input type="text" name="new_typ" required>
      <label>Status</label>
      <select name="new_status" required>
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
// Select / deselect all
document.getElementById('selectAll').addEventListener('change', e => {
  document.querySelectorAll('input[name="rooms[]"]').forEach(cb => {
    cb.checked = e.target.checked;
  });
});
// Modal logic
const overlay = document.getElementById('addModalOverlay');
document.getElementById('openAddModal').onclick  = () => overlay.style.display = 'flex';
document.getElementById('closeAddModal').onclick = () => overlay.style.display = 'none';
document.getElementById('cancelAddModal').onclick= () => overlay.style.display = 'none';
overlay.onclick = e => { if (e.target === overlay) overlay.style.display = 'none'; };
</script>
</body>
</html>