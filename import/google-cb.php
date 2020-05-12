<?PHP
    @session_start();

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    function encodeURIComponent($str) {
		$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
		return strtr(rawurlencode($str), $revert);
	}
	
	$context = stream_context_create(array(
		'http' => array('ignore_errors' => true),
	));

    $id_token = strip_tags(stripslashes($_POST['id_token']));
    $json = file_get_contents("https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=".encodeURIComponent($id_token), false, $context);
    
    if ($json != false) {
		$auth = json_decode($json, true);
		if ($auth != null) {
			if (!empty($auth["error"]) || !empty($auth["error_description"])) {
				echo "error:We konden niet vaststellen dat jij het echt bent. Error details; ";
				if (!empty($auth["error"])) {
					echo '['.strtolower($auth["error"]).'] ';
				}
				echo strtolower($auth["error_description"]);
			}
			else {
				if (!empty($auth["email"]) && !empty($auth["name"]) && !empty($auth["picture"]) && !empty($auth["given_name"])) {
                    $_SESSION["google_signed_in"] = true;
                    $_SESSION["user"] = array();
                    $_SESSION["user"]["code"] = explode("@", $auth["email"])[0];
                    $_SESSION["user"]["roles"] = array();
                    $_SESSION["user"]["prefix"] = "";
                    $_SESSION["user"]["firstName"] = (!empty($auth["given_name"]) ? $auth["given_name"] : $auth["name"]);
                    $_SESSION["user"]["lastName"] = (!empty($auth["family_name"]) ? $auth["family_name"] : "");
                    $_SESSION["user"]["email"] = $auth["email"];
                    $_SESSION["user"]["schoolInSchoolYears"] = array();
                    $_SESSION["user"]["isApplicationManager"] = false;
                    $_SESSION["user"]["archived"] = false;
                    $_SESSION["user"]["hasPassword"] = true;
                    $_SESSION["user"]["isStudent"] = true;
                    $_SESSION["user"]["isEmployee"] = false;
                    $_SESSION["user"]["isFamilyMember"] = false;
                    $_SESSION["user"]["isSchoolScheduler"] = false;
                    $_SESSION["user"]["isSchoolLeader"] = false;
                    $_SESSION["user"]["isStudentAdministrator"] = false;
                    $_SESSION["user"]["isTeamLeader"] = false;
                    $_SESSION["user"]["isSectionLeader"] = false;
                    $_SESSION["user"]["isMentor"] = false;
                    $_SESSION["user"]["isParentTeacherNightScheduler"] = false;
                    $_SESSION["user"]["isDean"] = false;

                    echo "success:Je bent nu ingelogd";
                }
                else {
					echo "error:Je moet toegang geven tot de gevraagde gegevens om het Device Portaal te kunnen gebruiken.";
				}
            }
        }
        else {
            echo "error:We konden niet vaststellen dat jij het echt bent vanwege een serverfout. Probeer het later opnieuw.";
        }
    }
    else {
        echo "error:We konden niet vaststellen dat jij het echt bent, omdat Google niet bereikbaar is. Probeer het later opnieuw.";
    }
?>