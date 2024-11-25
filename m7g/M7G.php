<?php
/* Description: Main class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/System.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/LogDecoder.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/DTCReader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/LogAnalyzer.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/DBSession.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/profile/PHPMailer.php');

class M7G extends System 
{
    var $pid="", $mid=""; var $dbconn=""; 
	var $homeurl="", $datadir="", $trclog="", $paslog="";
	var $retmsg = "", $error="", $tags="",
	$CMU_Part="", $CMU_Pack="", $RT_Part="", $RT_Pack="", $AMP_Part="", $AMP_Pack="",  
	$Log_VIN="", $Ver_RT="", $Ver_AMP="", $Ver_JE="", $My_JE="", $Res_JE="", $Log_EV=""; 
	var $acause=""; var $agroup=""; 
	
	function __construct() 
	{
		$this->datadir="$_SERVER[DOCUMENT_ROOT]/data/m7g/";

		// DB connection using mysqli
		$settings = System::getSettings();
		
		try {
			// Check if MySQL service is running
			$socket = @fsockopen($settings['dbhost'], 3306, $errno, $errstr, 5);
			if (!$socket) {
				die('<p>Error: MySQL server is not running. Please start MySQL service and try again.<br>
					 Details: ' . $errstr . ' (Error ' . $errno . ')</p>');
			} @fclose($socket);

			// Attempt database connection
			if (($settings['dbhost'] == "localhost") || ($settings['dbhost'] == "host.docker.internal")) {
				$this->dbconn = new mysqli(
					$settings['dbhost'], 
					$settings['dbuser'], 
					$settings['dbpass'], 
					$settings['dbname'],
					3306  // explicitly specify port
				);
			} else {
				$this->dbconn = mysqli_init();
				mysqli_ssl_set($this->dbconn, NULL, NULL, "/app/public/DigiCertGlobalRootCA.crt.pem", NULL, NULL);
				mysqli_real_connect($this->dbconn, $settings['dbhost'], $settings['dbuser'], $settings['dbpass'], $settings['dbname'], 3306, MYSQLI_CLIENT_SSL);
			}

			if ($this->dbconn->connect_error) {
				throw new Exception('Database Connection Error: ' . $this->dbconn->connect_error);
			}

		} catch (Exception $e) {
			die('<p>Failed to connect to MySQL:<br>
				 1. Please verify MySQL service is running<br>
				 2. Check database credentials in settings<br>
				 3. Ensure database exists<br><br>
				 Error Details: ' . $e->getMessage() . '</p>');
		}

		// session persistance with DB
		if (session_status() == PHP_SESSION_NONE) {
			$handler = new DBSession($this->dbconn);
			session_set_save_handler($handler, true);
			session_start();
		}
    }

	function __destruct() 
	{
        mysqli_close($this->dbconn);
    }

	function exeLogin() 
	{
		$email = $_POST['email'];
		$password = $_POST['password'];
		$sql = "SELECT * FROM users WHERE email = '$email'";
		$result = $this->dbconn->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc(); // Fetch user data
				if (password_verify($password, $row['password'])) { // Successful login
				$_SESSION['userid'] = $row['userid'];
				$_SESSION['username'] = $row['username'];
				$_SESSION['email'] = $row['email'];
				$_SESSION['role'] = $row['role'];
				$_SESSION['loggedin'] = true;
				// Redirect to the last page if it exists, or home.php
				if (isset($_SESSION['last_page'])) {
					$redirect_url = $_SESSION['last_page']; //echo "redirect_url=".$redirect_url;
					unset($_SESSION['last_page']);  // Unset last page after use
					header("Location: " . $redirect_url);
				} else { header("Location: index.php"); }
			} else {
				$_SESSION['login_error'] = "Invalid email or password. Please try again.";
				echo $_SESSION['login_error'];
				header("Location: login.php");
			}
		} else {
			$_SESSION['login_error'] = "No user found with the email = $email";
			echo $_SESSION['login_error'];
			header("Location: login.php");
		}
		exit();
	}

	function exeRegister() 
	{
		$userid =date('ymdHis');
		$username = $_POST['username'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$sql = "SELECT * FROM users WHERE email = ?";
		$stmt = $this->dbconn->prepare($sql);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) { // Registarion failed, email exists
			echo "This email is already registered!";
			$_SESSION['register_error'] = "This email is already registered. Please use a different email.";
			header("Location: login.php");
		} elseif(strlen($password) < 6) { // Registration failed - password is too short
			$_SESSION['register_error'] = "Password must be at least 6 characters long.";
			header("Location: login.php"); 
		} else {    // Insert the new user into the database
			// Hash password before storing it
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			$role = "member"; $setting = "{\"company\":\"pasa\",\"branch\":\"ptc\",\"group\":\"mazda\"}";
			$sql = "INSERT INTO users (userid, username, email, password, role, setting) VALUES (?, ?, ?, ?, ?, ?)";
			$stmt = $this->dbconn->prepare($sql);
			$stmt->bind_param('ssssss', $userid, $username, $email, $hashed_password, $role, $setting);
			if ($stmt->execute()) { //echo "Registration successful!";
				header("Location: login.php");
			} else {
				echo "Error: " . $stmt->error;
			}
		}
		exit();
		$stmt->close();    
	}

	function exeClaim() {
		$table = "analysis";
		$retval = 0; $info = "";
		$tags = ""; $mt = microtime(true);
		$opCode = htmlspecialchars($_POST['opCode']); 
		if(isset($_POST['setData'])){
			$setJson = $_POST['setData']; 
			$setArray = json_decode($setJson, true);
			if (is_array($setArray)) {
				foreach ($setArray as $item) {
					$tags = $tags.htmlspecialchars($item).",";
				} $this->tags = substr($tags, 0, -1);
			} else { echo "Failed to decode set data."; }
		} 
		$desc = htmlspecialchars($_POST['desc']); 
		$vin = htmlspecialchars($_POST['vin']);
		$date = isset($_POST['date']) ? $_POST['date'] : '';
		$time = isset($_POST['time']) ? $_POST['time'] : '';
		$dealer_number = isset($_POST['dealer_number']) ? $_POST['dealer_number'] : '';

		if($opCode == "newClaim") {
			$itemid=date('YmdHis'); $fid = $itemid; $_SESSION['itemid'] = $itemid;
			if($this->fileUp($fid)){ //uploaded 
				$info = "received at ".round((microtime(true))-$mt,1)."s";
				if($this->unzipLog($fid,$fold)){ // unzipped
					$info .= ", unzipped at ".round((microtime(true))-$mt,1)."s";
					if(($this->decodeLog($fold))=="decode-ok"){ // decoded
						$info .= ", decoded at ".round((microtime(true))-$mt,1)."s";
						if ($this->analyzeLog($this->paslog)) {
							$info .= ", analyzed at " . round(microtime(true) - $mt, 1) . "s";
							//$this->retmsg = "log-analyzed-more to be added 2";
							$status = 'analyzed'; $retval = 1; 
						} else { $status = 'not-analyzed'; }
					} else { 
						//$status = 'vin-mismatch'; 
						$status = $this->retmsg;
					}
					//echo "Ver_JE=".$this->Ver_JE;
					$query = "INSERT INTO $table (`itemid`,`tag`,`date`,`time`,`desc`,`vin`,`dealer_number`,`status`,`email`,`version`,`result`,`model`,`detail`) 
					VALUES ('$itemid','$this->tags','$date','$time','$desc','$_SESSION[logvin]','$dealer_number','$status','$_SESSION[email]','$this->Ver_JE','$this->Res_JE','$this->My_JE','$this->Log_EV');";
					try { $ret = mysqli_query($this->dbconn,$query);
					} catch (mysqli_sql_exception $e) { echo "DB-Insert: ".$e->getMessage(); } 
					//echo $query.$_SESSION['email'];			
				} //else { $this->error = "log-unzipped-error"; }
			} //else { $this->error = "log-uploaded-error"; }
		} else if($opCode == "newVin") {
			if ($_SESSION['logvin'] == $vin) {
				if ($this->analyzeLog($this->paslog)) {
					$retval = 1; $this->retmsg = "log-analyzed-more to be added 2"; $status = "analyzed";
					$stmt = $this->dbconn->prepare("UPDATE $table SET `status` = ? WHERE `itemid` = ?");
					$stmt->bind_param("ss", $status, $_SESSION['itemid']);
					try { $stmt->execute(); } catch (mysqli_sql_exception $e) {
						$this->error .= "DB-Update: " . $e->getMessage(); }
					$stmt->close();
				}
			} else {
				$this->retmsg = "vin-mismatch"; $this->error = "V I N = ".$vin." is not correct";
			}
		} else { $this->retmsg = "invalid-opcode"; }
		if($status == "analyzed") {
			$table = "analysis";
			$query = "SELECT * FROM `$table` "."WHERE `itemid`=".$itemid; //echo $query;
			if($irs = mysqli_query($this->dbconn,$query)){
				$record = $irs->fetch_assoc();
				//echo json_encode($record);
				$this->retmsg = $record;
			} else {
				echo json_encode([]);	// send an empty array
			}	
		}
		echo json_encode(['success'=>$retval,'content'=>$this->retmsg,'info'=>$info,'error'=>$this->error]);
	}

	function fetchList() {
		$table = "analysis";
		$cond = "ORDER BY itemid DESC, tag DESC";
		$query = "SELECT * FROM `$table` ".$cond; //echo $query;
		if($irs = mysqli_query($this->dbconn,$query)){
			$data = array();	// store rows in associative array
			while($row = $irs->fetch_assoc()) { $data[] = $row; }	
			echo json_encode($data);
		} else {
			echo json_encode([]);	// send an empty array
		}
	}
	function dealer_fetchTags() {
		$result = $this->dbconn->query("SHOW TABLES LIKE 'dealertag'");
		if ($result->num_rows == 0) {	// create table
			$sql = "CREATE TABLE `dealertag` (`tagid` char(14) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
			  `tagname` varchar(128) NOT NULL, `email` varchar(64) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			if ($this->dbconn->query($sql) === FALSE) { echo "<br>Error creating table:".$mysqli->error; }
		}
		$query = "SELECT * FROM dealertag";
		$irs = mysqli_query($this->dbconn,$query);
		while ($ir = mysqli_fetch_array($irs)){
			echo $ir['dealer_num']."<br>";
		}
	}

	function fetchTags() {
		$result = $this->dbconn->query("SHOW TABLES LIKE 'issuetag'");
		if ($result->num_rows == 0) {	// create table
			$sql = "CREATE TABLE `issuetag` (`tagid` char(14) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
			  `tagname` varchar(128) NOT NULL, `email` varchar(64) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			if ($this->dbconn->query($sql) === FALSE) { echo "<br>Error creating table:".$mysqli->error; }
		}
		$query = "SELECT * FROM issuetag";
		$irs = mysqli_query($this->dbconn,$query);
		while ($ir = mysqli_fetch_array($irs)){
			echo $ir['tagname']."<br>"; 		
		}
	}
	function storeTag() {
		$data = json_decode(file_get_contents('php://input'));
		if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
			echo "Invalid JSON data!";
		} else {
			$table = "issuetag";			
			$tagname = $data->{'newTag'};
			$itemid = date('YmdHis'); 
			$email = $_SESSION['email']; //$updated = date('Y-m-d H:i:s'); 
			$query = "INSERT INTO $table (`tagid`,`tagname`,`email`) 
						VALUES ('$itemid','$tagname','$email');";
			$result = mysqli_query($this->dbconn,$query);
		} 
	}

	function fetchItem($itemid) {
		$table = "analysis";
		$query = "SELECT * FROM `$table` "."WHERE `itemid`=".$itemid; //echo $query;
		if($irs = mysqli_query($this->dbconn,$query)){
			$record = $irs->fetch_assoc();
			echo json_encode($record);
			//$this->retmsg = $record;
		} else {
			echo json_encode([]);	// send an empty array
		}
	}
	function fetchDetail($itemid) {
		$table = "analysis";
		$query = "SELECT detail FROM `$table` WHERE `itemid` = ?";
		
		$stmt = $this->dbconn->prepare($query);
		$stmt->bind_param("s", $itemid);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($row = $result->fetch_assoc()) {
			echo $row['detail'];
		} else {
			echo "No details found for this item.";
		}
		
		$stmt->close();
	}

	function fileUp(&$fid){
		$retval=0; $path = $this->datadir;
		if (!is_dir($path)) { if (!(mkdir($path, 0777, true))) {echo "Failed to create data folder"; } }
		if ($_FILES["upfile"]["error"] === UPLOAD_ERR_OK) {
			$fn = explode(".", ($_FILES["upfile"]["name"])); $fextn = end($fn);
			$fs = explode(".", ($_FILES["upfile"]["size"])); $fsize = end($fs);
			switch($fextn){
				case "zip":
					if($fsize < (520*1024*1024)) { // acceptable size < 0.5GB
						$fid = $fid.".".$fextn;
						if (move_uploaded_file($_FILES["upfile"]["tmp_name"], $path.$fid)) {
							$retval = "1";
						} else {
							echo "FILE-MOVE-ERROR ".$_FILES["upfile"]["error"]."PATH-ID=".$path.$fid;
						} sleep(1);
					} else { echo "File is over-size, upload smaller one -> ".$fsize; }
					break;
				default:
					echo "Check-file-type: .".$fextn;
					break;
			}
		} return ($retval);
	}
	function unzipLog($fid,&$fold){
		$retval = 0;
		$fold = current(explode(".", $fid));
		$file = $this->datadir.$fid;
		$path = $this->datadir.$fold;
		$zip = new ZipArchive;
		$res = $zip->open($file);
		if ($res === TRUE) {
			$zip->extractTo($path);
			$zip->close(); //unlink($file);
			$retval = 1;
		} //else { echo "Couldn't open $file"; }
		return($retval);
	}
	function decodeLog($fold) {
		$decoder = new LogDecoder($this->datadir);
		$result = $decoder->decodeLog($fold);
		$this->trclog = $result['trclog'];
		$this->paslog = $result['paslog'];
		$this->error = $result['error'];
		$this->retmsg = $result['retmsg'];
		$this->Ver_JE = $result['Ver_JE'];
		return $result['retval'];
	}

	function analyzeLog($paslog) {
		$dtcReader = new DTCReader($this->trclog);
		$result = $dtcReader->readDTC("TRC_ERR.dat");
		$this->DTC_List = $dtcReader->DTC_List;
		// Get the last DTC
		if (!empty($this->DTC_List)) {
			$lastDTC = end($this->DTC_List);
			$this->Log_EV .= $lastDTC;
		}
		
		$analyzer = new LogAnalyzer();
		$result = $analyzer->analyzeLog($paslog, $this->tags, $_SESSION['logvin']);
		if ($result['retval']) {	// test
			$errorDetails = "Analysis returned null result. ";
			$errorDetails .= !empty($this->Log_EV) ? "Event details: " . $this->Log_EV : "No event details available.";
			$this->sendAnalysisFailureEmail($_SESSION['logvin'], $errorDetails);
		}
		$this->My_JE = $result['My_JE'];
		$this->Res_JE = $result['Res_JE'];
		$this->Log_EV .= $result['Log_EV'];
				
		return $result['retval'];
	}

	function sendAnalysisFailureEmail($vin, $errorDetails) {
		$mail = new PHPMailer(true); 
		try {
			// Server settings
			$mail->isSMTP();                                      // Use SMTP
			$mail->Host       = 'smtp.gmail.com';                 // SMTP server
			$mail->SMTPAuth   = true;                             // Enable SMTP authentication
			$mail->Username   = 'rameshks15@gmail.com';           // SMTP username
			$mail->Password   = 'hweg zmvl wgex pnwg';            // SMTP password or App Password (if Gmail/2FA)
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption
			$mail->Port       = 587;                              // TCP port for TLS
			//$mail->SMTPDebug = 2;                               // 2 for verbose debug output
			
			// Recipients
			$mail->setFrom('m7g-analysis@pasa.com', 'M7G Analysis');
			$mail->addAddress('rameshks15@outlook.com');
			$mail->addReplyTo('noreply@pasa.com');

			// Content
			$mail->isHTML(true);
			$mail->Subject = "M7G Analysis Failed - VIN: {$vin}";
			$mail->Body    = "
				<p>Hello,</p>
				<p>The M7G analysis has failed for the following vehicle:</p>
				<p><strong>VIN:</strong> {$vin}</p>
				<p><strong>Error Details:</strong><br>{$errorDetails}</p>
				<p>Please review the analysis and take necessary action.</p>
				<br>
				<p>Best regards,<br>M7G Analysis</p>
			";
			$mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $mail->Body));

			$mail->send();
			return true;

		} catch (Exception $e) {
			error_log("Failed to send analysis failure email: " . $e->getMessage());
			$this->error = "Email notification failed: " . $e->getMessage();
			return false;
		}
	}

}
?>