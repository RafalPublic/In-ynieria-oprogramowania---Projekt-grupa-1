<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dodaj Użytkownika</title>
<link rel="stylesheet" href="styles.css" />
</head>
<body>
<header class="top-header">
<div class="brand">
<span class="logo-symbol">⦻</span>
<span class="logo-text">Hotel Atlantica</span>
</div>
<div class="actions">
<span class="notification">🔔</span>
<span class="dot"></span>
<button class="logout-btn">Wyloguj się</button>
</div>
</header>
<div class="layout">
<aside class="menu">
<a href="admin-lista-pokoii.php"><button>Lista pokoi</button></a>
<a href="users.php"><button  class="selected">Użytkownicy</button></a>
<a href="admin-rezerwacje.php"><button>Rezerwacje</button></a>
<a href="platnosci.php"><button>Płatności</button></a>
<a href="raporty.php"><button>Raporty</button></a>
<a href="system.php"><button>System</button></a>
</aside>
<main class="main-content">
<section class="form-box">
<h2>Dodaj Użytkownika</h2>
<form>
<label>Imię</label>
<input type="text" placeholder="Dane">
<label>Nazwisko</label>
<input type="text" placeholder="Dane">
<label>Rola</label>
<select>
<option>Gość</option>
<option>Pracownik-recepcji</option>
<option>Pracownik-sprzątający</option>
<option>Pracownik-kuchnii</option>
<option>Admin</option>
</select>
<label>Email</label>
<input type="email" placeholder="Email">
<label>Telefon</label>
<input type="tel" placeholder="Tel +48">
<button type="submit" class="form-button">Dodaj</button>
</form>
</section>
</main>
</div>
</body>
</html>