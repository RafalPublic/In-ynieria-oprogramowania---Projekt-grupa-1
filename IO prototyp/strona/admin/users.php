<?php
// users.php – panel administracyjny z listą użytkowników, filtrowanie/sortowanie i CRUD

// 1) Połączenie z bazą
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

// 2) GET: odbiór parametrów filtrów i sortowania
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort   = isset($_GET['sort'])   ? $_GET['sort']   : 'new';

// 3) POST: zapis edycji (PRIORYTET)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $id      = intval($_POST['id_user']);
    $fname   = $conn->real_escape_string($_POST['imie']);
    $lname   = $conn->real_escape_string($_POST['nazwisko']);
    $role    = $conn->real_escape_string($_POST['rola']);
    $email   = $conn->real_escape_string($_POST['email']);
    $phone   = $conn->real_escape_string($_POST['telefon']);

    $upd = "UPDATE user SET 
              imie='$fname',
              nazwisko='$lname',
              rola='$role',
              email='$email',
              telefon='$phone'
            WHERE id_user=$id";
    if ($conn->query($upd)) {
        header("Location: users.php?search=".urlencode($search)."&sort=".urlencode($sort));
        exit;
    } else {
        $message = "Błąd przy aktualizacji: " . $conn->error;
    }
}

// 4) POST: pozostałe akcje (add/delete/prepare update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_update'])) {
    $selected = $_POST['users'] ?? [];

    // DODAJ
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $fname = $conn->real_escape_string($_POST['new_imie']);
        $lname = $conn->real_escape_string($_POST['new_nazwisko']);
        $role  = $conn->real_escape_string($_POST['new_rola']);
        $email = $conn->real_escape_string($_POST['new_email']);
        $phone = $conn->real_escape_string($_POST['new_telefon']);
        if ($fname === '' || $lname === '' || $role === '' || $email === '' || $phone === '') {
            $message = "Wszystkie pola muszą być wypełnione.";
        } else {
            $ins = "INSERT INTO user(imie,nazwisko,rola,email,telefon)
                    VALUES('$fname','$lname','$role','$email','$phone')";
            if ($conn->query($ins)) {
                $message = "Dodano: $fname $lname";
            } else {
                $message = "Błąd przy dodawaniu: " . $conn->error;
            }
        }
    }

    // USUŃ
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if ($selected) {
            $ids = implode(',', array_map('intval', $selected));
            if ($conn->query("DELETE FROM user WHERE id_user IN($ids)")) {
                $message = "Usunięto użytkownika/ów ID: $ids";
            } else {
                $message = "Błąd przy usuwaniu: " . $conn->error;
            }
        } else {
            $message = "Nie zaznaczono użytkownika.";
        }
    }

    // PRZYGOTUJ AKTUALIZACJĘ
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (count($selected) > 1) {
            $message = "Zaznacz jeden użytkownik do edycji.";
        } elseif (count($selected) === 1) {
            $eid = intval($selected[0]);
            header("Location: users.php?edit=$eid&search=".urlencode($search)."&sort=".urlencode($sort));
            exit;
        } else {
            $message = "Nie zaznaczono użytkownika.";
        }
    }
}

// 5) TRYB EDYCJI?
$editMode = false;
$editUser = [];
if (isset($_GET['edit'])) {
    $editMode = true;
    $eid = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM user WHERE id_user=$eid LIMIT 1");
    if ($res && $res->num_rows) {
        $editUser = $res->fetch_assoc();
    } else {
        $message = "Nie znaleziono użytkownika ID $eid";
        $editMode = false;
    }
}

// 6) Filtr WHERE
$where = '';
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where = "WHERE imie LIKE '%$s%' OR nazwisko LIKE '%$s%' OR email LIKE '%$s%'";
}

// 7) Sort ORDER BY
switch ($sort) {
    case 'nazwisko':
        $orderBy = 'ORDER BY nazwisko ASC';
        break;
    case 'email':
        $orderBy = 'ORDER BY email ASC';
        break;
    default:
        $orderBy = 'ORDER BY id_user ASC';
        break;
}

