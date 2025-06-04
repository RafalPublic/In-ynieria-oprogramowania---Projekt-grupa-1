-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 23 Kwi 2025, 10:58
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
  `dostepnosc` tinyint(1) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pokoj`
--

CREATE TABLE `pokoj` (
  `id_pokoj` int(11) NOT NULL,
  `numer` varchar(10) NOT NULL,
  `typ` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `rola` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_danie`);

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
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `menu`
--
ALTER TABLE `menu`
  MODIFY `id_danie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `pokoj`
--
ALTER TABLE `pokoj`
  MODIFY `id_pokoj` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `rezerwacja`
--
ALTER TABLE `rezerwacja`
  MODIFY `id_rezerwacja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `sprzatanie`
--
ALTER TABLE `sprzatanie`
  MODIFY `id_sprzatanie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT dla tabeli `zamowienie`
--
ALTER TABLE `zamowienie`
  MODIFY `id_zamowienie` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ograniczenia dla zrzutów tabel
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
