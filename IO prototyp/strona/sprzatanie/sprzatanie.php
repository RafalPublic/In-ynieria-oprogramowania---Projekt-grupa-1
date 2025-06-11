<?php
// admin-sprzatanie.php – przegląd pokoi w statusie 'serwis' i zatwierdzanie sprzątania

$host='localhost'; $db='hotelsync'; $user='root'; $pass='';
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Błąd połączenia: ".$conn->connect_error);

// 1) Obsługa POST: zatwierdzenie sprzątania
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['clean'])) {
  $id = intval($_POST['clean']);
  $conn->query("UPDATE pokoj SET status='wolny' WHERE id_pokoj=$id");
  // odświeżamy tę samą stronę:
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// 2) Pobranie pokoi do sprzątania
$res = $conn->query("
  SELECT id_pokoj, numer, typ, zdj_pokoj
  FROM pokoj
  WHERE status='serwis'
  ORDER BY numer ASC
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sprzątanie – Hotel Atlantica</title>
  <link rel="stylesheet" href="styles_sprzatanie.css" />
  <style>
    .filter-bar { display:flex; gap:8px; margin-bottom:16px; }
    .search-box { flex:1; padding:8px; border:1px solid #ccc; border-radius:4px; }
    .clean-table { width:100%; border-collapse:collapse; }
    .clean-table th, .clean-table td { border:1px solid #ccc; padding:8px; }
    .clean-table th { background:#333; color:#fff; }
    .clean-table img { width:80px; height:50px; object-fit:cover; border-radius:4px; }
    .clean-btn { padding:6px 12px; background:#1ec41e; color:#fff; border:none; border-radius:4px; cursor:pointer; }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', ()=>{
      const input = document.querySelector('.search-box');
      input.addEventListener('input', ()=>{
        const term = input.value.toLowerCase();
        document.querySelectorAll('.clean-table tbody tr').forEach(row=>{
          const num = row.querySelector('.col-numer').textContent.toLowerCase();
          const typ = row.querySelector('.col-typ').textContent.toLowerCase();
          row.style.display = (num.includes(term)||typ.includes(term)) ? '' : 'none';
        });
      });
    });
  </script>
</head>
<body>
<header class="top-bar">
  <div class="logo">Hotel Atlantica</div>
  <a href="../main/main.html"><button class="logout">Wyloguj się</button></a>
</header>

<main class="main-content">
  <aside class="sidebar">
    <button class="room-list-btn active">Lista pokoi do sprzątania</button>
  </aside>

  <section class="content">
    <div class="filter-bar">
      <input type="text" placeholder="Szukaj numer/typ…" class="search-box" />
    </div>

    <form method="post">
      <table class="clean-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Numer</th>
            <th>Typ</th>
            <th>Zdjęcie</th>
            <th>Akcja</th>
          </tr>
        </thead>
        <tbody>
          <?php if($res && $res->num_rows): ?>
            <?php while($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id_pokoj'] ?></td>
              <td class="col-numer"><?= htmlspecialchars($r['numer']) ?></td>
              <td class="col-typ"><?= htmlspecialchars($r['typ']) ?></td>
              <td>
                <img src="<?= htmlspecialchars($r['zdj_pokoj']) ?>"
                     alt="<?= htmlspecialchars($r['typ']) ?>">
              </td>
              <td>
                <button type="submit" name="clean"
                        value="<?= $r['id_pokoj'] ?>"
                        class="clean-btn">
                  Posprzątane
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center;">Brak pokoi w serwisie.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </form>
  </section>
</main>
</body>
</html>
