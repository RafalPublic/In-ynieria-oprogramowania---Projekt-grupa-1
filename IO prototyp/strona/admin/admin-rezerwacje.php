<?php
// admin-rezerwacje.php – panel administracyjny z filtrowaniem/sortowaniem i CRUD rezerwacji

// 1) Połączenie z DB
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// 2) Odbiór GET: filtr "search" i "sort"
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort   = isset($_GET['sort'])   ? $_GET['sort']   : 'new';

// 3) Obsługa POST: add/delete/prepare update/save update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sel = $_POST['reservations'] ?? [];

    // ADD
    if (($_POST['action'] ?? '') === 'add') {
        $uid = intval($_POST['new_user']);
        $pid = intval($_POST['new_room']);
        $df  = $conn->real_escape_string($_POST['new_date_from']);
        $dt  = $conn->real_escape_string($_POST['new_date_to']);

        if (!$uid || !$pid || !$df || !$dt) {
            $message = "Wypełnij wszystkie pola.";
        } elseif ($df > $dt) {
            $message = "Data od musi być wcześniejsza lub równa dacie do.";
        } else {
            $q = "INSERT INTO rezerwacja(id_user,id_pokoj,data_od,data_do)
                  VALUES($uid,$pid,'$df','$dt')";
            if ($conn->query($q)) {
                $message = "Dodano rezerwację.";
            } else {
                $message = "Błąd: " . $conn->error;
            }
        }
    }

    // DELETE
    if (($_POST['action'] ?? '') === 'delete') {
        if ($sel) {
            $ids = implode(',', array_map('intval', $sel));
            if ($conn->query("DELETE FROM rezerwacja WHERE id_rezerwacja IN($ids)")) {
                $message = "Usunięto rezerwację(ie) ID: $ids";
            } else {
                $message = "Błąd: " . $conn->error;
            }
        } else {
            $message = "Nic nie zaznaczono.";
        }
    }

    // PREPARE UPDATE
    if (($_POST['action'] ?? '') === 'update') {
        if (count($sel) > 1) {
            $message = "Zaznacz tylko jedną rezerwację do edycji.";
        } elseif (count($sel) === 1) {
            header(
                "Location: admin-rezerwacje.php?edit=" . intval($sel[0]) .
                "&search=" . urlencode($search) .
                "&sort="   . urlencode($sort)
            );
            exit;
        } else {
            $message = "Nie zaznaczono rezerwacji.";
        }
    }

    // SAVE UPDATE
    if (isset($_POST['save_update'])) {
        $id  = intval($_POST['id_rezerwacja']);
        $uid = intval($_POST['user']);
        $pid = intval($_POST['room']);
        $df  = $conn->real_escape_string($_POST['date_from']);
        $dt  = $conn->real_escape_string($_POST['date_to']);

        if (!$uid || !$pid || !$df || !$dt) {
            $message = "Wypełnij wszystkie pola.";
        } elseif ($df > $dt) {
            $message = "Data od musi być wcześniejsza lub równa dacie do.";
        } else {
            $q = "UPDATE rezerwacja
                  SET id_user=$uid,
                      id_pokoj=$pid,
                      data_od='$df',
                      data_do='$dt'
                  WHERE id_rezerwacja=$id";
            if ($conn->query($q)) {
                $message = "Zaktualizowano rezerwację #$id";
            } else {
                $message = "Błąd: " . $conn->error;
            }
            // przekierowanie, by zachować GETy i uniknąć ponownego wysłania POST
            header(
                "Location: admin-rezerwacje.php" .
                "?search=" . urlencode($search) .
                "&sort="   . urlencode($sort)
            );
            exit;
        }
    }
}

// 4) Tryb edycji?
$editMode = false;
$editRes  = [];
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $r = $conn->query("
        SELECT r.*, u.imie, u.nazwisko, p.numer
        FROM rezerwacja r
        JOIN user u   ON r.id_user = u.id_user
        JOIN pokoj p  ON r.id_pokoj = p.id_pokoj
        WHERE r.id_rezerwacja = $eid
        LIMIT 1
    ");
    if ($r && $r->num_rows) {
        $editMode = true;
        $editRes  = $r->fetch_assoc();
    } else {
        $message = "Nie znaleziono rezerwacji #$eid.";
    }
}

