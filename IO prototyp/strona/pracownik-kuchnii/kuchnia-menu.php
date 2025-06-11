<?php
$host = 'localhost';
$db   = 'hotelsync';
$user = 'root';
$pass = '';
$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) {
  die("Błąd połączenia: " . $conn->connect_error);
}

// Ajax: toggle dostępności
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_id'])) {
  $id        = intval($_POST['toggle_id']);
  $newStatus = intval($_POST['new_status']);
  $conn->query("UPDATE menu SET dostepnosc=$newStatus WHERE id_danie=$id");
  exit;
}

// Pobierz dania
$res    = $conn->query("SELECT id_danie,nazwa,cena,dostepnosc FROM menu ORDER BY id_danie DESC");
$dishes = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Hotel Atlantica – Menu</title>
  <link rel="stylesheet" href="styles_kuchnia.css">
  <style>
    /* kontener główny obok sidebaru */
    .content {
      flex:1;
      display: flex;
      flex-direction: column;
      gap: 20px;
      padding: 20px;
      background: #d6d6d6;
    }

    /* poziome filtry nad daniami */
    .filters-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .filters-row .search-input {
      flex: 1;
      max-width: 240px;
    }
    .filters-row .filter-btn {
      padding: 8px 16px;
      background: #ddd;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .filters-row .filter-btn.active {
      background: #8c5c5c;
      color: white;
    }

    /* pozioma siatka dań */
    .dishes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
      overflow-y: auto;
    }
    .dish-card {
      background: #fff;
      border-radius: 8px;
      padding: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      text-align: center;
      position: relative;
    }
    .dish-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 4px;
      margin-bottom: 8px;
    }
    .dish-card h4 { margin: 8px 0 4px; font-size: 16px; }
    .dish-card .price { font-weight: bold; margin-bottom: 12px; }

    /* switch */
    .switch {
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', ()=>{
      const grid        = document.querySelector('.dishes-grid');
      const cards       = Array.from(grid.children);
      const searchInput = document.querySelector('.filters-row .search-input');
      const btns        = document.querySelectorAll('.filters-row .filter-btn');

      // filtrowanie po nazwie
      searchInput.addEventListener('input', ()=>{
        const term = searchInput.value.toLowerCase();
        cards.forEach(c=>{
          const name = c.querySelector('h4').textContent.toLowerCase();
          c.style.display = name.includes(term) ? '' : 'none';
        });
      });

      // sortowanie
      btns.forEach(b=>{
        b.addEventListener('click', ()=>{
          btns.forEach(x=>x.classList.remove('active'));
          b.classList.add('active');
          const mode = b.dataset.filter;
          let sorted = [...cards];
          if (mode==='new')          sorted.sort((a,b)=>b.dataset.id - a.dataset.id);
          if (mode==='price-asc')    sorted.sort((a,b)=> parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
          if (mode==='price-desc')   sorted.sort((a,b)=> parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
          grid.innerHTML = '';
          sorted.forEach(c=>grid.appendChild(c));
        });
      });
    });

    function toggleAvailability(id, chk){
      fetch('kuchnia-menu.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`toggle_id=${id}&new_status=${chk.checked?1:0}`
      });
    }
  </script>
</head>
<body>
  <header class="header">
    <div class="logo">
      <span class="icon">🏨</span> Hotel Atlantica
    </div>
    <a href="../main/main.html">
      <button class="logout-button">Wyloguj się</button>
    </a>
  </header>

  <div class="main">
    <aside class="sidebar">
      <a href="kuchnia-zamowienia.php"><button class="menu-button">Zamówienia</button></a>
      <button class="menu-button active">Menu</button>
    </aside>

    <section class="content">
      <!-- FILTRY POZIOME -->
      <div class="filters-row">
        <input type="text" class="search-input" placeholder="Szukaj dań…" />
        <button class="filter-btn active"    data-filter="new">Nowe</button>
        <button class="filter-btn"           data-filter="price-asc">Cena ↑</button>
        <button class="filter-btn"           data-filter="price-desc">Cena ↓</button>
        <button class="filter-btn"           data-filter="rating">Oceny</button>
      </div>

      <!-- SIATKA DAŃ POZIOMO -->
      <div class="dishes-grid">
        <?php foreach($dishes as $d): ?>
        <div class="dish-card"
             data-id="<?= $d['id_danie'] ?>"
             data-price="<?= $d['cena'] ?>">
          <img src="<?= strtolower(str_replace(' ','_',$d['nazwa'])) ?>.jpg"
               alt="<?= htmlspecialchars($d['nazwa']) ?>">
          <h4><?= htmlspecialchars($d['nazwa']) ?></h4>
          <p class="price"><?= number_format($d['cena'],2) ?> zł</p>
          <label class="switch">
            <input type="checkbox"
                   <?= $d['dostepnosc']?'checked':'' ?>
                   onchange="toggleAvailability(<?= $d['id_danie'] ?>,this)">
            <span class="slider"></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</body>
</html>
