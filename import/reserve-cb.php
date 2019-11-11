<?PHP
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

    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        returnError("Je bent niet (meer) ingelogd! Log opnieuw in.");
    }
    else {
        // ERROR HANDLING START

        if (!isset($_POST["date"]) || empty($_POST["date"])) {
            returnError("Datum is niet ingevuld. Vul een datum in.");
        }
        
        if (strtotime($_POST["date"]) < strtotime(date("today"))) {
            returnError("Datum is in het verleden... Je kunt alleen voor vandaag of in de toekomst reserveren!");
        }

        if (!isset($_POST["hour"]) || empty($_POST["hour"])) {
            returnError("Lesuur is niet aangegeven. Kies een lesuur in het dropdown-menu.");
        }

        if (intval($_POST["hour"]) < 1 || intval($_POST["hour"]) > 9) {
            returnError("Ongeldig lesuur! Lesuur mag minimaal 1 en maximaal 9 zijn.");
        }

        if (!isset($_POST["location"]) || empty($_POST["location"])) {
            returnError("Geen lokaal ingevuld. Vul het lokaal in waar de kar zal worden gebruikt.");
        }

        if (!isset($_POST["cart"]) || empty($_POST["cart"])) {
            returnError("Geen apparaatkar geselecteerd. Kies een kar om te reserveren uit het dropdown-menu.");
        }

        require_once("db.php");
        $damstedeDB = new DamstedeDB();
        
        if (empty($damstedeDB->getDeviceCart($_POST["cart"]))) {
            returnError("Apparaatkar ".intval($_POST["cart"])." bestaat niet.");
        }

        if ($damstedeDB->isReserved($_POST["cart"], $_POST["date"], $_POST["hour"])) {
            returnError("Deze kar is al gereserveerd voor dit uur. Probeer een andere kar uit het dropdown-menu.");
        }

        // ERROR HANDLING END

        $reserved = $damstedeDB->reserveCart($_POST["cart"], $_POST["date"], $_POST["hour"], $_POST["location"], $_SESSION["user"]["code"], $_POST["teacher"]);
        if ($reserved != false) {
            returnData("Reservering geplaatst", null);
        }
        else {
            returnError("Kon geen reservering plaatsen. Probeer het later opnieuw.");
        }
    }
?>