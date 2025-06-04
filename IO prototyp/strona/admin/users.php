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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = isset($_POST['users']) ? $_POST['users'] : [];

    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $fname  = $conn->real_escape_string($_POST['new_imie']);
        $lname  = $conn->real_escape_string($_POST['new_nazwisko']);
        $role   = $conn->real_escape_string($_POST['new_rola']);
        $email  = $conn->real_escape_string($_POST['new_email']);
        $phone  = $conn->real_escape_string($_POST['new_telefon']);
        if ($fname === '' || $lname === '' || $role === '' || $email === '' || $phone === '') {
            $message = "Wszystkie pola muszą być wypełnione.";
        } else {
            $insSql = "INSERT INTO user (imie, nazwisko, rola, email, telefon) 
                       VALUES ('$fname', '$lname', '$role', '$email', '$phone')";
            if ($conn->query($insSql) === TRUE) {
                $message = "Dodano nowego użytkownika: $fname $lname";
            } else {
                $message = "Błąd przy dodawaniu: " . $conn->error;
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!empty($selected)) {
            $ids = array_map('intval', $selected);
            $idList = implode(',', $ids);
            $delSql = "DELETE FROM user WHERE id_user IN ($idList)";
            if ($conn->query($delSql) === TRUE) {
                $message = "Usunięto użytkownika/ów o ID: $idList";
            } else {
                $message = "Błąd przy usuwaniu: " . $conn->error;
            }
        } else {
            $message = "Nie zaznaczono żadnego użytkownika do usunięcia.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (count($selected) > 1) {
            $message = "Można jeden jednocześnie aktualizować.";
        } elseif (count($selected) === 1) {
            $editId = intval($selected[0]);
            header("Location: users.php?edit=$editId");
            exit;
        } else {
            $message = "Nie zaznaczono użytkownika do aktualizacji.";
        }
    }

    if (isset($_POST['save_update'])) {
        $id      = intval($_POST['id_user']);
        $fname   = $conn->real_escape_string($_POST['imie']);
        $lname   = $conn->real_escape_string($_POST['nazwisko']);
        $role    = $conn->real_escape_string($_POST['rola']);
        $email   = $conn->real_escape_string($_POST['email']);
        $phone   = $conn->real_escape_string($_POST['telefon']);

        $updSql = "UPDATE user 
                   SET imie = '$fname', nazwisko = '$lname', rola = '$role', 
                       email = '$email', telefon = '$phone' 
                   WHERE id_user = $id";
        if ($conn->query($updSql) === TRUE) {
            $message = "Pomyślnie zaktualizowano użytkownika o ID: $id";
        } else {
            $message = "Błąd przy aktualizacji: " . $conn->error;
        }
        header("Location: users.php");
        exit;
    }
}

$editMode = false;
$editUser = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editId = intval($_GET['edit']);
    $eSql = "SELECT id_user, imie, nazwisko, rola, email, telefon 
             FROM user WHERE id_user = $editId LIMIT 1";
    $eRes = $conn->query($eSql);
    if ($eRes && $eRes->num_rows === 1) {
        $editUser = $eRes->fetch_assoc();
    } else {
        $message = "Nie znaleziono użytkownika o ID: $editId";
        $editMode = false;
    }
}

