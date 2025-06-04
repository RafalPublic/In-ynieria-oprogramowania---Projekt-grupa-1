<?php
require('fpdf.php');

$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}


$date_from   = isset($_GET['date_from'])   ? $_GET['date_from']   : '';
$date_to     = isset($_GET['date_to'])     ? $_GET['date_to']     : '';
$user_id     = isset($_GET['user_id'])     ? intval($_GET['user_id']) : '';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'all';


function fetchPayments($conn, $date_from, $date_to, $user_id, $report_type) {
    $where = [];

    if (!empty($date_from)) {
        $df = $conn->real_escape_string($date_from);
        $where[] = "p.data_platnosci >= '$df'";
    }
    if (!empty($date_to)) {
        $dt = $conn->real_escape_string($date_to);
        $where[] = "p.data_platnosci <= '$dt'";
    }
    if (!empty($user_id)) {
        $uid = intval($user_id);
        $where[] = "p.id_user = $uid";
    }
    if ($report_type === 'due') {
        $where[] = "p.status = 'Nowa'";
    } elseif ($report_type === 'paid') {
        $where[] = "p.status = 'Zapłacone'";
    }

    $sql = "
      SELECT 
        p.id_platnosc,
        u.imie,
        u.nazwisko,
        p.id_rezerwacja,
        p.id_zamowienie,
        p.kwota,
        p.data_platnosci,
        p.status,
        p.metoda_platnosci
      FROM platnosci p
      JOIN user u ON p.id_user = u.id_user
    ";
    if (count($where) > 0) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= " ORDER BY u.nazwisko ASC, p.data_platnosci ASC";

    return $conn->query($sql);
}


$payments = fetchPayments($conn, $date_from, $date_to, $user_id, $report_type);


