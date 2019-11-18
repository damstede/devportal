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
        if (!isset($_POST["id"]) || empty($_POST["id"])) {
            returnError("Geen reservering-ID opgegeven. Geef een reservering-ID op via POST id.");
        }

        require_once("db.php");
        $damstedeDB = new DamstedeDB();

        $reservation = $damstedeDB->getCartReservation($_POST["id"]);
        if ($reservation["user"] === $_SESSION["user"]["code"]) {
            if ($damstedeDB->cancelReservation($_POST["id"])) {
                returnData("De reservering is geannuleerd.", $damstedeDB->makeSafe($_POST["id"]));
            }
            else {
                returnError("Kon reservering niet annuleren. Probeer het later opnieuw.");
            }
        }
        else {
            returnError("Je kunt alleen reserveringen annuleren die je zelf hebt gemaakt.");
        }
    }
?>