$sql = "SELECT id_user, imie, nazwisko, rola, email, telefon FROM user ORDER BY nazwisko ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel administracyjny – Użytkownicy</title>
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
      <a href="users.php"><button class="sidebar-btn active">Użytkownicy</button></a>
      <a href="admin-rezerwacje.php"><button class="sidebar-btn">Rezerwacje</button></a>
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

      <?php if ($editMode && $editUser): ?>
        <div class="edit-form">
          <h2>Aktualizuj użytkownika ID: <?= $editUser['id_user'] ?></h2>
          <form method="post" action="users.php">
            <input type="hidden" name="id_user" value="<?= $editUser['id_user'] ?>">

            <label for="imie">Imię</label>
            <input type="text" id="imie" name="imie" required 
                   value="<?= htmlspecialchars($editUser['imie']) ?>">

            <label for="nazwisko">Nazwisko</label>
            <input type="text" id="nazwisko" name="nazwisko" required 
                   value="<?= htmlspecialchars($editUser['nazwisko']) ?>">

            <label for="rola">Rola</label>
            <select id="rola" name="rola" required>
              <option value="gosc"               <?= $editUser['rola']==='gosc'               ? 'selected' : '' ?>>gosc</option>
              <option value="pracownik_kuchnii"  <?= $editUser['rola']==='pracownik_kuchnii'  ? 'selected' : '' ?>>pracownik_kuchnii</option>
              <option value="pracownik_recepcji" <?= $editUser['rola']==='pracownik_recepcji' ? 'selected' : '' ?>>pracownik_recepcji</option>
              <option value="pracownik_sprzatajacy" <?= $editUser['rola']==='pracownik_sprzatajacy' ? 'selected' : '' ?>>pracownik_sprzatajacy</option>
              <option value="admin"              <?= $editUser['rola']==='admin'              ? 'selected' : '' ?>>admin</option>
            </select>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($editUser['email']) ?>">

            <label for="telefon">Telefon</label>
            <input type="tel" id="telefon" name="telefon" required 
                   value="<?= htmlspecialchars($editUser['telefon']) ?>">

            <button type="submit" name="save_update">Zapisz zmiany</button>
          </form>
        </div>
      <?php endif; ?>


      <div class="filter-bar">
        <input type="text" placeholder="Szukaj" class="search-box" />
        <button class="filter-btn active">Nowi</button>
        <button class="filter-btn">Nazwisko (alfabetycznie)</button>
        <button class="filter-btn">Email (rosnąco)</button>
      </div>


      <form method="post" action="users.php" id="usersForm">
        <div class="table-container">
          <table class="rooms-table">
            <thead>
              <tr>
                <th class="checkbox-cell"><input type="checkbox" id="selectAll" /></th>
                <th>ID</th>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Rola</th>
                <th>Email</th>
                <th>Telefon</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="checkbox-cell">
                      <input type="checkbox" name="users[]" value="<?= $row['id_user'] ?>" />
                    </td>
                    <td><?= htmlspecialchars($row['id_user']) ?></td>
                    <td><?= htmlspecialchars($row['imie']) ?></td>
                    <td><?= htmlspecialchars($row['nazwisko']) ?></td>
                    <td><?= htmlspecialchars($row['rola']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['telefon']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align:center;">
                    Brak danych o użytkownikach.
                  </td>
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
      <h2>Dodaj nowego użytkownika</h2>
      <form method="post" action="users.php">
        <input type="hidden" name="action" value="add">

        <label for="new_imie">Imię</label>
        <input type="text" id="new_imie" name="new_imie" required>

        <label for="new_nazwisko">Nazwisko</label>
        <input type="text" id="new_nazwisko" name="new_nazwisko" required>

        <label for="new_rola">Rola</label>
        <select id="new_rola" name="new_rola" required>
          <option value="gosc">gosc</option>
          <option value="pracownik_kuchnii">pracownik_kuchnii</option>
          <option value="pracownik_recepcji">pracownik_recepcji</option>
          <option value="pracownik_sprzatajacy">pracownik_sprzatajacy</option>
          <option value="admin">admin</option>
        </select>

        <label for="new_email">Email</label>
        <input type="email" id="new_email" name="new_email" required>

        <label for="new_telefon">Telefon</label>
        <input type="tel" id="new_telefon" name="new_telefon" required>

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
      document.querySelectorAll('input[name="users[]"]').forEach(function(cb) {
        cb.checked = checked;
      });
    });


    var actionForm = document.getElementById('actionForm');
    var usersForm  = document.getElementById('usersForm');
    var deleteBtn  = actionForm.querySelector('button[value="delete"]');
    var updateBtn  = actionForm.querySelector('button[value="update"]');

    deleteBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'action';
      inp.value = 'delete';
      usersForm.appendChild(inp);
      usersForm.submit();
    });
    updateBtn.addEventListener('click', function(e) {
      e.preventDefault();
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'action';
      inp.value = 'update';
      usersForm.appendChild(inp);
      usersForm.submit();
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
