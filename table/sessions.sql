-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-11-16 03:33:27
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `dbm7g`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `data` text NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `sessions`
--

INSERT INTO `sessions` (`id`, `data`, `last_activity`) VALUES
('65tab848pc44kij5ho5b3p2dmm', 'login_error|s:44:\"No user found with the email = ramesh@gm.com\";userid|s:12:\"241005184456\";username|s:12:\"Ramesh Singh\";email|s:22:\"ramesh.singh@mazda.com\";role|s:6:\"member\";loggedin|b:1;itemid|s:14:\"20241106112143\";paslog|s:81:\"C:/xampp/htdocs/data/m7g/20241106112143\\S_DREC\\20240918104112\\TRC_PANA/dcu/paslog\";logvin|s:17:\"3MVDMBXY2RM610763\";', '2024-11-06 14:32:26'),
('vf43bgsnfpc49vass9llumgnav', 'userid|s:12:\"241005184456\";username|s:12:\"Ramesh Singh\";email|s:22:\"ramesh.singh@mazda.com\";role|s:6:\"member\";loggedin|b:1;itemid|s:14:\"20241116033124\";paslog|s:81:\"C:/xampp/htdocs/data/m7g/20241116033124\\S_DREC\\20240918104112\\TRC_PANA/dcu/paslog\";logvin|s:17:\"3MVDMBXY2RM610763\";', '2024-11-16 02:31:40');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
