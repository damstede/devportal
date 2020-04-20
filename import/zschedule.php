<?PHP
	// error_reporting(1); ini_set('display_errors', 1);
	
    header('Content-Type: text/html; charset=utf-8');
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

    $data = array();
	$data["type"] = "error";
	$data["message"] = "Onbekende error";
	$data["data"] = array();
	
	function returnError($msg, $stuff = array()) {
		global $data;
		$data["type"] = "error";
		$data["message"] = $msg;
		$data["data"] = $stuff;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
	}
	
	function returnWarning($msg, $stuff = array()) {
		global $data;
		$data["type"] = "warning";
		$data["message"] = $msg;
		$data["data"] = $stuff;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
	}
	
	function returnData($msg, $stuff = array()) {
		global $data;
		$data["type"] = "success";
		$data["message"] = $msg;
		$data["data"] = $stuff;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
    }

    session_start();

    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        returnError("Je bent niet (meer) ingelogd! Log opnieuw in.");
    }
    else {
		if (isset($_GET["year"]) && !empty($_GET["year"]) && isset($_GET["week"]) && !empty($_GET["week"])) {
			require_once("db.php");
			$damstedeDB = new DamstedeDB();
			$startAndEndDate = $damstedeDB->getStartAndEndDate(intval($_GET["year"]), intval($_GET["week"]));
			
			$ch = curl_init();
			// user=~me voor eigen rooster
			$url = "https://".$_SESSION["zermelo_school"].".zportal.nl/api/v3/appointments/?user=~me&valid=true&start=".strtotime($startAndEndDate[0])."&end=".strtotime($startAndEndDate[1] . " +1 day")."&fields=".implode("%2C", array("id", "appointmentInstance", "groups", "startTimeSlot", "start", "endTimeSlot", "end", "subjects", "teachers", "students", "locations", "type", "valid", "cancelled", "locationChanged", "branchOfSchool"))."&access_token=".$_SESSION["zermelo_access_token"];
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->secure);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($result, true)["response"];
			if ($json["status"] == 200) {
				$lessonCount = $json["totalRows"];
				for ($i = 0; $i < $lessonCount; $i++) {
					$json["data"][$i]["day"] = intval(date("N", $json["data"][$i]["start"])) - 1;
					$json["data"][$i]["students"] = count($json["data"][$i]["students"]);
					if ($json["data"][$i]["students"] < 1) {
						$json["data"][$i]["students"] = $defaultClassSize;
					}
				}
				returnData($lessonCount . " lessen gevonden", $json["data"]);
			}
			else {
				returnError("Er ging iets mis bij het inloggen. Probeer het later opnieuw of neem contact op met de systeembeheerder. Foutmelding: ".$json["message"]." op ".$url." Access token: ".$_SESSION["zermelo_access_token"].". DEEL DEZE ACCESS TOKEN ENKEL MET DE SYSTEEMBEHEERDER!!", $_SESSION);
			}
		}
		else {
			returnError("Missende data: GET year en GET week moeten worden aangegeven.");
		}
    }
?>