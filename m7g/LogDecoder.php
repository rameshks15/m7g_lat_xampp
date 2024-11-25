<?php
/* Description: LogDecoder class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */

class LogDecoder {
    private $datadir;
    private $paslog;
    private $trclog;
    private $error;
    private $retmsg;
    private $Ver_JE;
    private $Log_VIN;
    private $CMU_Part;
    private $CMU_Pack;
    private $RT_Part;
    private $RT_Pack;
    private $AMP_Part;
    private $AMP_Pack;

    public function __construct($datadir) {
        $this->datadir = $datadir;
    }

    public function decodeLog($fold) {
        $retval = "decode-error";
        $dir = $this->datadir.$fold; 
        //$paslog = ""; $trclog = "";
        
        // Find paslog directory
        $it = new RecursiveDirectoryIterator($dir);
        foreach(new RecursiveIteratorIterator($it) as $file) {
            if (str_contains($file, "TRC_PANA")) { 
                $this->trclog = current(explode("TRC_PANA", $file));
                $this->paslog = $this->trclog."TRC_PANA/dcu/paslog";
                $_SESSION['paslog'] = $this->paslog;
                $_SESSION['trclog'] = $this->trclog;
                break; 
            }
        }

        if ($handle = opendir($this->paslog)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != "debug" && $entry != "ExtendedLog") {
                    $this->untarFile($this->paslog, $entry);
                }
            } closedir($handle); 
            if($this->get_version($this->paslog)) { 
                $retval = "decode-ok";
                $this->retmsg = "decode-ok";
            } else { 
                $this->error = "version info was not found"; 
            }
        } else { 
            $this->retmsg = "decode-error";
            $this->error = "paslog folder was not found"; 
        }

        return [
            'retval' => $retval,
            'trclog' => $this->trclog,
            'paslog' => $this->paslog,
            'error' => $this->error,
            'retmsg' => $this->retmsg,
            'Ver_JE' => $this->Ver_JE
        ];
    }

    // You'll need to move these dependent methods as well
    private function untarFile($path, $log) {
        // Move the untarFile implementation here
        $part = explode(".", $log); //echo "log=$log";
		$fold = $part[0]; $tar = $part[0].".".$part[1]; 
		if($part[2]=="gz"){
			$gzf = $path."/".$log; $tarf = $path."/".$tar; $dest = $path."/".$fold;
			$p = new PharData($gzf);
			$p->decompress(); // creates tar file
			$phar = new PharData($tarf);
			$phar->extractTo($dest);
			unlink($tarf); unlink($gzf); // deletes tar & gz files
		}
    }

    private function get_version($dir) {
        // Move the get_version implementation here
        $retval = 0; $verFile = ""; $partFile = ""; 
		$files = scandir($dir, SCANDIR_SORT_DESCENDING);
		// Remove the current (.) and parent (..) directory references
		$files = array_diff($files, array('.', '..'));
		//
		foreach($files as $file) { //echo "$file<br>";
			if (($verFile=="") && (str_contains($file, "_DIAG") || str_contains($file, "_UMOFF"))) {
				$verFile = $dir."/$file/paslog/pas_systemdata.log";
				if (!file_exists($verFile)){ $verFile = ""; }
			}
			if (($partFile=="") && (str_contains($file, "_DIAG") || str_contains($file, "_UMOFF"))) {
				$partFile = $dir."/$file/paslog/pas_diag_info.log"; 
				if (!file_exists($partFile)){ $partFile = ""; }
			}
			if ($verFile && $partFile) { //echo "verFile=$verFile,partFile=$partFile";
				$this->getVerInfo($verFile);
				$this->getPartInfo($partFile);
				$this->Ver_JE = "Part# $this->CMU_Part, Ver# $this->CMU_Pack;
								 Part# $this->RT_Part, Ver# $this->RT_Pack;
								 Part# $this->AMP_Part, Ver# $this->AMP_Pack";
				$retval = 1; break;
			}
        }
        return $retval;
    }
    function getVerInfo($verFile){
		if ($file = fopen($verFile, "r")) {
			while(!feof($file)) {
				$line = fgets($file);
				if (str_contains($line, "VP_VEHICLE_VIN")) {
					$keys = explode("=",$line);
					$this->Log_VIN = trim(end($keys));
					$_SESSION['logvin'] = $this->Log_VIN; //echo ",V1=".$this->Log_VIN;
				} if (str_contains($line, "PACKAGE_VERSION")) {
					$keys = preg_split('/\s+/', trim($line));
					$this->CMU_Pack = trim(end($keys)); //echo ",V2=".$this->CMU_Pack;
				} if (str_contains($line, "VP_PANA_maker_ID")) {
					$keys = explode("=",$line);
					$this->CMU_Part = trim(end($keys));
				}
				if($this->Log_VIN && $this->CMU_Pack && $this->CMU_Part){ break; }
			} fclose($file);
			if (isset($_SESSION['logvin'])) {
				if($_SESSION['logvin'] == $_POST['vin']) { 
					$retval = 1; $this->retmsg = 'decode-ok';
				} else { 
					$this->retmsg = 'vin-mismatch'; $this->error = "V I N = $_POST[vin] is not correct";
				}
			} else {
				$this->retmsg = "decode-error";
				$this->error = "VIN was not found in the log";
			}
		}
	}	
	function getPartInfo($partFile){
		if ($file = fopen($partFile, "r")) {
			while(!feof($file)) {
				$line = fgets($file);
				if (str_contains($line, "Title                  : RT")) {
					$line = fgets($file);$line = fgets($file);$line = fgets($file);
					if(str_contains($line, "Package / ")) {
						$keys = preg_split('/\s+/', trim($line));
						$this->RT_Pack = trim(end($keys));	
					}
					$line = fgets($file);$line = fgets($file);$line = fgets($file);$line = fgets($file);
					$line = fgets($file);$line = fgets($file);$line = fgets($file);$line = fgets($file);
					if(str_contains($line, "Part No.               : ")) {
						$keys = preg_split('/\s+/', trim($line));
						$this->RT_Part = trim(end($keys));	
					}
					//$this->Ver_RT = "2222222222";
				} if (str_contains($line, "Title                  : AMP")) {
					$line = fgets($file);$line = fgets($file);$line = fgets($file);
					if(str_contains($line, "Package / ")) {
						$keys = preg_split('/\s+/', trim($line));
						$this->AMP_Pack = trim(end($keys));	
					}
					$line = fgets($file);$line = fgets($file);$line = fgets($file);
					$line = fgets($file);$line = fgets($file);
					if(str_contains($line, "Part No.               : ")) {
						$keys = preg_split('/\s+/', trim($line));
						$this->AMP_Part = trim(end($keys));	
					}
					//$this->Ver_AMP = "3333333";
					//echo "AMP<br>";
				}
				if(($this->RT_Part) && ($this->AMP_Part)){ break; }
			} fclose($file);
		}
	}

    // Getters for private properties
    public function getError() {
        return $this->error;
    }

    public function getRetMsg() {
        return $this->retmsg;
    }

    public function getPaslog() {
        return $this->paslog;
    }
}
?>