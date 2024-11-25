-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-11-16 03:33:18
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
-- テーブルの構造 `hashtag`
--

CREATE TABLE `hashtag` (
  `tagid` char(14) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `tagname` varchar(128) NOT NULL,
  `email` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- テーブルのデータのダンプ `hashtag`
--

INSERT INTO `hashtag` (`tagid`, `tagname`, `email`) VALUES
('20241006195529', 'Rebooting', 'ramesh.singh@mazda.com'),
('20241007030222', 'Black screen', 'ramesh.singh@mazda.com'),
('20241007030235', 'Carplay Inop', 'ramesh.singh@mazda.com'),
('20241008172347', 'Screen flickers', 'ramesh.singh@mazda.com'),
('20241008232435', 'RameshSingh', 'ramesh.singh@mazda.com'),
('20241008232656', 'Ramesh Singh', 'ramesh.singh@mazda.com'),
('20241010222731', 'SeemaSingh', 'ramesh.singh@mazda.com'),
('20241023034115', 'Can not use wireless Carplay or Android Auto', 'ramesh.singh@mazda.com');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `hashtag`
--
ALTER TABLE `hashtag`
  ADD PRIMARY KEY (`tagid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
