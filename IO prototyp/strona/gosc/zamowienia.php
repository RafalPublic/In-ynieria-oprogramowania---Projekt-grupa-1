<?php
session_start();

// 1) Połączenie z bazą
$conn = new mysqli('localhost','root','','hotelsync');
if($conn->connect_error) die("Błąd połączenia: ".$conn->connect_error);

// 2) Pobranie ID gościa z GET (albo 1)
$userId = intval($_GET['user'] ?? 1);

// 3) Znajdź aktywną rezerwację na dziś (potrzebna do FK)
$today = date('Y-m-d');
$resAct = $conn->query("
  SELECT id_rezerwacja 
  FROM rezerwacja 
  WHERE id_user=$userId
    AND data_od <= '$today' 
    AND data_do >= '$today'
  LIMIT 1
");
$activeResId = ($resAct && $resAct->num_rows)
    ? $resAct->fetch_assoc()['id_rezerwacja']
    : null;

// 4) Inicjalizacja koszyka w sesji
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];  // [ id_danie => ilość ]
}

// 5) Dodaj/usuń pozycję
if(isset($_GET['add'])) {
    $did = intval($_GET['add']);
    $_SESSION['cart'][$did] = ($_SESSION['cart'][$did] ?? 0) + 1;
    header("Location: zamowienia.php?user=$userId");
    exit;
}
if(isset($_GET['remove'])) {
    $did = intval($_GET['remove']);
    unset($_SESSION['cart'][$did]);
    header("Location: zamowienia.php?user=$userId");
    exit;
}

// 6) Obsługa “Zapłać”
$message = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['pay'])) {
    if(!$activeResId) {
        $message = "Musisz mieć aktywną rezerwację pokoju, aby składać zamówienia.";
    } elseif(empty($_SESSION['cart'])) {
        $message = "Koszyk jest pusty.";
    } else {
        // a) Wstaw nagłówek zamówienia
        $stmt = $conn->prepare("
          INSERT INTO zamowienie_header
            (id_rezerwacja, data_zamowienia, status)
          VALUES (?, NOW(), 'nowe')
        ");
        $stmt->bind_param("i", $activeResId);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        // b) Wstaw pozycje
        $stmt = $conn->prepare("
          INSERT INTO zamowienie_item
            (id_order, id_danie, quantity)
          VALUES (?, ?, ?)
        ");
        foreach($_SESSION['cart'] as $did => $qty) {
            $stmt->bind_param("iii", $orderId, $did, $qty);
            $stmt->execute();
        }
        $stmt->close();

        // wyczyść koszyk i przejdź do płatności
        $_SESSION['cart'] = [];
        header("Location: platnosci.php?user=$userId&order=$orderId");
        exit;
    }
}

// 7) Pobierz menu restauracji wraz ze ścieżką do zdjęcia
$menu = $conn->query("
  SELECT id_danie, nazwa, cena, zdj_menu 
  FROM menu 
  WHERE dostepnosc=1
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Zamówienia – Hotel Atlantica</title>
  <link rel="stylesheet" href="style_gosc.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h1 class="logo">🏨 Hotel Atlantica</h1>
      <a href="pokoje.php?user=<?= $userId ?>"><button>Zarezerwuj</button></a>
      <a href="rezerwacje.php?user=<?= $userId ?>"><button>Twoje Rezerwacje</button></a>
      <button class="active">Zamówienia</button>
    </aside>

    <main class="main-content">
      <header class="header">
        <a href="../main/main.html" class="logout">Wyloguj się</a>
      </header>

      <?php if($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <h2 class="section-title">Menu restauracji</h2>
      <?php if($menu->num_rows): ?>
        <?php while($d = $menu->fetch_assoc()): ?>
          <div class="room-card">
            <div class="room-info">
              <h3><?= htmlspecialchars($d['nazwa']) ?></h3>
              <p><?= number_format($d['cena'],2) ?> zł</p>
              <!-- wyświetlamy ścieżkę z bazy -->
              <img src="<?= htmlspecialchars($d['zdj_menu']) ?>" alt="<?= htmlspecialchars($d['nazwa']) ?>" style="width:100%;max-width:200px;border-radius:4px;margin:8px 0;">
              <a href="zamowienia.php?user=<?= $userId ?>&add=<?= $d['id_danie'] ?>">
                <button>Dodaj do koszyka</button>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Brak dostępnych dań.</p>
      <?php endif; ?>

      <h2 class="section-title">Twój koszyk</h2>
      <?php if(!empty($_SESSION['cart'])): ?>
        <div class="form-container">
          <table>
            <thead>
              <tr>
                <th>Danie</th>
                <th>Ilość</th>
                <th>Cena jedn.</th>
                <th>Usuń</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $did => $qty):
                  $row = $conn->query("SELECT nazwa,cena,zdj_menu FROM menu WHERE id_danie=$did")
                             ->fetch_assoc();
                  $line = $row['cena'] * $qty;
                  $total += $line;
              ?>
                <tr>
                  <td>
                    <!-- miniaturka w Koszyku -->
                    <img src="<?= htmlspecialchars($row['zdj_menu']) ?>" alt="<?= htmlspecialchars($row['nazwa']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;margin-right:8px;vertical-align:middle;">
                    <?= htmlspecialchars($row['nazwa']) ?>
                  </td>
                  <td><?= $qty ?></td>
                  <td><?= number_format($row['cena'],2) ?> zł</td>
                  <td>
                    <a href="zamowienia.php?user=<?= $userId ?>&remove=<?= $did ?>">
                      <button>Anuluj</button>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <th colspan="2"></th>
                <th>Razem:</th>
                <th><?= number_format($total,2) ?> zł</th>
              </tr>
            </tbody>
          </table>

          <!-- 8) Formularz Zapłać -->
          <form method="post" action="zamowienia.php?user=<?= $userId ?>">
            <button type="submit" name="pay" value="1">
              Zapłać
            </button>
          </form>
        </div>
      <?php else: ?>
        <p>Koszyk jest pusty.</p>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