if (isset($_GET['download']) && $_GET['download'] == '1') {

    $result = $payments;


    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 15);


    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10, 'Wyciąg płatności – Hotel Atlantica', 0,1,'C');
    $pdf->Ln(4);


    $pdf->SetFont('Arial','B',10);

    $headers = [
        'ID'            => 10,
        'Użytkownik'    => 40,
        'ID Rezerwacji' => 20,
        'ID Zamówienia' => 20,
        'Kwota'         => 25,
        'Data płatności'=> 30,
        'Status'        => 25,
        'Metoda'        => 20
    ];

    foreach ($headers as $col => $w) {
        $pdf->Cell($w, 7, iconv('UTF-8','CP1250',$col), 1, 0, 'C');
    }
    $pdf->Ln();


    $pdf->SetFont('Arial','',10);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $userName = $row['imie'] . ' ' . $row['nazwisko'];
            $amount   = number_format($row['kwota'], 2, ',', ' ') . ' zł';
            $datePay  = $row['data_platnosci'];
            $status   = $row['status'];
            $method   = $row['metoda_platnosci'];

            $pdf->Cell($headers['ID'], 6, $row['id_platnosc'], 1, 0, 'C');
            $pdf->Cell($headers['Użytkownik'], 6, iconv('UTF-8','CP1250',$userName), 1, 0);
            $pdf->Cell($headers['ID Rezerwacji'], 6, $row['id_rezerwacja'] ?: '–', 1, 0, 'C');
            $pdf->Cell($headers['ID Zamówienia'], 6, $row['id_zamowienie'] ?: '–', 1, 0, 'C');
            $pdf->Cell($headers['Kwota'], 6, iconv('UTF-8','CP1250',$amount), 1, 0, 'R');
            $pdf->Cell($headers['Data płatności'], 6, iconv('UTF-8','CP1250',$datePay), 1, 0, 'C');
            $pdf->Cell($headers['Status'], 6, iconv('UTF-8','CP1250',$status), 1, 0, 'C');
            $pdf->Cell($headers['Metoda'], 6, iconv('UTF-8','CP1250',$method), 1, 0, 'C');
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(array_sum($headers), 6,
            iconv('UTF-8','CP1250','Brak danych o płatnościach.'), 1, 1, 'C');
    }


    $pdf->Output('D', 'wyciag_platnosci.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Atlantica – Wyciąg płatności</title>
  <link rel="stylesheet" href="styles_wyciag.css">
</head>
<body>
  <header class="top-bar">
    <div class="logo">
      <span class="icon">⦻</span>
      <span class="name">Hotel Atlantica</span>
      <link rel="stylesheet" href="styles.css" />
    </div>
    <div class="icons">
      <span class="notif-icon">🔔<span class="notif-dot"></span></span>
    </div>
  </header>

  <div class="main-content">

    <nav class="sidebar">
      <a href="admin-lista-pokoii.php"><button>Lista pokoi</button></a>
      <a href="users.php"><button>Użytkownicy</button></a>
      <a href="admin-rezerwacje.php"><button>Rezerwacje</button></a>
      <a href="platnosci.php"><button>Płatności</button></a>
      <a href="raporty.php"><button>Raporty</button></a>
      <a href="system.php"><button>System</button></a>
    </nav>


    <div style="display: flex; flex: 1;">

      <form class="filter-panel" method="get" action="wyciag.php">
        <label for="date_from">Od</label>
        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">

        <label for="date_to">Do</label>
        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">

        <label for="user_id">Użytkownik</label>
        <select id="user_id" name="user_id">
          <option value="" <?= $user_id === '' ? 'selected' : '' ?>>Wszyscy</option>
          <?php
          $uRes = $conn->query("SELECT id_user, imie, nazwisko FROM user ORDER BY nazwisko ASC");
          if ($uRes && $uRes->num_rows > 0) {
            while ($uRow = $uRes->fetch_assoc()) {
              $uid   = $uRow['id_user'];
              $uName = htmlspecialchars($uRow['imie'] . ' ' . $uRow['nazwisko']);
              $sel   = ($user_id == $uid) ? 'selected' : '';
              echo "<option value=\"$uid\" $sel>$uName</option>";
            }
          }
          ?>
        </select>

        <label for="report_type">Typ raportu</label>
        <select id="report_type" name="report_type">
          <option value="all" <?= $report_type === 'all' ? 'selected' : '' ?>>Wszystkie</option>
          <option value="due" <?= $report_type === 'due' ? 'selected' : '' ?>>Do zapłaty</option>
          <option value="paid" <?= $report_type === 'paid' ? 'selected' : '' ?>>Zapłacone</option>
        </select>

        <div class="button-group">
          <button type="submit" class="btn generate">Aktualizuj podgląd</button>
        </div>

        <div class="button-group">
          <?php

          $queryParams = [];
          if ($date_from)   $queryParams[] = "date_from=" . urlencode($date_from);
          if ($date_to)     $queryParams[] = "date_to=" . urlencode($date_to);
          if ($user_id)     $queryParams[] = "user_id=" . urlencode($user_id);
          if ($report_type) $queryParams[] = "report_type=" . urlencode($report_type);
          $queryParams[] = "download=1";
          $downloadLink = "wyciag.php?" . implode('&', $queryParams);
          ?>
          <a href="<?= htmlspecialchars($downloadLink) ?>" class="btn download">Generuj wyciąg</a>
        </div>
      </form>


      <div class="preview-panel">
        <h2>Podgląd wyciągu</h2>
        <table class="preview-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Użytkownik</th>
              <th>ID Rezerwacji</th>
              <th>ID Zamówienia</th>
              <th>Kwota (PLN)</th>
              <th>Data płatności</th>
              <th>Status</th>
              <th>Metoda płatności</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($payments && $payments->num_rows > 0): ?>
              <?php while ($row = $payments->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id_platnosc'] ?></td>
                  <td><?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']) ?></td>
                  <td><?= htmlspecialchars($row['id_rezerwacja'] ?: '–') ?></td>
                  <td><?= htmlspecialchars($row['id_zamowienie'] ?: '–') ?></td>
                  <td><?= number_format($row['kwota'], 2, ',', ' ') ?> zł</td>
                  <td><?= $row['data_platnosci'] ?></td>
                  <td><?= htmlspecialchars($row['status']) ?></td>
                  <td><?= htmlspecialchars($row['metoda_platnosci']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align:center;">Brak danych o płatnościach.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