// 5) Budujemy WHERE
$where = '';
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where = "WHERE u.imie LIKE '%$s%'
              OR u.nazwisko LIKE '%$s%'
              OR p.numer LIKE '%$s%'";
}

// 6) Budujemy ORDER BY
switch ($sort) {
    case 'date_from':
        $order = "ORDER BY r.data_od ASC";
        break;
    case 'date_to':
        $order = "ORDER BY r.data_do ASC";
        break;
    default: // 'new'
        $order = "ORDER BY r.id_rezerwacja DESC";
        break;
}

// 7) Pobieramy listy dropdownów i listę rezerwacji
$usersList = $conn->query("SELECT id_user, imie, nazwisko FROM user ORDER BY nazwisko");
$roomsList = $conn->query("SELECT id_pokoj, numer      FROM pokoj ORDER BY numer");
$sql = "
    SELECT r.id_rezerwacja,
           u.imie, u.nazwisko,
           p.numer    AS pokoj,
           r.data_od, r.data_do
    FROM rezerwacja r
    JOIN user u   ON r.id_user = u.id_user
    JOIN pokoj p  ON r.id_pokoj = p.id_pokoj
    $where
    $order
";
$result = $conn->query($sql);

// helper status
function computeStatus($from, $to) {
    $t = date('Y-m-d');
    if ($to < $t)            return 'odbyta';
    if ($from <= $t && $t <= $to) return 'w trakcie';
    return 'w przyszłości';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin – Rezerwacje</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="top-bar">
  <div class="logo">Hotel Atlantica</div>
  <a href="../main/main.html" class="logout-link">
    <button class="logout">Wyloguj się</button>
  </a>
</header>

<div class="main-content">
  <aside class="sidebar">
    <a href="admin-lista-pokoii.php"><button>Lista pokoi</button></a>
    <a href="users.php"><button>Użytkownicy</button></a>
    <a href="admin-rezerwacje.php"><button class="active">Rezerwacje</button></a>
    <a href="platnosci.php"><button>Płatności</button></a>
    <a href="raporty.php"><button>Raporty</button></a>
    <a href="system.php"><button>System</button></a>
  </aside>

  <div class="content-area">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- FILTR + SORT (GET) -->
    <form method="get" class="filter-bar">
      <input type="text"
             name="search"
             class="search-box"
             placeholder="Szukaj imię/nazwisko/pokój…"
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" name="sort" value="new"
              class="filter-btn <?= $sort==='new'?'active':'' ?>">
        Nowe
      </button>
      <button type="submit" name="sort" value="date_from"
              class="filter-btn <?= $sort==='date_from'?'active':'' ?>">
        Data od
      </button>
      <button type="submit" name="sort" value="date_to"
              class="filter-btn <?= $sort==='date_to'?'active':'' ?>">
        Data do
      </button>
      <?php if ($editMode): ?>
        <input type="hidden" name="edit" value="<?= intval($editRes['id_rezerwacja']) ?>">
      <?php endif; ?>
    </form>

    <!-- TRYB EDYCJI -->
    <?php if ($editMode): ?>
      <div class="edit-form">
        <h2>Edytuj rezerwację #<?= intval($editRes['id_rezerwacja']) ?></h2>
        <form method="post"
              action="admin-rezerwacje.php?search=<?= urlencode($search) ?>
                                     &sort=<?= urlencode($sort) ?>
                                     &edit=<?= intval($editRes['id_rezerwacja']) ?>">
          <input type="hidden" name="id_rezerwacja" value="<?= intval($editRes['id_rezerwacja']) ?>">

          <label>Użytkownik</label>
          <select name="user" required>
            <option value="">— wybierz —</option>
            <?php while($u = $usersList->fetch_assoc()): ?>
              <option value="<?= $u['id_user'] ?>"
                <?= $u['id_user']==$editRes['id_user']?'selected':'' ?>>
                <?= htmlspecialchars($u['imie'].' '.$u['nazwisko']) ?>
              </option>
            <?php endwhile; ?>
          </select>
          <?php $usersList->data_seek(0); ?>

          <label>Pokój</label>
          <select name="room" required>
            <option value="">— wybierz —</option>
            <?php while($p = $roomsList->fetch_assoc()): ?>
              <option value="<?= $p['id_pokoj'] ?>"
                <?= $p['id_pokoj']==$editRes['id_pokoj']?'selected':'' ?>>
                <?= htmlspecialchars($p['numer']) ?>
              </option>
            <?php endwhile; ?>
          </select>
          <?php $roomsList->data_seek(0); ?>

          <label>Data od</label>
          <input type="date" name="date_from" required
                 value="<?= htmlspecialchars($editRes['data_od']) ?>">
          <label>Data do</label>
          <input type="date" name="date_to" required
                 value="<?= htmlspecialchars($editRes['data_do']) ?>">

          <button name="save_update" type="submit">Zapisz</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- CRUD + TABELA (POST) -->
    <form id="reservationsForm" method="post"
          action="admin-rezerwacje.php?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
      <div class="action-buttons">
        <button type="button" id="openAddModal" class="action-btn add">Dodaj</button>
        <button type="submit" name="action" value="delete" class="action-btn delete">Usuń</button>
        <button type="submit" name="action" value="update" class="action-btn update">Aktualizuj</button>
      </div>

      <div class="table-container">
        <table class="rooms-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>ID</th><th>Imię</th><th>Nazwisko</th><th>Pokój</th>
              <th>Data od</th><th>Data do</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows): ?>
              <?php while ($r = $result->fetch_assoc()): ?>
                <?php $st = computeStatus($r['data_od'], $r['data_do']); ?>
                <tr>
                  <td><input type="checkbox" name="reservations[]"
                             value="<?= $r['id_rezerwacja'] ?>"></td>
                  <td><?= $r['id_rezerwacja'] ?></td>
                  <td><?= htmlspecialchars($r['imie']) ?></td>
                  <td><?= htmlspecialchars($r['nazwisko']) ?></td>
                  <td><?= htmlspecialchars($r['pokoj']) ?></td>
                  <td><?= $r['data_od'] ?></td>
                  <td><?= $r['data_do'] ?></td>
                  <td><?= $st ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" style="text-align:center">Brak rezerwacji</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Dodaj rezerwację -->
