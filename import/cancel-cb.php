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
			$cart = $damstedeDB->getDeviceCart($reservation["cart_id"]);
            if ($damstedeDB->cancelReservation($_POST["id"])) {
				if ($cart["cart_type"] == 2) {
					$phpMailerLoaded = include("phpmailer/PHPMailerAutoload.php");
					if (!$phpMailerLoaded) {
						returnData("De reservering is geannuleerd, maar ".$zermeloManagerName." <b>kon hiervan niet op de hoogte worden gebracht</b>. E-mail zelf om de wijziging in het rooster door te voeren: <a href='mailto:".$zermeloManagerEmail."' target='_blank'>".$zermeloManagerEmail."</a>", null);
					}
					else {
						setlocale(LC_ALL, 'nl_NL');
						$mail = new PHPMailer();
						$mail->isSMTP();
						$mail->Host = $smtpHost;
						$mail->Port = $smtpPort;
						$mail->SMTPSecure = $smtpSecure;
						$mail->SMTPAuth = true;
						$mail->Username = $smtpUsername;
						$mail->Password = $smtpPassword;
						$mail->setFrom($smtpUsername, $smtpName);
						$mail->addReplyTo($contactEmail, $contactName);
						$mail->addAddress($zermeloManagerEmail, $zermeloManagerName);
						$mail->isHTML(true);
						$mail->Subject = 'Geannuleerde reservering van '.$reservation["user"].' voor '.$cart["name"].' op '.$reservation["date"].', het '.$reservation["hour"].'e uur';
						$mail->Body = 'Dag '.$zermeloManagerName.',<br><br><b>'.$reservation["user"].'</b> heeft de reservering van <b>'.$cart["name"].'</b>, op <b>'.strftime("%A %e %B", strtotime($reservation["date"])).', het '.$reservation["hour"].'<sup>e</sup> uur</b> via het <a href="'.$portalUrl.'">Device Portal</a> geannuleerd. Kun jij deze lokaalwijziging doorvoeren in Zermelo? Als het helpt; volgens ons systeem was het oorspronkelijke lokaal <b>'.$reservation["location"].'</b>. Aangezien de gebruiker dit zelf aan kan passen, hoeft dit echter niet te kloppen.<br><br><br><small>Dit is een geautomatiseerd bericht vanuit het Device Portal. Mocht je een vraag of opmerking hebben, stuur dan een mailtje naar <a href="mailto:'.$contactEmail.'">'.$contactEmail.'</a>.</small>';
						$mail->AltBody = 'Dag '.$zermeloManagerName.',\n\n'.$reservation["user"].' heeft de reservering van '.$cart["name"].', op '.strftime("%A %e %B", strtotime($reservation["date"])).', het '.$reservation["hour"].'<sup>e</sup> uur via het Device Portal geannuleerd. Kun jij deze lokaalwijziging doorvoeren in Zermelo? Als het helpt; volgens ons systeem was het oorspronkelijke lokaal '.$reservation["location"].'. Aangezien de gebruiker dit zelf aan kan passen, hoeft dit echter niet te kloppen.\n\n\n\nDit is een geautomatiseerd bericht vanuit het Device Portal. Mocht je een vraag of opmerking hebben, stuur dan een mailtje naar '.$contactEmail.'.';
						if (!$mail->send()) {
							returnData("De reservering is geannuleerd, maar ".$zermeloManagerName." <b>kon hiervan niet op de hoogte worden gebracht</b>. E-mail zelf om de wijziging in het rooster door te voeren: <a href='mailto:".$zermeloManagerEmail."' target='_blank'>".$zermeloManagerEmail."</a>", null);
						}
						else {
							returnData("De reservering is geannuleerd en ".$zermeloManagerName." is hiervan op de hoogte gebracht. De reservering zal zo spoedig mogelijk ongedaan worden gemaakt in het rooster op Zermelo.", null);
						}
					}
				}
				else {
					returnData("De reservering is geannuleerd.", null);
				}
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