// 8) Pobierz listę
$sql    = "SELECT id_user,imie,nazwisko,rola,email,telefon
           FROM user $where $orderBy";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin – Użytkownicy</title>
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
    <a href="admin-lista-pokoii.php"><button type="button" class="sidebar-btn">Lista pokoi</button></a>
    <a href="users.php"><button type="button" class="sidebar-btn active">Użytkownicy</button></a>
    <a href="admin-rezerwacje.php"><button type="button" class="sidebar-btn">Rezerwacje</button></a>
    <a href="platnosci.php"><button type="button" class="sidebar-btn">Płatności</button></a>
    <a href="raporty.php"><button type="button" class="sidebar-btn">Raporty</button></a>
    <a href="system.php"><button type="button" class="sidebar-btn">System</button></a>
  </aside>

  <div class="content-area">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- 1) FORMULARZ GET: filtr i sortowanie -->
    <form method="get" class="filter-bar">
      <input type="text"
             name="search"
             class="search-box"
             placeholder="Szukaj imię/nazwisko/email…"
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" name="sort" value="new"
              class="filter-btn <?= $sort === 'new' ? 'active' : '' ?>">
        Nowi
      </button>
      <button type="submit" name="sort" value="nazwisko"
              class="filter-btn <?= $sort === 'nazwisko' ? 'active' : '' ?>">
        Nazwisko
      </button>
      <button type="submit" name="sort" value="email"
              class="filter-btn <?= $sort === 'email' ? 'active' : '' ?>">
        Email
      </button>
    </form>

    <!-- 2) TRYB EDYCJI -->
    <?php if ($editMode): ?>
      <div class="edit-form">
        <h2>Edytuj użytkownika ID <?= $editUser['id_user'] ?></h2>
        <form method="post" action="users.php">
          <input type="hidden" name="id_user" value="<?= $editUser['id_user'] ?>">
          <label>Imię</label>
          <input type="text" name="imie" required
                 value="<?= htmlspecialchars($editUser['imie']) ?>">
          <label>Nazwisko</label>
          <input type="text" name="nazwisko" required
                 value="<?= htmlspecialchars($editUser['nazwisko']) ?>">
          <label>Rola</label>
          <select name="rola" required>
            <option value="gosc" <?= $editUser['rola'] === 'gosc' ? 'selected' : '' ?>>gosc</option>
            <option value="pracownik_kuchnii" <?= $editUser['rola'] === 'pracownik_kuchnii' ? 'selected' : '' ?>>pracownik_kuchnii</option>
            <option value="pracownik_recepcji" <?= $editUser['rola'] === 'pracownik_recepcji' ? 'selected' : '' ?>>pracownik_recepcji</option>
            <option value="pracownik_sprzatajacy" <?= $editUser['rola'] === 'pracownik_sprzatajacy' ? 'selected' : '' ?>>pracownik_sprzatajacy</option>
            <option value="admin" <?= $editUser['rola'] === 'admin' ? 'selected' : '' ?>>admin</option>
          </select>
          <label>Email</label>
          <input type="email" name="email" required
                 value="<?= htmlspecialchars($editUser['email']) ?>">
          <label>Telefon</label>
          <input type="tel" name="telefon" required
                 value="<?= htmlspecialchars($editUser['telefon']) ?>">
          <button type="submit" name="save_update">Zapisz zmiany</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- 3) FORMULARZ POST: CRUD + tabela -->
    <form method="post" id="usersForm">
      <div class="action-buttons">
        <button type="button" class="action-btn add" id="openAddModal">Dodaj</button>
        <button type="submit" name="action" value="delete" class="action-btn delete">Usuń</button>
        <button type="submit" name="action" value="update" class="action-btn update">Aktualizuj</button>
      </div>

      <div class="table-container">
        <table class="rooms-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>ID</th><th>Imię</th><th>Nazwisko</th>
              <th>Rola</th><th>Email</th><th>Telefon</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows): ?>
              <?php while ($u = $result->fetch_assoc()): ?>
                <tr>
                  <td><input type="checkbox" name="users[]" value="<?= $u['id_user'] ?>"></td>
                  <td><?= $u['id_user'] ?></td>
                  <td><?= htmlspecialchars($u['imie']) ?></td>
                  <td><?= htmlspecialchars($u['nazwisko']) ?></td>
                  <td><?= htmlspecialchars($u['rola']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['telefon']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" style="text-align:center">Brak użytkowników</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </form>

  </div>
</div>

<!-- MODAL Dodaj użytkownika -->
<div class="modal-overlay" id="addModalOverlay">
  <div class="modal">
    <button class="close-btn" id="closeAddModal">&times;</button>
    <h2>Dodaj użytkownika</h2>
    <form method="post" action="users.php">
      <input type="hidden" name="action" value="add">
      <label>Imię</label>
      <input type="text" name="new_imie" required>
      <label>Nazwisko</label>
      <input type="text" name="new_nazwisko" required>
      <label>Rola</label>
      <select name="new_rola" required>
        <option value="gosc">gosc</option>
        <option value="pracownik_kuchnii">pracownik_kuchnii</option>
        <option value="pracownik_recepcji">pracownik_recepcji</option>
        <option value="pracownik_sprzatajacy">pracownik_sprzatajacy</option>
        <option value="admin">admin</option>
      </select>
      <label>Email</label>
      <input type="email" name="new_email" required>
      <label>Telefon</label>
      <input type="tel" name="new_telefon" required>
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
  document.querySelectorAll('input[name="users[]"]').forEach(cb => {
    cb.checked = e.target.checked;
  });
});
// Modal logic
const overlay = document.getElementById('addModalOverlay');
document.getElementById('openAddModal').onclick  = () => overlay.style.display = 'flex';
document.getElementById('closeAddModal').onclick = () => overlay.style.display = 'none';
document.getElementById('cancelAddModal').onclick= () => overlay.style.display = 'none';
overlay.onclick = e => { if (e.target === overlay) overlay.style.display = 'none'; }
</script>
</body>
</html>
