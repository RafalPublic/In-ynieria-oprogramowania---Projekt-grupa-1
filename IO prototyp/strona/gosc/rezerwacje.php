<?php
// rezerwacje.php – przegląd i anulowanie rezerwacji gościa

$host='localhost'; $db='hotelsync'; $user='root'; $pass='';
$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Błąd połączenia: ".$conn->connect_error);

// **Automatycznie przenieś zakończone rezerwacje do serwisu**
$conn->query("
  UPDATE pokoj p
  JOIN rezerwacja r ON p.id_pokoj = r.id_pokoj
  SET p.status = 'serwis'
  WHERE r.data_do < CURDATE()
    AND p.status <> 'serwis'
");

// Pobieramy ID gościa z GET (albo default 1)
$userId = intval($_GET['user'] ?? 1);

// Obsługa anulowania
if(isset($_GET['cancel'])) {
  $rid = intval($_GET['cancel']);
  $old = $conn->query(
    "SELECT id_pokoj FROM rezerwacja WHERE id_rezerwacja=$rid AND id_user=$userId"
  )->fetch_assoc()['id_pokoj'];
  $conn->query("DELETE FROM rezerwacja WHERE id_rezerwacja=$rid");
  // przejdzie do serwisu i czeka na sprzątanie
  header("Location: rezerwacje.php?user=$userId");
  exit;
}

// Pobranie rezerwacji wraz ze ścieżką do zdjęcia pokoju
$rs = $conn->query("
  SELECT r.id_rezerwacja, r.data_od, r.data_do,
         p.typ, p.numer, p.zdj_pokoj
  FROM rezerwacja r
  JOIN pokoj p ON r.id_pokoj=p.id_pokoj
  WHERE r.id_user=$userId
  ORDER BY r.data_od DESC
");

function status($from,$to){
  $t = date('Y-m-d');
  if($to < $t)             return 'odbyta';
  if($from <= $t && $t<=$to) return 'w trakcie';
  return 'w przyszlosci';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Twoje rezerwacje – Hotel Atlantica</title>
  <link rel="stylesheet" href="style_gosc.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h1 class="logo">🏨 Hotel Atlantica</h1>
      <a href="pokoje.php?user=<?= $userId ?>"><button>Zarezerwuj</button></a>
      <button class="active">Twoje Rezerwacje</button>
      <a href="zamowienia.php"><button>Zamówienia</button></a>
    </aside>

    <main class="main-content">
      <header class="header">
        <a href="../main/main.html" class="logout">Wyloguj się</a>
      </header>

      <h2 class="section-title">Twoje rezerwacje</h2>
      <?php if($rs && $rs->num_rows): ?>
        <?php while($r = $rs->fetch_assoc()): ?>
          <?php $st = status($r['data_od'],$r['data_do']); ?>
          <div class="room-card">
            <!-- zdjęcie z bazy -->
            <img src="<?= htmlspecialchars($r['zdj_pokoj']) ?>"
                 alt="<?= htmlspecialchars($r['typ']) ?>"
                 style="width:150px;height:100px;object-fit:cover;border-radius:4px;">
            <div class="room-info">
              <h3>Pokój <?=htmlspecialchars($r['typ'])?> (<?=htmlspecialchars($r['numer'])?>)</h3>
              <p><?=htmlspecialchars($r['data_od'])?> → <?=htmlspecialchars($r['data_do'])?></p>
              <a href="rezerwacje.php?user=<?= $userId ?>&cancel=<?= $r['id_rezerwacja'] ?>">
                <button>Anuluj rezerwację</button>
              </a>
            </div>
            <div class="room-status <?= $st==='w trakcie'?'green':($st==='w przyszlosci'?'yellow':'') ?>">
              <span class="dot"></span> <?= $st ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Nie masz żadnych rezerwacji.</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
