<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hotel Atlantica - Użytkownicy</title>
<link rel="stylesheet" href="styles.css" />
</head>
<body>
<header class="top-bar">
<div class="logo">
<span class="icon">⦻</span>
<span class="name">Hotel Atlantica</span>
</div>
<div class="icons">
<span class="notif-icon">🔔</span>
<span class="notif-dot"></span>
<button class="logout">Wyloguj się</button>
</div>
</header>
<div class="main-container">
<nav class="sidebar">
<a href="admin-lista-pokoii.php"><button>Lista pokoi</button></a>
<a href="users.php"><button class="active">Użytkownicy</button></a>
<a href="admin-rezerwacje.php"><button>Rezerwacje</button></a>
<a href="platnosci.php"><button>Płatności</button></a>
<a href="raporty.php"><button>Raporty</button></a>
<a href="system.php"><button>System</button></a>
</nav>
<main class="content">
<div class="form-container">
<h2>Aktualizuj Dane Użytkownika</h2>
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
<button type="submit" class="submit-btn">Aktualizuj dane</button>
</form>
</div>
</main>
</div>
</body>
</html>