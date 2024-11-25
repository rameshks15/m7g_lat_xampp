<?php
/* Description: DTCReader class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */

class DTCReader {
    private $traceDir;
    private $TRC_ERR = [];
    private $CAN_DTC = [];
    public $DTC_List = [];
    private $DtcRows = 0;
    private $addMsg;
    private $DTC_Count = 0;
    public function __construct($traceDir) {
        $this->traceDir = $traceDir;
    }

    public function readDTC($dtcFile) {
        if ($this->getDTC($dtcFile)) {
            return "OK";
        }
        return "NF";
    }

    /**
     * @param string $fileName Input file name (TRC_ERR.dat)
     * @return int Returns 1 for success, 0 for failure
     */
    private function getDTC($fileName) {
        $retval = 0;
        $start_t = 0;
        
        // Read and process the trace file
        $filePath = $this->traceDir.$fileName;
        
        if (file_exists($filePath)) {
            //echo "<br>File found: " . $filePath;
            // Read binary file
            $bytes = file_get_contents($filePath);
            $totalBytes = strlen($bytes);
            
            // Initialize arrays
            $rowCount = floor($totalBytes / 16);
            $this->TRC_ERR = array_fill(0, $rowCount, array_fill(0, 16, ''));
            $this->CAN_DTC = array_fill(0, $rowCount, array_fill(0, 8, ''));
            
            // Read hex-data in blocks of 16 bytes
            $row = 0;
            $col = 0;
            $timeStr = '';
            
            for ($i = 0; $i < $totalBytes; $i++) {
                $element = ord($bytes[$i]);
                $byteStr = sprintf("%02X", $element);
                
                if ($col <= 2) {
                    $timeStr .= $byteStr;
                    if ($col == 2) {
                        $this->TRC_ERR[$row][2] = $timeStr;
                    }
                } else {
                    $timeStr = "";
                    $this->TRC_ERR[$row][$col] = $byteStr;
                }
                
                $col++;
                if ($col % 16 == 0) {
                    $row++;
                    $col = 0;
                }
            }
            $this->DtcRows = $row;
            // Output the TRC list
            //$this->LogTRC($this->TRC_ERR, $this->DtcRows);            
            
            // Read GPS info and adjust time
            $blank_time = strtotime("1970-01-01 00:00:00");
            $start_sys = 0;
            $summer_time = "-";
            
            for ($elm = 0; $elm < count($this->TRC_ERR) - 1; $elm++) {
                $errCode = $this->TRC_ERR[$elm][3] . $this->TRC_ERR[$elm][4] . $this->TRC_ERR[$elm][5];
                $last9 = implode("", array_slice($this->TRC_ERR[$elm], 7, 9));                
                // GPS-Time processing
                if ($this->TRC_ERR[$elm][3] == "F0" && $last9 !== "000000000000000000") {
                    $stString = sprintf("%s/%s/%s %s:%s:%s",
                        $this->TRC_ERR[$elm][5],
                        $this->TRC_ERR[$elm][6],
                        $this->TRC_ERR[$elm][4],
                        $this->TRC_ERR[$elm][7],
                        $this->TRC_ERR[$elm][8],
                        $this->TRC_ERR[$elm][9]
                    );
                    
                    $start_time = strtotime($stString);
                    
                    // Time zone adjustments
                    $zone_high = $this->TRC_ERR[$elm][10];
                    $zone_low = $this->Hex2UInt($this->TRC_ERR[$elm][11]);
                    $zone_sign = $this->Hex2UInt(substr($zone_high, 0, 1));
                    $zone_hour = $this->Hex2UInt(substr($zone_high, -1));
                    $zone_min = ($zone_low & 252) >> 2;
                    
                    if ($zone_hour > 0 || $zone_min > 0) {
                        $minutes = ($zone_sign == 8) ? 
                            ($zone_min + $zone_hour * 60) : 
                            (-$zone_min - $zone_hour * 60);
                        $start_time = strtotime(sprintf("%+d minutes", $minutes), $start_time);
                    }

                    // Adjustment time processing
                    $adj_high = $this->TRC_ERR[$elm][12];
                    $adj_low = $this->Hex2UInt($this->TRC_ERR[$elm][13]);
                    $adj_sign = $this->Hex2UInt(substr($adj_high, 0, 1));
                    $adj_hour = $this->Hex2UInt(substr($adj_high, -1));
                    $adj_min = ($adj_low & 252) >> 2;
                    
                    if ($adj_hour > 0 || $adj_min > 0) {
                        $minutes = ($adj_sign == 8) ? 
                            ($adj_min + $adj_hour * 60) : 
                            (-$adj_min - $adj_hour * 60);
                        $start_time = strtotime(sprintf("%+d minutes", $minutes), $start_time);
                    }

                    $start_sys = 50 * $this->Hex2UInt($this->TRC_ERR[$elm][2]);
                    $start_month = $this->TRC_ERR[$elm][5];
                    
                    if ($start_month >= 1 && $start_month <= 12) {
                        $st_nib = $this->Hex2UInt(substr($this->TRC_ERR[$elm][11], -1));
                        $summer_time = $st_nib & 1;
                        $start_t = 1;
                    }
                } elseif ($errCode == "000000") {
                    $start_t = 0;
                    $start_sys = 0;
                    $summer_time = "-";
                }

                // Process current time
                $current_sys = 50 * $this->Hex2UInt($this->TRC_ERR[$elm][2]);
                $current_ms = $current_sys % 1000;
                $offset_sec = floor(($current_sys - $start_sys) / 1000);
                
                if ($start_t > 0) {
                    $current_time = $start_time + $offset_sec;
                    $this->CAN_DTC[$elm][0] = date('n/j/Y', $current_time);
                    $this->CAN_DTC[$elm][1] = date('H:i:s', $current_time);
                    $this->CAN_DTC[$elm][2] = $current_ms;
                    $this->CAN_DTC[$elm][3] = $summer_time;
                } else {
                    $current_time = $blank_time + $offset_sec;
                    $this->CAN_DTC[$elm][0] = "0/0/0000";
                    $this->CAN_DTC[$elm][1] = date('H:i:s', $current_time);
                    $this->CAN_DTC[$elm][2] = $current_ms;
                    $this->CAN_DTC[$elm][3] = $summer_time;
                }

            }

            // Output the TRC list
            //$this->LogTRC($this->TRC_ERR, $this->DtcRows);
            //
            // Process DTC info
            //$dtcFile = "DTC_List.csv";
            $dtcFile = "dtc_lookup.csv";
            $retval = $this->processDTCFile($dtcFile);
            //

        } else {
            //$this->logInfoDetails("DTC", "File not found: " . $fileName);
            echo "<br>File not found: " . $filePath;
        }
        
        //$this->addMsg = "View more on related tabs";
        return $retval;
    }

