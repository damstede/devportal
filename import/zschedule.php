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
		returnError("Deze functie werkt (nog) niet. Probeer het later opnieuw.");
		/*
        $ch = curl_init();
        $url = "https://".$_SESSION["zermelo_school"].".zportal.nl/api/v3/appointments/?user=~me&start=".strtotime("04-11-2019")."&end".strtotime("08-11-2019")."&access_token=".$_SESSION["zermelo_access_token"];
        echo $url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->secure);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
		echo $result;
		*/
        /*
        $json = json_decode($result, true)["response"];
        if (count($json["data"]) > 0) {
            
        }
        else {
            returnError("Er ging iets mis bij het inloggen. Probeer het later opnieuw of neem contact op met de systeembeheerder. Foutmelding: kon geen gebruikersinformatie verkrijgen. Access token: ".$accessData["access_token"].". DEEL DEZE ACCESS TOKEN ENKEL MET DE SYSTEEMBEHEERDER");
        }
        */
    }
?>