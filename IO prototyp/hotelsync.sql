-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 11 Cze 2025, 11:50
-- Wersja serwera: 10.4.22-MariaDB
-- Wersja PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `hotelsync`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `menu`
--

CREATE TABLE `menu` (
  `id_danie` int(11) NOT NULL,
  `nazwa` varchar(50) NOT NULL,
  `cena` decimal(6,2) NOT NULL,
  `dostepnosc` tinyint(1) NOT NULL,
  `zdj_menu` varchar(255) DEFAULT NULL
) ;

--
-- Zrzut danych tabeli `menu`
--

INSERT INTO `menu` (`id_danie`, `nazwa`, `cena`, `dostepnosc`, `zdj_menu`) VALUES
(1, 'Zupa pomidorowa', '12.50', 1, '../foty/Pomidorowa.jpg'),
(2, 'Schabowy z ziemniakami', '25.00', 1, '../foty/Schabowy.jpg'),
(3, 'Sałatka grecka', '18.00', 1, '../foty/Grecka.jpg'),
(4, 'Pizza Margherita', '30.00', 1, '../foty/Margherita.jpg'),
(5, 'Pieczona Mewa', '21.37', 1, '../foty/Mewa.jpg');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `platnosci`
--

CREATE TABLE `platnosci` (
  `id_platnosc` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_rezerwacja` int(11) DEFAULT NULL,
  `id_zamowienie` int(11) DEFAULT NULL,
  `kwota` decimal(10,2) NOT NULL,
  `data_platnosci` datetime NOT NULL,
  `status` enum('zaksięgowane','oczekuje na płatność') NOT NULL,
  `metoda_platnosci` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `platnosci`
--

INSERT INTO `platnosci` (`id_platnosc`, `id_user`, `id_rezerwacja`, `id_zamowienie`, `kwota`, `data_platnosci`, `status`, `metoda_platnosci`) VALUES
(1, 1, 1, NULL, '500.00', '2025-06-01 12:30:00', 'zaksięgowane', 'karta kredytowa'),
(2, 1, 2, NULL, '750.00', '2025-06-09 14:15:00', 'oczekuje na płatność', 'przelew bankowy'),
(3, 1, NULL, 1, '12.50', '2025-06-01 13:05:00', 'zaksięgowane', 'gotówka'),
(4, 1, NULL, 2, '25.00', '2025-06-02 18:35:00', 'zaksięgowane', 'karta debetowa'),
(5, 1, NULL, 3, '18.00', '2025-06-11 12:20:00', 'oczekuje na płatność', 'karta kredytowa'),
(6, 1, NULL, 4, '30.00', '2025-06-19 23:10:00', 'zaksięgowane', 'aplikacja mobilna'),
(7, 6, NULL, NULL, '200.00', '2025-06-03 09:45:00', 'zaksięgowane', 'przelew bankowy');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pokoj`
--

CREATE TABLE `pokoj` (
  `id_pokoj` int(11) NOT NULL,
  `numer` varchar(10) NOT NULL,
  `typ` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL,
  `maks_ilosc_gosci` int(11) NOT NULL DEFAULT 2,
  `zdj_pokoj` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `pokoj`
--

INSERT INTO `pokoj` (`id_pokoj`, `numer`, `typ`, `status`, `maks_ilosc_gosci`, `zdj_pokoj`) VALUES
(1, '101', 'dungeon', 'wolny', 2, '../foty/Dungeon.png'),
(2, '102', 'lapis lazuli', 'zajety', 3, '../foty/Lapis_lazuli.png'),
(3, '103', 'ocean', 'zajety', 2, '../foty/Oceano.png'),
(4, '105', 'dungeon', 'wolny', 6, '../foty/Dungeon.png'),
(7, '104', 'dungeon', 'serwis', 2, '../foty/Dungeon.png'),
(9, '202', 'lapis lazuli', 'wolny', 2, '../foty/Lapis_lazuli.png'),
(10, '203', 'lapis lazuli', 'wolny', 2, '../foty/Lapis_lazuli.png');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacja`
--

CREATE TABLE `rezerwacja` (
  `id_rezerwacja` int(11) NOT NULL,
  `id_pokoj` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `data_od` date NOT NULL,
  `data_do` date NOT NULL
) ;

--
-- Zrzut danych tabeli `rezerwacja`
--

INSERT INTO `rezerwacja` (`id_rezerwacja`, `id_pokoj`, `id_user`, `data_od`, `data_do`) VALUES
(1, 2, 1, '2025-06-11', '2025-06-21'),
(2, 3, 1, '2025-06-10', '2025-06-15'),
(3, 7, 4, '2025-06-04', '2025-06-05'),
(4, 7, 4, '2025-06-04', '2025-06-05'),
(7, 3, 1, '2025-06-03', '2025-06-12');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `sprzatanie`
--

CREATE TABLE `sprzatanie` (
  `id_sprzatanie` int(11) NOT NULL,
  `id_pokoj` int(11) NOT NULL,
  `id_pracownik` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `sprzatanie`
--

INSERT INTO `sprzatanie` (`id_sprzatanie`, `id_pokoj`, `id_pracownik`, `data`, `status`) VALUES
(1, 1, 4, '2025-05-31 10:00:00', 'wykonane'),
(2, 3, 4, '2025-06-09 16:00:00', 'zaplanowane');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `rola` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `telefon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `user`
--

INSERT INTO `user` (`id_user`, `imie`, `nazwisko`, `rola`, `email`, `haslo`, `telefon`) VALUES
(1, 'Jan', 'Kowalski', 'gosc', 'jan.kowalski@example.com', 'gosc123', '+48501111222'),
(2, 'Anna', 'Nowak', 'pracownik_kuchnii', 'anna.nowak@example.com', 'kuchnia123', '+48502222333'),
(4, 'Katarzyna', 'Mazur', 'pracownik_sprzatajac', 'katarzyna.mazur@example.com', 'sprzatanie123', '+48504444555'),
(5, 'Admin', 'Hotelu', 'admin', 'admin@example.com', 'admin123', '+48505555666'),
(6, 'Marek', 'Marucha', 'gosc', 'marekmarucha@duzamaczuga.pl', '123', ''),
(8, 'Bil', 'Laden', 'pracownik_recepcji', 'lubie@placki.123', '123', '+48213732132');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienie`
--

CREATE TABLE `zamowienie` (
  `id_zamowienie` int(11) NOT NULL,
  `id_rezerwacji` int(11) NOT NULL,
  `id_danie` int(11) NOT NULL,
  `data_zamowienia` datetime NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `zamowienie`
--

INSERT INTO `zamowienie` (`id_zamowienie`, `id_rezerwacji`, `id_danie`, `data_zamowienia`, `status`) VALUES
(1, 1, 1, '2025-06-01 13:00:00', 'zrealizowane'),
(2, 1, 2, '2025-06-02 18:30:00', 'zrealizowane'),
(3, 2, 3, '2025-06-11 12:15:00', 'oczekuje'),
(4, 1, 4, '2025-06-19 23:06:15', 'w dostawie');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienie_header`
--

CREATE TABLE `zamowienie_header` (
  `id_order` int(11) NOT NULL,
  `id_rezerwacja` int(11) NOT NULL,
  `data_zamowienia` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'nowe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `zamowienie_header`
--

INSERT INTO `zamowienie_header` (`id_order`, `id_rezerwacja`, `data_zamowienia`, `status`) VALUES
(1, 7, '2025-06-08 13:42:31', 'nowe'),
(2, 7, '2025-06-08 13:43:01', 'nowe');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienie_item`
--

CREATE TABLE `zamowienie_item` (
  `id_item` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_danie` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Zrzut danych tabeli `zamowienie_item`
--

INSERT INTO `zamowienie_item` (`id_item`, `id_order`, `id_danie`, `quantity`) VALUES
(1, 1, 1, 7),
(2, 2, 1, 1),
(3, 2, 2, 2);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_danie`);

--
-- Indeksy dla tabeli `platnosci`
--
ALTER TABLE `platnosci`
  ADD PRIMARY KEY (`id_platnosc`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_rezerwacja` (`id_rezerwacja`),
  ADD KEY `id_zamowienie` (`id_zamowienie`);

--
-- Indeksy dla tabeli `pokoj`
--
ALTER TABLE `pokoj`
  ADD PRIMARY KEY (`id_pokoj`);

--
-- Indeksy dla tabeli `rezerwacja`
--
ALTER TABLE `rezerwacja`
  ADD PRIMARY KEY (`id_rezerwacja`),
  ADD KEY `id_pokoj` (`id_pokoj`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeksy dla tabeli `sprzatanie`
--
ALTER TABLE `sprzatanie`
  ADD PRIMARY KEY (`id_sprzatanie`),
  ADD KEY `id_pokoj` (`id_pokoj`),
  ADD KEY `id_pracownik` (`id_pracownik`);

--
-- Indeksy dla tabeli `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `zamowienie`
--
ALTER TABLE `zamowienie`
  ADD PRIMARY KEY (`id_zamowienie`),
  ADD KEY `id_rezerwacji` (`id_rezerwacji`),
  ADD KEY `id_danie` (`id_danie`);

--
-- Indeksy dla tabeli `zamowienie_header`
--
ALTER TABLE `zamowienie_header`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_rezerwacja` (`id_rezerwacja`);

--
-- Indeksy dla tabeli `zamowienie_item`
--
ALTER TABLE `zamowienie_item`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_danie` (`id_danie`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `menu`
--
ALTER TABLE `menu`
  MODIFY `id_danie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `platnosci`
--
ALTER TABLE `platnosci`
  MODIFY `id_platnosc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT dla tabeli `pokoj`
--
ALTER TABLE `pokoj`
  MODIFY `id_pokoj` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT dla tabeli `rezerwacja`
--
ALTER TABLE `rezerwacja`
  MODIFY `id_rezerwacja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `sprzatanie`
--
ALTER TABLE `sprzatanie`
  MODIFY `id_sprzatanie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT dla tabeli `zamowienie`
--
ALTER TABLE `zamowienie`
  MODIFY `id_zamowienie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT dla tabeli `zamowienie_header`
--
ALTER TABLE `zamowienie_header`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `zamowienie_item`
--
ALTER TABLE `zamowienie_item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `platnosci`
--
ALTER TABLE `platnosci`
  ADD CONSTRAINT `platnosci_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `platnosci_ibfk_2` FOREIGN KEY (`id_rezerwacja`) REFERENCES `rezerwacja` (`id_rezerwacja`),
  ADD CONSTRAINT `platnosci_ibfk_3` FOREIGN KEY (`id_zamowienie`) REFERENCES `zamowienie` (`id_zamowienie`);

--
-- Ograniczenia dla tabeli `rezerwacja`
--
ALTER TABLE `rezerwacja`
  ADD CONSTRAINT `rezerwacja_ibfk_1` FOREIGN KEY (`id_pokoj`) REFERENCES `pokoj` (`id_pokoj`),
  ADD CONSTRAINT `rezerwacja_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Ograniczenia dla tabeli `sprzatanie`
--
ALTER TABLE `sprzatanie`
  ADD CONSTRAINT `sprzatanie_ibfk_1` FOREIGN KEY (`id_pokoj`) REFERENCES `pokoj` (`id_pokoj`),
  ADD CONSTRAINT `sprzatanie_ibfk_2` FOREIGN KEY (`id_pracownik`) REFERENCES `user` (`id_user`);

--
-- Ograniczenia dla tabeli `zamowienie`
--
ALTER TABLE `zamowienie`
  ADD CONSTRAINT `zamowienie_ibfk_1` FOREIGN KEY (`id_rezerwacji`) REFERENCES `rezerwacja` (`id_rezerwacja`),
  ADD CONSTRAINT `zamowienie_ibfk_2` FOREIGN KEY (`id_danie`) REFERENCES `menu` (`id_danie`);

--
-- Ograniczenia dla tabeli `zamowienie_header`
--
ALTER TABLE `zamowienie_header`
  ADD CONSTRAINT `zamowienie_header_ibfk_1` FOREIGN KEY (`id_rezerwacja`) REFERENCES `rezerwacja` (`id_rezerwacja`);

--
-- Ograniczenia dla tabeli `zamowienie_item`
--
ALTER TABLE `zamowienie_item`
  ADD CONSTRAINT `zamowienie_item_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `zamowienie_header` (`id_order`),
  ADD CONSTRAINT `zamowienie_item_ibfk_2` FOREIGN KEY (`id_danie`) REFERENCES `menu` (`id_danie`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
