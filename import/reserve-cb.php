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

    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
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

        if (!isset($_POST["hour"]) || empty($_POST["hour"])) {
            returnError("Lesuur is niet aangegeven. Kies een lesuur in het dropdown-menu.");
        }

        if (intval($_POST["hour"]) < 1 || intval($_POST["hour"]) > 9) {
            returnError("Ongeldig lesuur! Lesuur mag minimaal 1 en maximaal 9 zijn.");
        }

        if (!isset($_POST["location"]) || empty($_POST["location"])) {
            returnError("Geen lokaal ingevuld. Vul het lokaal in waar de kar zal worden gebruikt of waar de les oorspronkelijk plaats vond.");
        }

        if (!isset($_POST["cart"]) || empty($_POST["cart"])) {
            returnError("Geen apparaatkar of lokaal geselecteerd. Kies een kar of lokaal om te reserveren uit het dropdown-menu.");
        }

        if (!isset($_POST["amount"]) || empty($_POST["amount"])) {
            returnError("Geef het aantal te reserveren apparaten op onder 'aantal'.");
        }

        require_once("db.php");
        require_once("nogit.php");
        $damstedeDB = new DamstedeDB();
        
        $cart = $damstedeDB->getDeviceCart($_POST["cart"]);
        if (empty($cart)) {
            returnError("Apparaatkar of lokaal ".intval($_POST["cart"])." bestaat niet.");
        }

        if (!$cart["available"]) {
            returnError("Deze kar of dit lokaal kan momenteel niet gereserveerd worden. Probeer het later opnieuw.");
        }

        if (!$cart["amount_choosable"]) {
            if ($damstedeDB->isReserved($_POST["cart"], $_POST["date"], $_POST["hour"], $_POST["amount"])) {
                returnError("Deze kar of dit lokaal is al gereserveerd voor dit uur. Probeer een ander uit het dropdown-menu.");
            }
        }
        else {
            $devicesLeft = $damstedeDB->getAmountOfDevicesLeft($_POST["cart"], $_POST["date"], $_POST["hour"]);
            if ($devicesLeft < intval($_POST["amount"])) {
                returnError("Er zijn in dit uur nog maar ".$devicesLeft." apparaten over in dit lokaal (je wilde er ".intval($_POST["amount"])." reserveren). Reserveer minder apparaten, of kies een ander lokaal uit het dropdown-menu.");
            }
        }

        // ERROR HANDLING END

        $reserved = $damstedeDB->reserveCart($_POST["cart"], $_POST["date"], $_POST["hour"], $_POST["location"], $_SESSION["user"]["code"], $_POST["teacher"], $_POST["amount"]);
        if ($reserved != false) {
            if ($cart["cart_type"] == 2) {
                $phpMailerLoaded = include("phpmailer/PHPMailerAutoload.php");
                if (!$phpMailerLoaded) {
                    returnData("Je reservering is geplaatst, maar ".$zermeloManagerName." <b>kon niet op de hoogte worden gebracht van de lokaalwijziging</b>. E-mail zelf om de wijziging in het rooster door te voeren: <a href='mailto:".$zermeloManagerEmail."' target='_blank'>".$zermeloManagerEmail."</a>", null);
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
                    $mail->Subject = 'Nieuwe reservering van '.$_SESSION["user"]["code"].' voor '.$cart["name"].' op '.$_POST["date"].', het '.$_POST["hour"].'e uur';
                    $mail->Body = 'Dag '.$zermeloManagerName.',<br><br><b>'.$_SESSION["user"]["code"].'</b> heeft een reservering geplaatst via het <a href="'.$portalUrl.'">Device Portal</a> voor <b>'.$cart["name"].'</b>, op <b>'.strftime("%A %e %B", strtotime($_POST["date"])).', het '.$_POST["hour"].'<sup>e</sup> uur</b>. Kun jij deze lokaalwijziging doorvoeren in Zermelo?<br><br><br><small>Dit is een geautomatiseerd bericht vanuit het Device Portal. Mocht je een vraag of opmerking hebben, stuur dan een mailtje naar <a href="mailto:'.$contactEmail.'">'.$contactEmail.'</a>.</small>';
                    $mail->AltBody = 'Dag '.$zermeloManagerName.',\n\n'.$_SESSION["user"]["code"].' heeft een reservering geplaatst via het Device Portal voor '.$cart["name"].', op '.strftime("%A %e %B", strtotime($_POST["date"])).', het '.$_POST["hour"].'<sup>e</sup> uur. Kun jij deze lokaalwijziging doorvoeren in Zermelo?\n\n\n\nDit is een geautomatiseerd bericht vanuit het Device Portal. Mocht je een vraag of opmerking hebben, stuur dan een mailtje naar '.$contactEmail.'.';
                    if (!$mail->send()) {
                        returnData("Je reservering is geplaatst, maar ".$zermeloManagerName." <b>kon niet op de hoogte worden gebracht van de lokaalwijziging</b>. E-mail zelf om de wijziging in het rooster door te voeren: <a href='mailto:".$zermeloManagerEmail."' target='_blank'>".$zermeloManagerEmail."</a>", null);
                    }
                    else {
                        returnData("Je reservering is geplaatst en ".$zermeloManagerName." is op de hoogte gebracht van de lokaalwijziging. Deze zal zo spoedig mogelijk worden doorgevoerd in het rooster op Zermelo.", null);
                    }
                }
            }
            else {
                returnData("Je reservering is geplaatst!", null);
            }
        }
        else {
            returnError("Kon geen reservering plaatsen. Probeer het later opnieuw.");
        }
    }
?>