<?php

$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}


$message = '';

function computeStatus($dateFrom, $dateTo) {
    $today = date('Y-m-d');
    if ($dateTo < $today) {
        return 'odbyta';
    }
    if ($dateFrom <= $today && $today <= $dateTo) {
        return 'w trakcie';
    }
    if ($dateFrom > $today) {
        return 'w przyszlosci';
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = isset($_POST['reservations']) ? $_POST['reservations'] : [];


    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $userId   = intval($_POST['new_user']);
        $roomId   = intval($_POST['new_room']);
        $dateFrom = $conn->real_escape_string($_POST['new_date_from']);
        $dateTo   = $conn->real_escape_string($_POST['new_date_to']);
        if (!$userId || !$roomId || $dateFrom === '' || $dateTo === '') {
            $message = "Wszystkie pola muszą być wypełnione.";
        } else {
            $insSql = "INSERT INTO rezerwacja (id_user, id_pokoj, data_od, data_do)
                       VALUES ($userId, $roomId, '$dateFrom', '$dateTo')";
            if ($conn->query($insSql) === TRUE) {
                $message = "Dodano nową rezerwację.";
            } else {
                $message = "Błąd przy dodawaniu: " . $conn->error;
            }
        }
    }


    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!empty($selected)) {
            $ids = array_map('intval', $selected);
            $idList = implode(',', $ids);
            $delSql = "DELETE FROM rezerwacja WHERE id_rezerwacja IN ($idList)";
            if ($conn->query($delSql) === TRUE) {
                $message = "Usunięto rezerwację/rez rezerwacje o ID: $idList";
            } else {
                $message = "Błąd przy usuwaniu: " . $conn->error;
            }
        } else {
            $message = "Nie zaznaczono żadnej rezerwacji do usunięcia.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (count($selected) > 1) {
            $message = "Można jeden jednocześnie aktualizować.";
        } elseif (count($selected) === 1) {
            $editId = intval($selected[0]);
            header("Location: admin-rezerwacje.php?edit=$editId");
            exit;
        } else {
            $message = "Nie zaznaczono rezerwacji do aktualizacji.";
        }
    }


    if (isset($_POST['save_update'])) {
        $id        = intval($_POST['id_rezerwacja']);
        $userId    = intval($_POST['user']);
        $roomId    = intval($_POST['room']);
        $dateFrom  = $conn->real_escape_string($_POST['date_from']);
        $dateTo    = $conn->real_escape_string($_POST['date_to']);
        $status    = $conn->real_escape_string($_POST['status']);


        if ($status === 'anulowana') {
            $updSql = "UPDATE rezerwacja 
                       SET id_user = $userId, id_pokoj = $roomId, data_od = '$dateFrom', data_do = '$dateTo'
                       WHERE id_rezerwacja = $id";
        } else {

            $updSql = "UPDATE rezerwacja 
                       SET id_user = $userId, id_pokoj = $roomId, data_od = '$dateFrom', data_do = '$dateTo' 
                       WHERE id_rezerwacja = $id";
        }
        if ($conn->query($updSql) === TRUE) {
            $message = "Pomyślnie zaktualizowano rezerwację o ID: $id";
        } else {
            $message = "Błąd przy aktualizacji: " . $conn->error;
        }
        header("Location: admin-rezerwacje.php");
        exit;
    }
}


$editMode = false;
$editRes  = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editId   = intval($_GET['edit']);
    $eSql = "
      SELECT r.id_rezerwacja, r.id_user, r.id_pokoj, r.data_od, r.data_do,
             u.imie, u.nazwisko, p.numer
      FROM rezerwacja r
      JOIN user u ON r.id_user = u.id_user
      JOIN pokoj p ON r.id_pokoj = p.id_pokoj
      WHERE r.id_rezerwacja = $editId
      LIMIT 1
    ";
    $eRes = $conn->query($eSql);
    if ($eRes && $eRes->num_rows === 1) {
        $editRes = $eRes->fetch_assoc();
    } else {
        $message = "Nie znaleziono rezerwacji o ID: $editId";
        $editMode = false;
    }
}


$usersList = $conn->query("SELECT id_user, imie, nazwisko FROM user ORDER BY nazwisko ASC");


