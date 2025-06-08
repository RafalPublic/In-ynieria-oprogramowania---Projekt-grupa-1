<?php
// raporty.php – panel administracyjny z filtrowaniem i generowaniem PDF-raportów

require('fpdf.php');

// 1) Połączenie z bazą
$host='localhost'; $db='hotelsync'; $user='root'; $pass='';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Błąd połączenia: ".$conn->connect_error);

// 2) Odbiór GET: zakres dat i typ raportu
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';
$type     = $_GET['type']      ?? 'orders';

// 3) Jeżeli pobieramy PDF
if(isset($_GET['download']) && $_GET['download']==='1'){
    // tytuł PDF zależny od typu
    $titles = [
      'orders'   => 'Zamówienia wg dań',
      'guests'   => 'Liczba gości według pokoju',
      'revenue'  => 'Przychód dzienny'
    ];
    $title = $titles[$type] ?? 'Raport';

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10, "Raport: $title",0,1,'C');
    $pdf->SetFont('Arial','',12);
    if($dateFrom && $dateTo){
      $pdf->Cell(0,8, "Okres: $dateFrom — $dateTo",0,1,'C');
    }
    $pdf->Ln(5);

    // pobierz dane w zależności od typu
    switch($type){
      case 'orders':
        // ile razy zamówiono każde danie
        $sql = "
          SELECT m.nazwa, COUNT(z.id_danie) AS ile
          FROM zamowienie z
          JOIN menu m ON z.id_danie=m.id_danie
          WHERE DATE(z.data_zamowienia) BETWEEN '$dateFrom' AND '$dateTo'
          GROUP BY m.id_danie ORDER BY ile DESC
        ";
        $res = $conn->query($sql);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(120,8,'Nazwa dania',1);
        $pdf->Cell(40,8,'Ilość zam.',1,1,'R');
        $pdf->SetFont('Arial','',12);
        while($r=$res->fetch_assoc()){
          $pdf->Cell(120,8,iconv('UTF-8','CP1250',$r['nazwa']),1);
          $pdf->Cell(40,8,$r['ile'],1,1,'R');
        }
        break;

      case 'guests':
        // ilu różnych gości było na rezerwacjach
        $sql = "
          SELECT p.numer, COUNT(DISTINCT r.id_user) AS guests
          FROM rezerwacja r
          JOIN pokoj p ON r.id_pokoj=p.id_pokoj
          WHERE r.data_od BETWEEN '$dateFrom' AND '$dateTo'
          GROUP BY p.id_pokoj ORDER BY guests DESC
        ";
        $res = $conn->query($sql);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(60,8,'Numer pokoju',1);
        $pdf->Cell(60,8,'Liczba gości',1,1,'R');
        $pdf->SetFont('Arial','',12);
        while($r=$res->fetch_assoc()){
          $pdf->Cell(60,8,$r['numer'],1);
          $pdf->Cell(60,8,$r['guests'],1,1,'R');
        }
        break;

      case 'revenue':
        // przychód dzienny
        $sql = "
          SELECT DATE(p.data_platnosci) AS day, SUM(p.kwota) AS total
          FROM platnosci p
          WHERE p.data_platnosci BETWEEN '$dateFrom 00:00:00' AND '$dateTo 23:59:59'
          GROUP BY day ORDER BY day ASC
        ";
        $res = $conn->query($sql);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(60,8,'Data',1);
        $pdf->Cell(60,8,'Przychód (PLN)',1,1,'R');
        $pdf->SetFont('Arial','',12);
        while($r=$res->fetch_assoc()){
          $pdf->Cell(60,8,$r['day'],1);
          $pdf->Cell(60,8,number_format($r['total'],2).' zł',1,1,'R');
        }
        break;
    }

    $pdf->Output('D',"raport_{$type}_{$dateFrom}_{$dateTo}.pdf");
    exit;
}

