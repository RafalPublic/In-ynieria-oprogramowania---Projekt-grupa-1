<?php
// system.php – panel administracyjny: bezpieczne pobieranie danych systemowych

// 1) Pobranie danych systemowych z zabezpieczeniami
// Load average (Unix-only)
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
} else {
    $load = [null, null, null];  // N/A
}

// Pamięć używana przez skrypt (w bajtach)
$memUsage = memory_get_usage(true);
$memPeak  = memory_get_peak_usage(true);

// Całkowita i wolna przestrzeń dyskowa (na partycji "/"), w bajtach
$diskTotal = @disk_total_space('/') ?: null;
$diskFree  = @disk_free_space('/')  ?: null;

// Uptime systemu: dla Unix próbujemy 'uptime -p', w Windows dajemy N/A
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $up = @shell_exec('uptime -p');
    $uptime = $up ? trim($up) : 'N/A';
} else {
    $uptime = 'N/A';
}

// Pomocnicza funkcja do czytelnego formatu pamięci i przestrzeni dyskowej
function formatBytes($bytes) {
    if ($bytes === null) return 'N/A';
    $units = ['B','KB','MB','GB','TB'];
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    return number_format($bytes / pow(1024, $power), 2).' '.$units[$power];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel System – Hotel Atlantica</title>
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
    <a href="raporty.php"><button>Raporty</button></a>
    <a href="system.php"><button class="active">System</button></a>
  </aside>

  <main class="content">
    <h2>Stan systemu</h2>
    <table class="system-stats">
      <tr>
        <th>Uptime</th>
        <td><?= htmlspecialchars($uptime) ?></td>
      </tr>
      <tr>
        <th>Load Average (1m, 5m, 15m)</th>
        <td>
          <?= $load[0] !== null ? number_format($load[0], 2) : 'N/A' ?>,
          <?= $load[1] !== null ? number_format($load[1], 2) : 'N/A' ?>,
          <?= $load[2] !== null ? number_format($load[2], 2) : 'N/A' ?>
        </td>
      </tr>
      <tr>
        <th>Pamięć RAM używana przez skrypt</th>
        <td><?= formatBytes($memUsage) ?></td>
      </tr>
      <tr>
        <th>Pik pamięci RAM (skrypt)</th>
        <td><?= formatBytes($memPeak) ?></td>
      </tr>
      <tr>
        <th>Całkowita przestrzeń dyskowa</th>
        <td><?= formatBytes($diskTotal) ?></td>
      </tr>
      <tr>
        <th>Wolna przestrzeń dyskowa</th>
        <td><?= formatBytes($diskFree) ?></td>
      </tr>
    </table>
  </main>
</div>
</body>
</html>
