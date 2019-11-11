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

    function signInUsingAccessToken($accessToken, $expiresIn, $zermeloSchool) {
        $ch = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$_POST["zermelo-school"].".zportal.nl/api/v3/users/~me?access_token=".$accessToken);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->secure);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($result, true)["response"];
        if (count($json["data"]) > 0) {
            $user = $json["data"][0];
            if ($user["isEmployee"] === true) {
                // set session data
                $_SESSION["zermelo_access_token"] = $accessToken;
                $_SESSION["zermelo_expires_in"] = $expiresIn;
                $_SESSION["zermelo_school"] = $zermeloSchool;
                $_SESSION["user"] = $user;

                // set cookie for future automatic logins
                setcookie("zat", $_SESSION["zermelo_access_token"], 2147483647, "/");
                setcookie("zei", $_SESSION["zermelo_expires_in"], 2147483647, "/");
                setcookie("zs", $_SESSION["zermelo_school"], 2147483647, "/");

                returnData("Login successvol", $_SESSION);
            }
            else {
                returnError("Je hebt onvoldoende rechten om in te loggen bij het Device Portaal van Damstede.");
            }
        }
        else {
            returnError("Er ging iets mis bij het inloggen. Probeer het later opnieuw of neem contact op met de systeembeheerder. Foutmelding: kon geen gebruikersinformatie verkrijgen. Access token: ".$accessToken.". DEEL DEZE ACCESS TOKEN ENKEL MET DE SYSTEEMBEHEERDER");
        }
    }
    
    if (isset($_POST["zermelo-school"]) && !empty($_POST["zermelo-school"]) && isset($_POST["zermelo-code"]) && !empty($_POST["zermelo-code"])) {
        if ($_POST["zermelo-school"] !== "damstedelyceum") {
            returnError("Alleen medewerkers van het Damstede Lyceum kunnen gebruik maken van dit portaal");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://".$_POST["zermelo-school"].".zportal.nl/api/v3/oauth/token");
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->secure);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('grant_type' => 'authorization_code', 'code' => $_POST["zermelo-code"])));
        $result = curl_exec($ch);
        curl_close($ch);
        if (!empty($result)) {
            $accessData = json_decode($result, true);
    
            signInUsingAccessToken($accessData["access_token"], time() + intval($accessData["expires_in"]), $_POST["zermelo-school"]);
        }
        else {
            returnError("Er ging iets mis bij het inloggen. Druk opnieuw op koppel app om de toegangscode te vernieuwen (in Zermelo) en probeer het nogmaals.");
        }
    }
    else if (isset($_COOKIE["zat"]) && !empty($_COOKIE["zat"]) && isset($_COOKIE["zei"]) && !empty($_COOKIE["zei"]) && isset($_COOKIE["zs"]) && !empty($_COOKIE["zs"])) {
        signInUsingAccessToken($_COOKIE["zat"], $_COOKIE["zei"], $_COOKIE["zs"]);
    }
    else {
        returnError("Onvoldoende data ontvangen: POST fields zermelo-school en zermelo-code missen");
    }
?>