// 4) Pobranie podglądu (te same zapytania co wyżej)
$previewData = [];
switch($type){
  case 'orders':
    $q = "
      SELECT m.nazwa, COUNT(z.id_danie) AS ile
      FROM zamowienie z
      JOIN menu m ON z.id_danie=m.id_danie
      WHERE DATE(z.data_zamowienia) BETWEEN '$dateFrom' AND '$dateTo'
      GROUP BY m.id_danie ORDER BY ile DESC
    ";
    $previewData = $conn->query($q);
    break;
  case 'guests':
    $q = "
      SELECT p.numer, COUNT(DISTINCT r.id_user) AS guests
      FROM rezerwacja r
      JOIN pokoj p ON r.id_pokoj=p.id_pokoj
      WHERE r.data_od BETWEEN '$dateFrom' AND '$dateTo'
      GROUP BY p.id_pokoj ORDER BY guests DESC
    ";
    $previewData = $conn->query($q);
    break;
  case 'revenue':
    $q = "
      SELECT DATE(p.data_platnosci) AS day, SUM(p.kwota) AS total
      FROM platnosci p
      WHERE p.data_platnosci BETWEEN '$dateFrom 00:00:00' AND '$dateTo 23:59:59'
      GROUP BY day ORDER BY day ASC
    ";
    $previewData = $conn->query($q);
    break;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Raporty – Hotel Atlantica</title>
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
    <a href="admin-rezerwacje.php"><button>Rezerwacje</button></a>
    <a href="platnosci.php"><button>Płatności</button></a>
    <a href="raporty.php"><button class="active">Raporty</button></a>
    <a href="system.php"><button>System</button></a>
  </aside>
  <main class="content">
    <h2>Generuj raport</h2>
    <form method="get" class="filter-bar">
      <label>Od:</label>
      <input type="date" name="date_from" value="<?=htmlspecialchars($dateFrom)?>" required>
      <label>Do:</label>
      <input type="date" name="date_to"   value="<?=htmlspecialchars($dateTo)?>" required>
      <label>Typ raportu:</label>
      <select name="type">
        <option value="orders"  <?=$type==='orders'?'selected':''?>>Zamówienia wg dań</option>
        <option value="guests"  <?=$type==='guests'?'selected':''?>>Goście wg pokoju</option>
        <option value="revenue" <?=$type==='revenue'?'selected':''?>>Przychód dzienny</option>
      </select>
      <button type="submit" class="btn generate-button">Pokaż podgląd</button>
      <button type="submit" name="download" value="1" class="btn generate-button">Pobierz PDF</button>
    </form>

    <h3>Podgląd: <?= html_entity_decode([
      'orders'=>'Zamówienia wg dań',
      'guests'=>'Goście wg pokoju',
      'revenue'=>'Przychód dzienny'
    ][$type]) ?></h3>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <?php if($type==='orders'): ?>
              <th>Danie</th><th>Ilość</th>
            <?php elseif($type==='guests'): ?>
              <th>Pokój</th><th>Liczba gości</th>
            <?php else: ?>
              <th>Data</th><th>Przychód (PLN)</th>
            <?php endif;?>
          </tr>
        </thead>
        <tbody>
          <?php if($previewData && $previewData->num_rows): ?>
            <?php while($r=$previewData->fetch_assoc()): ?>
              <tr>
              <?php if($type==='orders'): ?>
                <td><?=htmlspecialchars($r['nazwa'])?></td>
                <td><?=$r['ile']?></td>
              <?php elseif($type==='guests'): ?>
                <td><?=htmlspecialchars($r['numer'])?></td>
                <td><?=$r['guests']?></td>
              <?php else: ?>
                <td><?=$r['day']?></td>
                <td><?=number_format($r['total'],2)?> zł</td>
              <?php endif;?>
              </tr>
            <?php endwhile;?>
          <?php else: ?>
            <tr><td colspan="2" style="text-align:center">Brak danych dla tego okresu</td></tr>
          <?php endif;?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
