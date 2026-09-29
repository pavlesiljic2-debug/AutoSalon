-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 03:16 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autosalon`
--

-- --------------------------------------------------------

--
-- Table structure for table `nalog`
--

CREATE TABLE `nalog` (
  `id` int NOT NULL,
  `ime` varchar(255) NOT NULL,
  `prezime` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lozinka` varchar(255) NOT NULL,
  `uloga` int NOT NULL,
  `datum_registracije` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nalog`
--

INSERT INTO `nalog` (`id`, `ime`, `prezime`, `email`, `lozinka`, `uloga`, `datum_registracije`) VALUES
(5, 'Marko', 'Markovic', 'markomarkovic@gmail.com', '$2y$10$qY1l7vfzzbY938UEqTsmmeoPHv8DeH7djtR5VAEQsqM/GOApP0xnq', 1, '2025-10-08 12:57:17'),
(6, 'Uros', 'Urosevic', 'urosurosevic@gmail.com', '$2y$10$6jEprfI.6YWnWaYk/iBIWemdL4SP8oHQl9OdTKTwysSXpxM9UH.5O', 1, '2025-10-08 12:58:03'),
(7, 'Moderator', 'Moderator', 'moderator@gmail.com', '$2y$10$kqiBxscHu.Aor.z/z1Sek.D7.1SHZeRs9EvbNY2y1Tz64fCNJu22G', 2, '2025-10-08 13:06:09'),
(8, 'Pavle', 'Siljic', 'pavlesiljic@gmail.com', '$2y$10$arJbiM6fCUN6jExnUAMdeOOYKLAm94Xs/YsO4v1IKsc9SnNjc6A6u', 3, '2025-10-08 13:12:37');

-- --------------------------------------------------------

--
-- Table structure for table `recenzija`
--

CREATE TABLE `recenzija` (
  `id` int NOT NULL,
  `nalog_fk` int NOT NULL,
  `vozilo_fk` int NOT NULL,
  `komentar` text NOT NULL,
  `ocena` int NOT NULL,
  `dozvola` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recenzija`
--

