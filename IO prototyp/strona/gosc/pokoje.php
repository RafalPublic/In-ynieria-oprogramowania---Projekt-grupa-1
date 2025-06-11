<?php
// pokoje.php – lista wolnych pokoi i rezerwacja z obrazkami z bazy

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

// Dla testu: gość o ID = 1 (później z sesji/logowania)
$userId = 1;

// Obsługa POST: rezerwacja
$message = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['reserve'])) {
  $roomId = intval($_POST['room']);
  $from   = $conn->real_escape_string($_POST['date_from']);
  $to     = $conn->real_escape_string($_POST['date_to']);
  if(!$roomId || !$from || !$to) {
    $message = "Proszę wybrać daty.";
  } else {
    $ins = "INSERT INTO rezerwacja(id_pokoj,id_user,data_od,data_do)
            VALUES($roomId,$userId,'$from','$to')";
    if($conn->query($ins)) {
      $conn->query("UPDATE pokoj SET status='zajety' WHERE id_pokoj=$roomId");
      $resId = $conn->insert_id;
      header("Location: platnosci.php?user=$userId&res=$resId");
      exit;
    } else {
      $message = "Błąd zapisu: ".$conn->error;
    }
  }
}

// Pobranie wolnych pokoi (tylko wolne, serwis i zajęte pomijamy)
$res = $conn->query("
  SELECT id_pokoj, numer, typ, zdj_pokoj 
  FROM pokoj 
  WHERE status='wolny' 
  ORDER BY numer
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Zarezerwuj pokój – Hotel Atlantica</title>
  <link rel="stylesheet" href="style_gosc.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h1 class="logo">🏨 Hotel Atlantica</h1>
      <button class="active">Zarezerwuj</button>
      <a href="rezerwacje.php?user=<?= $userId ?>"><button>Twoje Rezerwacje</button></a>
      <a href="zamowienia.php"><button>Zamówienia</button></a>
    </aside>

    <main class="main-content">
      <header class="header">
        <a href="../main/main.html" class="logout">Wyloguj się</a>
      </header>

      <?php if($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <h2 class="section-title">Dostępne pokoje</h2>
      <?php if($res && $res->num_rows): ?>
        <?php while($r = $res->fetch_assoc()): ?>
          <div class="room-card">
            <img src="<?= htmlspecialchars($r['zdj_pokoj']) ?>"
                 alt="<?= htmlspecialchars($r['typ']) ?>"
                 class="room-img">
            <div class="room-info">
              <h3>Pokój <?= htmlspecialchars($r['typ']) ?></h3>
              <p>Numer: <?= htmlspecialchars($r['numer']) ?></p>
              <form method="post">
                <input type="hidden" name="room" value="<?= $r['id_pokoj'] ?>">
                <label>Data od
                  <input type="date" name="date_from" required>
                </label>
                <label>Data do
                  <input type="date" name="date_to" required>
                </label>
                <button type="submit" name="reserve">Zarezerwuj</button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Brak wolnych pokoi.</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
