<?php
/* Description: LogAnalyzer class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */

class LogAnalyzer {
    private $tags;
    private $My_JE;
    private $Res_JE;
    private $Log_EV;
    private $paslog;
    private $WCP_AAW_Err; 
    private $DRAMIntegrity_Err;

    public function analyzeLog($paslog, $tags, $logvin) {
        $retval = 0;
        $this->tags = $tags;
        $this->paslog = $paslog;

        // Decode VIN
        $this->My_JE = $this->decodeVIN($logvin);

        // Find and check for specific errors in PAS log directory
        $retval = $this->findAndCheckErrors();
        
        if(!($this->WCP_AAW_Err || $this->DRAMIntegrity_Err)){
            $this->Res_JE = "No-issue found";
            $this->Log_EV = "";
        }
        return [
            'retval' => $retval,
            'My_JE' => $this->My_JE,
            'Res_JE' => $this->Res_JE,
            'Log_EV' => $this->Log_EV
        ];
    }

    /**
     * Find and check for specific errors in PAS log directory
     * @return int Returns 1 if successful, 0 if error
     */
    private function findAndCheckErrors() {
        $retval = 1;
        $this->WCP_AAW_Err = 0;
        $this->DRAMIntegrity_Err = 0;
        if ($handle = opendir($this->paslog)) {
            while (false !== ($entry = readdir($handle))) {
                // Check for WCP_AAW errors
                if ((!$this->WCP_AAW_Err) && str_contains($entry, "_UMOFF")) {
                    $errDir = "{$this->paslog}/$entry";
                    if ($this->Check_WCP_AAW_Err($errDir)) {
                        $this->WCP_AAW_Err = 1;
                        $this->Res_JE = "Software issue, WCP_AAW_Err";
                    } else {
                        $this->Log_EV .= "Check_WCP_AAW_Err was NG<br>"; 
                    }        
                }

                // Check for DRAM Integrity errors
                if ((!$this->DRAMIntegrity_Err) && (str_contains($entry, "_ERR") || str_contains($entry, "_ILG_RESET"))) {
                    $errDir = "{$this->paslog}/$entry";
                    if ($this->Check_DRAMIntegrity_Err($errDir)) {
                        $this->DRAMIntegrity_Err = 1;
                        $this->Res_JE = "Hardware issue, DRAMIntegrity_Err";
                    } else {
                        $this->Log_EV .= "Check_DRAMIntegrity_Err was NG<br>"; 
                    }
                }
            }
            closedir($handle);
        } else {
            $this->error .= "could not open paslog={$this->paslog}";
            $retval = 0;
        }
        return $retval;
    }

    function Check_DRAMIntegrity_Err($errDir){
        $retval = 0; $errFound = ""; $errCheck1 = "";
        $errFile = $errDir."/kernel.log"; //echo "<br>errFile=$errFile";
        if ($file = fopen($errFile, "r")) {
			$text1 = "[Secure](0x3)/usr/pana/bin";
			while(!feof($file)) {
				$line = fgets($file); //echo "<br>line-all=$line";
				if (str_contains($line, $text1)) {	//echo "<br>line1=$line";
					$errFound = 1;
					break;
				}
			} fclose($file);
		}
		if($errFound){
			$errFile = $errDir."/paslog/pas_systemdata.log";
			if ($file = fopen($errFile, "r")) {
				$text2 = "MessageStr,[Secure]bg ROM checkIntegrity error";
				while(!feof($file)) {
					$line = fgets($file);
					if (str_contains($line, $text2)) { //echo "<br>line2=$line";
						$errCheck1 = 1;
						break;
					}	
				} fclose($file);
			}
		}
		if($errFound){
			$errFile = $errDir."/paslog/pas_debug_base_pf.log";
			if ($file = fopen($errFile, "r")) {
				$text3 = "bg ROM checkIntegrity error";
				while(!feof($file)) {
					$line = fgets($file);
					if (str_contains($line, $text3)) { //echo "<br>line3=$line";
						$errCheck2 = 1;
						break;
					}	
				} fclose($file);
			}
		}
		if ($errCheck1 && $errCheck2) { 
			$retval = 1;
		}
		return $retval;
	}

    private function Check_WCP_AAW_Err($errDir) {
        $retval = 0; $err1 = 0; $err2 = 0; $err3 = 0;
        $errFile = $errDir."/paslog/pas_systemdata.log";
        if ($file = fopen($errFile, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                if (str_contains($line, "VP_CMU_CARPLAY=available_wireless")) {	
                    $err1 = 1; $this->Log_EV .= "<b>pas_systemdata.log:</b><br>".$line."<br><br>"; 
                    break;
                }
            } fclose($file);
        }
        $errFile = $errDir."/paslog/pas_debug_connect_func.log";
        if ($file = fopen($errFile, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                if (str_contains($line, "[CarPlayService_SetSupportedStatus] cpsrv_supported_status:")) {	
                    $err2 = 1; $this->Log_EV .= "<b>pas_debug_connect_func.log:</b><br>".$line."<br><br>";
                    break;
                }
            } fclose($file);
        }
        $errFile = $errDir."/paslog/pas_alsa.log";
        if ($file = fopen($errFile, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                if (str_contains($line, "D_BK_ID_WIFI_FREQ_SW_SETTING")) {	
                    $err3 = 1; $this->Log_EV .= "<b>pas_alsa.log:</b><br>".$line."<br><br>";
                    break;								
                }
            } fclose($file);
        }
        if($err1 && $err2 && $err3){ 
            $retval = 1;
        }
		return $retval;
    }

    private function decodeVIN($vin) {
		$modelCode = substr($vin, 3, 2); // 4th and 5th characters
		$yearCode = $vin[9]; // 10th character
		// Load lookup tables
		$modelLookup = $this->loadLookupTable('model_lookup.txt');
		$yearLookup = $this->loadLookupTable('year_lookup.txt');
		// Get the model and year
		$model = $modelLookup[$modelCode] ?? 'Unknown Model';
		$modelYear = $yearLookup[$yearCode] ?? 'Unknown Year';
		return "$model, $modelYear";
    }
	function loadLookupTable($filename) {
		$table = [];
		$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			list($key, $value) = explode('|', $line);
			$table[trim($key)] = trim($value);
		}
		return $table;
	}

}

?>