INSERT INTO `recenzija` (`id`, `nalog_fk`, `vozilo_fk`, `komentar`, `ocena`, `dozvola`) VALUES
(2, 5, 17, 'komentar o Golf 8..', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `upit`
--

CREATE TABLE `upit` (
  `id` int NOT NULL,
  `vozilo_fk` int DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `naziv` varchar(255) NOT NULL,
  `pitanje` text NOT NULL,
  `odgovor` text,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `upit`
--

INSERT INTO `upit` (`id`, `vozilo_fk`, `email`, `naziv`, `pitanje`, `odgovor`, `status`) VALUES
(7, 22, 'posetilac1@gmail.com', 'Pitanje o Ford Focu-u', 'pitanje..', 'odgovor na upit neregistorvanog korisnika..', 'U obradi'),
(8, 19, 'markomarkovic@gmail.com', 'Zahtev za kupovinu za Opel Astra', 'ponuda..', 'odgovor na upit registrovanog krosnika..', 'Odgovoreno');

-- --------------------------------------------------------

--
-- Table structure for table `vozilo`
--

CREATE TABLE `vozilo` (
  `id` int NOT NULL,
  `marka` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `godiste` int NOT NULL,
  `tip_motora` varchar(255) NOT NULL,
  `kubikaza_motora` int NOT NULL,
  `snaga_motora` int NOT NULL,
  `cena` float NOT NULL,
  `opis` text NOT NULL,
  `naziv_slike` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vozilo`
--

INSERT INTO `vozilo` (`id`, `marka`, `model`, `godiste`, `tip_motora`, `kubikaza_motora`, `snaga_motora`, `cena`, `opis`, `naziv_slike`) VALUES
(15, 'Audi', 'A3', 2024, 'Benzin', 1400, 150, 25500, 'Novo vozilo, bez vožnje.', 'audia3.jpg'),
(16, 'BMW', '320d', 2025, 'Dizel', 2000, 190, 34900, 'Novi model, garancija.', 'bmw320d.jpg'),
(17, 'Volkswagen', 'Golf 8', 2024, 'Benzin', 1600, 120, 22300, 'Premium oprema, 0 km.', 'vwgolf8.jpg'),
(18, 'Škoda', 'Octavia', 2025, 'Dizel', 1600, 115, 20900, 'Novo, najnovija generacija.', 'skodaoctavia.jpg'),
(19, 'Opel', 'Astra', 2024, 'Benzin', 1400, 110, 18500, 'Nova Astra, ekonomska potrošnja.', 'opelastra.jpg'),
(20, 'Renault', 'Megane', 2025, 'Dizel', 1500, 110, 21500, 'Novi Megane, full oprema.', 'renaultmegane.jpg'),
(21, 'Peugeot', '308', 2024, 'Benzin', 1200, 130, 19700, 'Novo vozilo iz salona.', 'peugeot308.jpg'),
(22, 'Ford', 'Focus', 2025, 'Dizel', 1600, 115, 18800, 'Najnoviji model.', 'fordfocus.jpg'),
(23, 'Mercedes', 'C200', 2024, 'Dizel', 2200, 170, 37900, 'Novo vozilo, luksuz.', 'mercedesc200.jpg'),
(24, 'Toyota', 'Corolla', 2025, 'Benzin', 1800, 140, 23900, 'Salon vozilo, bez korišćenja.', 'toyotacorolla.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `zahtev_za_test_voznju`
--

CREATE TABLE `zahtev_za_test_voznju` (
  `id` int NOT NULL,
  `nalog_fk` int NOT NULL,
  `vozilo_fk` int NOT NULL,
  `datum` int NOT NULL,
  `vreme_od` int NOT NULL,
  `vrme_do` int NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zahtev_za_test_voznju`
--

INSERT INTO `zahtev_za_test_voznju` (`id`, `nalog_fk`, `vozilo_fk`, `datum`, `vreme_od`, `vrme_do`, `status`) VALUES
(5, 5, 19, 1761778800, 9, 10, 'Odobren');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nalog`
--
ALTER TABLE `nalog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recenzija`
--
ALTER TABLE `recenzija`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nalog_fk` (`nalog_fk`),
  ADD KEY `vozilo_fk` (`vozilo_fk`);

--
-- Indexes for table `upit`
--
ALTER TABLE `upit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vozilo_fk` (`vozilo_fk`);

--
-- Indexes for table `vozilo`
--
ALTER TABLE `vozilo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zahtev_za_test_voznju`
--
ALTER TABLE `zahtev_za_test_voznju`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nalog_fk` (`nalog_fk`),
  ADD KEY `vozilo_fk` (`vozilo_fk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nalog`
--
ALTER TABLE `nalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recenzija`
--
ALTER TABLE `recenzija`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `upit`
--
ALTER TABLE `upit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vozilo`
--
ALTER TABLE `vozilo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `zahtev_za_test_voznju`
--
ALTER TABLE `zahtev_za_test_voznju`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recenzija`
--
ALTER TABLE `recenzija`
  ADD CONSTRAINT `recenzija_ibfk_1` FOREIGN KEY (`nalog_fk`) REFERENCES `nalog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recenzija_ibfk_2` FOREIGN KEY (`vozilo_fk`) REFERENCES `vozilo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `upit`
--
ALTER TABLE `upit`
  ADD CONSTRAINT `upit_ibfk_1` FOREIGN KEY (`vozilo_fk`) REFERENCES `vozilo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `zahtev_za_test_voznju`
--
ALTER TABLE `zahtev_za_test_voznju`
  ADD CONSTRAINT `zahtev_za_test_voznju_ibfk_1` FOREIGN KEY (`nalog_fk`) REFERENCES `nalog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `zahtev_za_test_voznju_ibfk_2` FOREIGN KEY (`vozilo_fk`) REFERENCES `vozilo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
