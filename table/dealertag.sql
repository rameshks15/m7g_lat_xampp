-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-11-16 03:33:07
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
-- テーブルの構造 `dealertag`
--

CREATE TABLE `dealertag` (
  `tagid` char(12) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `dealer_num` char(5) NOT NULL,
  `resisterd_by` varchar(128) NOT NULL,
  `last_used_on` date NOT NULL,
  `usage_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- テーブルのデータのダンプ `dealertag`
--

INSERT INTO `dealertag` (`tagid`, `dealer_num`, `resisterd_by`, `last_used_on`, `usage_count`) VALUES
('241006195529', '30261', 'ramesh.singh', '2024-10-29', 1),
('241007030221', '30262', 'ramesh.singh', '2024-10-29', 10),
('241007030232', '30263', 'ramesh.singh', '2024-10-29', 20);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `dealertag`
--
ALTER TABLE `dealertag`
  ADD PRIMARY KEY (`tagid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