<div class="modal-overlay" id="addModalOverlay">
  <div class="modal">
    <button class="close-btn" id="closeAddModal">&times;</button>
    <h2>Dodaj rezerwację</h2>
    <form method="post"
          action="admin-rezerwacje.php?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
      <input type="hidden" name="action" value="add">

      <label>Użytkownik</label>
      <select name="new_user" required>
        <option value="">— wybierz —</option>
        <?php while($u = $usersList->fetch_assoc()): ?>
          <option value="<?= $u['id_user'] ?>">
            <?= htmlspecialchars($u['imie'].' '.$u['nazwisko']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <?php $usersList->data_seek(0); ?>

      <label>Pokój</label>
      <select name="new_room" required>
        <option value="">— wybierz —</option>
        <?php while($p = $roomsList->fetch_assoc()): ?>
          <option value="<?= $p['id_pokoj'] ?>">
            <?= htmlspecialchars($p['numer']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <?php $roomsList->data_seek(0); ?>

      <label>Data od</label>
      <input type="date" name="new_date_from" required>
      <label>Data do</label>
      <input type="date" name="new_date_to"   required>

      <div class="btn-group">
        <button type="submit" class="save-btn">Dodaj</button>
        <button type="button" class="cancel-btn" id="cancelAddModal">Anuluj</button>
      </div>
    </form>
  </div>
</div>

<script>
// zaznacz/odznacz wszystkie
document.getElementById('selectAll')
  .addEventListener('change', e => {
    document.querySelectorAll('input[name="reservations[]"]')
      .forEach(cb => cb.checked = e.target.checked);
  });

// modal
const overlay = document.getElementById('addModalOverlay');
document.getElementById('openAddModal').onclick   = () => overlay.style.display = 'flex';
document.getElementById('closeAddModal').onclick  = () => overlay.style.display = 'none';
document.getElementById('cancelAddModal').onclick = () => overlay.style.display = 'none';
overlay.onclick = e => { if (e.target === overlay) overlay.style.display = 'none'; };
</script>
</body>
</html>