    private function processDTCFile($dtcFile) {
        $retval = 0;
        if (file_exists($dtcFile)) {
            try {
                // Read CSV file
                $dtcLines = [];
                if (($handle = fopen($dtcFile, "r")) !== FALSE) {
                    // Skip header if exists
                    fgetcsv($handle);
                    
                    // Read all DTC definitions into array
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        $dtcLines[] = $data;
                    }
                    fclose($handle);

                    // Process each TRC_ERR entry
                    for ($elm = 0; $elm < count($this->TRC_ERR) - 1; $elm++) {
                        $this->processDTCEntry($elm, $dtcLines);
                    }
                }
                
                // Output the CAN-DTC list
                //$this->LogDTC($this->CAN_DTC, $elm);
                //$this->LogDTC($this->DTC_List, $this->DTC_Count);
                $retval = 1;
            } catch (Exception $e) {
                $this->logInfoDetails("DTC", "Error reading DTC file: " . $e->getMessage());
            }
        } else {
            $this->logInfoDetails("DTC", "File not found: " . $dtcFile);
        }
        return $retval;
    }

    private function processDTCEntry($elm, $dtcLines) {
        $errCode = $this->TRC_ERR[$elm][3] . 
                   $this->TRC_ERR[$elm][4] . 
                   $this->TRC_ERR[$elm][5];
        $last9 = implode(" ", array_slice($this->TRC_ERR[$elm], 7, 9));
        $last10 = $this->TRC_ERR[$elm][6] . " " . $last9;

        if ($errCode == "F00000") {
            $this->setDTCValues($elm, $errCode, "- GPS -", "N/A", "N/A");
        } elseif ($errCode == "F10000" && $last10 == "00 00 00 00 00 00 00 00 00 00") {
            $this->setDTCValues($elm, $errCode, "Rec-Stop", "N/A", "N/A");
        } elseif ($errCode == "F20000" && $last10 == "00 00 00 00 00 00 00 00 00 00") {
            $this->setDTCValues($elm, $errCode, "Rec-Start", "N/A", "N/A");
        } elseif ($errCode != "000000" && $last9 == "00 00 00 00 00 00 00 00 00") {
            $this->processMatchingDTC($elm, $errCode, $dtcLines);
        } else {
            $this->processOtherCases($elm, $errCode);
        }
    }

    private function processMatchingDTC($elm, $errCode, $dtcLines) {

        foreach ($dtcLines as $dtc) {
            if (trim($dtc[0]) === $errCode) {
                $this->CAN_DTC[$elm][4] = $errCode;
                $this->CAN_DTC[$elm][5] = trim($dtc[1]); // DTC_Code
                $this->CAN_DTC[$elm][6] = ($this->TRC_ERR[$elm][6] == 1) ? "Rec" : "Del";
                $this->CAN_DTC[$elm][7] = trim($dtc[2]); // Description
                // Add to DTC_List
                $this->DTC_List[$this->DTC_Count++] = $dtc[0];
                $this->DTC_List[$this->DTC_Count++] = $dtc[1];
                $this->DTC_List[$this->DTC_Count++] = $dtc[2];
                break;
            }
        }
    }

    private function processOtherCases($elm, $errCode) {
        if ($this->TRC_ERR[$elm][3] == "F0") {
            $this->setDTCValues($elm, $errCode, "- GPS -", "N/A", "N/A");
        } elseif ($errCode == "000000") {
            $this->setDTCValues($elm, $errCode, "-SLEEP-", "N/A", "N/A");
        } else {
            $this->setDTCValues($elm, $errCode, "-OTHER-", "N/A", "N/A");
        }
    }

    private function Hex2UInt($hex) {
        $value = hexdec($hex);
        if ($value < 0) {
            $value = hexdec('1' . $hex) - 4294967296;
        }
        return $value;
    }

    private function setDTCValues($elm, $val4, $val5, $val6, $val7) {
        $this->CAN_DTC[$elm][4] = $val4;
        $this->CAN_DTC[$elm][5] = $val5;
        $this->CAN_DTC[$elm][6] = $val6;
        $this->CAN_DTC[$elm][7] = $val7;
    }

    // These methods need to be implemented based on your requirements
    private function LogTRC($trc_err, $rows) {
        echo "<pre>\n";
        echo "TRC Error Log:\n";
        echo str_repeat("-", 80) . "\n";
        echo "Row\t| Time\t| Error Code\t| Data\n";
        echo str_repeat("-", 80) . "\n";
        
        for ($i = 0; $i < $rows; $i++) {
            echo sprintf(
                "%d\t| %s\t| %s%s%s\t| %s %s %s %s %s %s %s %s %s %s\n",
                $i,
                $trc_err[$i][2],
                $trc_err[$i][3],
                $trc_err[$i][4],
                $trc_err[$i][5],
                $trc_err[$i][6],
                $trc_err[$i][7],
                $trc_err[$i][8],
                $trc_err[$i][9],
                $trc_err[$i][10],
                $trc_err[$i][11],
                $trc_err[$i][12],
                $trc_err[$i][13],
                $trc_err[$i][14],
                $trc_err[$i][15]
            );
        }
        
        echo str_repeat("-", 80) . "\n";
        echo "</pre>\n";
    }

    private function LogDTC($can_dtc, $rows) {
        echo "<pre>\n";
        echo "CAN-DTC-Log:\n";
        echo str_repeat("-", 120) . "\n";
        echo sprintf(
            "%-4s | %-10s | %-8s | %-5s | %-3s | %-6s | %-6s | %-3s | %-30s\n",
            "Row", "Date", "Time", "ms", "DST", "Error", "Code", "Sts", "Description"
        );
        echo str_repeat("-", 120) . "\n";
        
        for ($i = 0; $i < $rows; $i++) {
            echo sprintf(
                "%-4d | %-10s | %-8s | %-5s | %-3s | %-6s | %-6s | %-3s | %-30s\n",
                $i,
                $can_dtc[$i][0],  // Date
                $can_dtc[$i][1],  // Time
                $can_dtc[$i][2],  // Milliseconds
                $can_dtc[$i][3],  // Summer time
                $can_dtc[$i][4],  // Error code
                $can_dtc[$i][5],  // DTC code
                $can_dtc[$i][6],  // Status
                $can_dtc[$i][7]   // Description
            );
        }
        
        echo str_repeat("-", 120) . "\n";
        echo "</pre>\n";
    }

    private function logInfoDetails($type, $message) {
        // Implement logging info details
    }
}
?> 