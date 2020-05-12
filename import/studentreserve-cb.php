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
	
	function returnError($msg) {
		global $data;
		$data["type"] = "error";
		$data["message"] = $msg;
		$data["data"] = array();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
	}
	
	function returnWarning($msg) {
		global $data;
		$data["type"] = "warning";
		$data["message"] = $msg;
		$data["data"] = array();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
	}
	
	function returnData($msg, $stuff) {
		global $data;
		$data["type"] = "success";
		$data["message"] = $msg;
		$data["data"] = $stuff;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		die();
    }

    session_start();

    if (!isset($_SESSION["google_signed_in"]) || empty($_SESSION["google_signed_in"]) || $_SESSION["google_signed_in"] === false) {
        returnError("Je bent niet (meer) ingelogd! Log opnieuw in.");
    }
    else {
        // ERROR HANDLING START

        if (!isset($_POST["date"]) || empty($_POST["date"])) {
            returnError("Datum is niet ingevuld. Vul een datum in.");
        }
        
        if (strtotime($_POST["date"]) < strtotime("today")) {
            returnError("Datum is in het verleden... Je kunt alleen voor vandaag of in de toekomst reserveren!");
        }

        if (strtotime($_POST["date"]) > strtotime("+4 weeks")) {
            returnError("Je kunt maximaal 4 weken van tevoren reserveren. Voor deze datum kun je reserveren vanaf ".date("d-m-Y", strtotime($_POST["date"] . " -4 weeks")).".");
        }

        if (!isset($_POST["hour"]) || empty($_POST["hour"])) {
            returnError("Lesuur is niet aangegeven. Kies een lesuur in het dropdown-menu.");
        }

        if (intval($_POST["hour"]) < 1 || intval($_POST["hour"]) > 9) {
            returnError("Ongeldig lesuur! Lesuur mag minimaal 1 en maximaal 9 zijn.");
        }

        require_once("db.php");
        require_once("nogit.php");
        $damstedeDB = new DamstedeDB();
        
        $cart = $damstedeDB->getDeviceCart(5);

        if (!$cart["available"]) {
            returnError("Reserveren is momenteel niet mogelijk. Probeer het later opnieuw.");
        }

        $devicesLeft = $damstedeDB->getAmountOfDevicesLeft(5, $_POST["date"], $_POST["hour"]);
        if ($devicesLeft < 1) {
            returnError("Er is in het gekozen lesuur op deze datum geen plek meer in de mediatheek. Kies een ander lesuur.");
        }

        if (!$damstedeDB->userHasNotReservedYet(false, $_SESSION["user"]["code"], $_POST["date"], $_POST["hour"])) {
            returnError("Je hebt voor dit lesuur op deze datum al een computer gereserveerd in de mediatheek.");
        }

        // ERROR HANDLING END

        $reserved = $damstedeDB->reserveCart(5, $_POST["date"], $_POST["hour"], "Mediatheek", $_SESSION["user"]["code"], $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"], 1);
        if ($reserved != false) {
            returnData("Je reservering is geplaatst!", null);
        }
        else {
            returnError("Kon geen reservering plaatsen. Probeer het later opnieuw.");
        }
    }
?>