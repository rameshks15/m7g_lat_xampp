-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2024-11-16 17:44:22
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
-- テーブルの構造 `analysis`
--

CREATE TABLE `analysis` (
  `itemid` char(14) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `tag` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `desc` text NOT NULL,
  `vin` text NOT NULL,
  `dealer_number` char(5) NOT NULL,
  `status` varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `email` text NOT NULL,
  `version` text NOT NULL,
  `result` text DEFAULT NULL,
  `model` text NOT NULL,
  `detail` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- テーブルのデータのダンプ `analysis`
--

INSERT INTO `analysis` (`itemid`, `tag`, `date`, `time`, `desc`, `vin`, `dealer_number`, `status`, `email`, `version`, `result`, `model`, `detail`) VALUES
('20241116033124', 'Carplay Inop', '2024-11-14', '21:33:00', 'Carplay Inop', '3MVDMBXY2RM610763', '30261', 'analyzed', 'ramesh.singh@mazda.com', 'Part# DRVP-669C0-C, Ver# 10022;\r\n								 Part# BDTS-66DR0-B, Ver# 26016;\r\n								 Part# DPTW-66A20-, Ver# 34006', 'Software issue, WiFi_CPAA_Off_Error', 'CX-30, 2024', '<b>pas_systemdata.log:</b><br>VP_CMU_CARPLAY=available_wireless\n<br><br><b>pas_debug_connect_func.log:</b><br>00:00:03.186/21212/29/CarPlayService/00510/CP_Common_if.cpp/02022/=[CarPlayService_SetSupportedStatus] cpsrv_supported_status:2\r\n<br><br><b>pas_alsa.log:</b><br>00:00:02.295/675/29/NS_BkupNAND/00268/bkup_process.cpp/01291/=NaviProxy/RD/D_BK_ID_WIFI_FREQ_SW_SETTING/o:0/s:1/h:00 -- -- --/c:0/r:0\r\n<br><br>'),
('20241116173922', 'Carplay Inop', '2024-11-12', '11:41:00', 'CMU reboots and carplay does not connect and inoperative. MUS4273846', '3MVDMBXY2RM610763', '30261', 'analyzed', 'ramesh.singh@mazda.com', 'Part# DRVP-669C0-C, Ver# 10022;\r\n								 Part# BDTS-66DR0-B, Ver# 26016;\r\n								 Part# DPTW-66A20-, Ver# 34006', 'Software issue, WiFi_CPAA_Off_Error', 'CX-30, 2024', '<b>pas_systemdata.log:</b><br>VP_CMU_CARPLAY=available_wireless\n<br><br><b>pas_debug_connect_func.log:</b><br>00:00:03.186/21212/29/CarPlayService/00510/CP_Common_if.cpp/02022/=[CarPlayService_SetSupportedStatus] cpsrv_supported_status:2\r\n<br><br><b>pas_alsa.log:</b><br>00:00:02.295/675/29/NS_BkupNAND/00268/bkup_process.cpp/01291/=NaviProxy/RD/D_BK_ID_WIFI_FREQ_SW_SETTING/o:0/s:1/h:00 -- -- --/c:0/r:0\r\n<br><br>'),
('20241116174102', 'Carplay Inop', '2024-11-14', '11:43:00', 'The Wi-Fi setting is turned off and Wireless CarPlay cannot be used (same for Android Auto). RTC 245193', '3MVDMBXY2RM610763', '30261', 'analyzed', 'ramesh.singh@mazda.com', 'Part# DRVP-669C0-C, Ver# 10022;\r\n								 Part# BDTS-66DR0-B, Ver# 26016;\r\n								 Part# DPTW-66A20-, Ver# 34006', 'Software issue, WiFi_CPAA_Off_Error', 'CX-30, 2024', '<b>pas_systemdata.log:</b><br>VP_CMU_CARPLAY=available_wireless\n<br><br><b>pas_debug_connect_func.log:</b><br>00:00:03.186/21212/29/CarPlayService/00510/CP_Common_if.cpp/02022/=[CarPlayService_SetSupportedStatus] cpsrv_supported_status:2\r\n<br><br><b>pas_alsa.log:</b><br>00:00:02.295/675/29/NS_BkupNAND/00268/bkup_process.cpp/01291/=NaviProxy/RD/D_BK_ID_WIFI_FREQ_SW_SETTING/o:0/s:1/h:00 -- -- --/c:0/r:0\r\n<br><br>');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `analysis`
--
ALTER TABLE `analysis`
  ADD PRIMARY KEY (`itemid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