$roomsList = $conn->query("SELECT id_pokoj, numer FROM pokoj ORDER BY numer ASC");


$sql = "
  SELECT 
    r.id_rezerwacja,
    u.imie,
    u.nazwisko,
    p.numer AS numer_pokoju,
    r.data_od,
    r.data_do
  FROM rezerwacja r
  JOIN user u ON r.id_user = u.id_user
  JOIN pokoj p ON r.id_pokoj = p.id_pokoj
  ORDER BY r.data_od DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel administracyjny – Rezerwacje</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <header class="top-bar">
    <div class="logo"><span class="icon">🏨</span>Hotel Atlantica</div>
    <div class="notifications">
      <span class="notif-icon">🔔<span class="notif-dot"></span></span>
    </div>
    <button class="logout">Wyloguj się</button>
  </header>

  <div class="main-container">

    <aside class="sidebar">
      <a href="admin-lista-pokoii.php"><button class="sidebar-btn">Lista pokoi</button></a>
      <a href="users.php"><button class="sidebar-btn">Użytkownicy</button></a>
      <a href="admin-rezerwacje.php"><button class="sidebar-btn active">Rezerwacje</button></a>
      <a href="platnosci.php"><button class="sidebar-btn">Płatności</button></a>
      <a href="raporty.php"><button class="sidebar-btn">Raporty</button></a>
      <a href="system.php"><button class="sidebar-btn">System</button></a>

      <div class="action-buttons">
        <button class="action-btn add" id="openAddModal">Dodaj</button>
        <form method="post" id="actionForm">
          <button type="submit" name="action" value="delete" class="action-btn delete">Usuń</button>
          <button type="submit" name="action" value="update" class="action-btn update">Aktualizuj</button>
        </form>
      </div>
    </aside>


    <div class="content-area">
      <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>


      <?php if ($editMode && $editRes): ?>
        <?php
          $computedStatus = computeStatus($editRes['data_od'], $editRes['data_do']);
        ?>
        <div class="edit-form">
          <h2>Aktualizuj rezerwację ID: <?= $editRes['id_rezerwacja'] ?></h2>
          <form method="post" action="admin-rezerwacje.php">
            <input type="hidden" name="id_rezerwacja" value="<?= $editRes['id_rezerwacja'] ?>">

            <label for="user">Użytkownik</label>
            <select id="user" name="user" required>
              <option value="">— Wybierz —</option>
              <?php while ($u = $usersList->fetch_assoc()): ?>
                <?php $uid = $u['id_user']; $uName = htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']); ?>
                <option value="<?= $uid ?>"
                  <?= ($editRes['id_user'] == $uid) ? 'selected' : '' ?>>
                  <?= $uName ?>
                </option>
              <?php endwhile; ?>
            </select>
            <?php $usersList->data_seek(0); ?>

            <label for="room">Pokój</label>
            <select id="room" name="room" required>
              <option value="">— Wybierz —</option>
              <?php while ($r = $roomsList->fetch_assoc()): ?>
                <?php $rid = $r['id_pokoj']; $rNum = htmlspecialchars($r['numer']); ?>
                <option value="<?= $rid ?>"
                  <?= ($editRes['id_pokoj'] == $rid) ? 'selected' : '' ?>>
                  <?= $rNum ?>
                </option>
              <?php endwhile; ?>
            </select>
            <?php $roomsList->data_seek(0); ?>

            <label for="date_from">Data od</label>
            <input type="date" id="date_from" name="date_from" required
                   value="<?= htmlspecialchars($editRes['data_od']) ?>">

            <label for="date_to">Data do</label>
            <input type="date" id="date_to" name="date_to" required
                   value="<?= htmlspecialchars($editRes['data_do']) ?>">

            <label for="status">Status</label>
            <select id="status" name="status" required>
              <option value="odbyta" <?= ($computedStatus === 'odbyta') ? 'selected' : '' ?>>odbyta</option>
              <option value="w trakcie" <?= ($computedStatus === 'w trakcie') ? 'selected' : '' ?>>w trakcie</option>
              <option value="w przyszlosci" <?= ($computedStatus === 'w przyszlosci') ? 'selected' : '' ?>>w przyszlosci</option>
              <option value="anulowana">anulowana</option>
            </select>

            <button type="submit" name="save_update">Zapisz zmiany</button>
          </form>
        </div>
      <?php endif; ?>


      <div class="filter-bar">
        <input type="text" placeholder="Szukaj" class="search-box" />
        <button class="filter-btn active">Wszystkie</button>
        <button class="filter-btn">Data od (rosnąco)</button>
        <button class="filter-btn">Data do (rosnąco)</button>
      </div>


      <form method="post" action="admin-rezerwacje.php" id="reservationsForm">
        <div class="table-container">
          <table class="rooms-table">
            <thead>
              <tr>
                <th class="checkbox-cell"><input type="checkbox" id="selectAll" /></th>
                <th>ID</th>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Numer pokoju</th>
                <th>Data od</th>
                <th>Data do</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <?php
                    $status = computeStatus($row['data_od'], $row['data_do']);
                  ?>
                  <tr>
                    <td class="checkbox-cell">
                      <input type="checkbox" name="reservations[]" value="<?= $row['id_rezerwacja'] ?>" />
                    </td>
                    <td><?= htmlspecialchars($row['id_rezerwacja']) ?></td>
                    <td><?= htmlspecialchars($row['imie']) ?></td>
                    <td><?= htmlspecialchars($row['nazwisko']) ?></td>
                    <td><?= htmlspecialchars($row['numer_pokoju']) ?></td>
                    <td><?= htmlspecialchars($row['data_od']) ?></td>
                    <td><?= htmlspecialchars($row['data_do']) ?></td>
                    <td><?= htmlspecialchars($status) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align:center;">Brak danych o rezerwacjach.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>


  <div class="modal-overlay" id="addModalOverlay">
    <div class="modal">
      <button class="close-btn" id="closeAddModal">&times;</button>
      <h2>Dodaj nową rezerwację</h2>
      <form method="post" action="admin-rezerwacje.php">
        <input type="hidden" name="action" value="add">

        <label for="new_user">Użytkownik</label>
        <select id="new_user" name="new_user" required>
          <option value="">— Wybierz —</option>
          <?php while ($u = $usersList->fetch_assoc()): ?>
            <?php $uid = $u['id_user']; $uName = htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']); ?>
            <option value="<?= $uid ?>"><?= $uName ?></option>
          <?php endwhile; ?>
        </select>
        <?php $usersList->data_seek(0); ?>

        <label for="new_room">Pokój</label>
        <select id="new_room" name="new_room" required>
          <option value="">— Wybierz —</option>
          <?php while ($r = $roomsList->fetch_assoc()): ?>
            <?php $rid = $r['id_pokoj']; $rNum = htmlspecialchars($r['numer']); ?>
            <option value="<?= $rid ?>"><?= $rNum ?></option>
          <?php endwhile; ?>
        </select>
        <?php $roomsList->data_seek(0); ?>

        <label for="new_date_from">Data od</label>
        <input type="date" id="new_date_from" name="new_date_from" required>

        <label for="new_date_to">Data do</label>
        <input type="date" id="new_date_to" name="new_date_to" required>

        <div class="btn-group">
          <button type="submit" class="save-btn">Dodaj</button>
          <button type="button" class="cancel-btn" id="cancelAddModal">Anuluj</button>
        </div>
      </form>
    </div>
  </div>

  <script>

    document.getElementById('selectAll').addEventListener('change', function() {
      var checked = this.checked;
      document.querySelectorAll('input[name="reservations[]"]').forEach(function(cb) {
        cb.checked = checked;
      });
    });


    var actionForm      = document.getElementById('actionForm');
    var reservationsForm = document.getElementById('reservationsForm');
    var deleteBtn       = actionForm.querySelector('button[value="delete"]');
    var updateBtn       = actionForm.querySelector('button[value="update"]');

    deleteBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type  = 'hidden';
      inp.name  = 'action';
      inp.value = 'delete';
      reservationsForm.appendChild(inp);
      reservationsForm.submit();
    });
    updateBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type  = 'hidden';
      inp.name  = 'action';
      inp.value = 'update';
      reservationsForm.appendChild(inp);
      reservationsForm.submit();
    });


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
    addModalOverlay.addEventListener('click', function(e) {
      if (e.target === addModalOverlay) {
        addModalOverlay.style.display = 'none';
      }
    });
  </script>
</body>
</html>
