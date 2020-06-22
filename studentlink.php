<?PHP
    require_once("import/nogit.php");
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Log in via Google</title>
    <?PHP include_once("import/headers.html"); ?>
    <meta name="google-signin-client_id" content="893609922416-qfs9mfsck2qabafc8ourg8i19h29lfve.apps.googleusercontent.com">
</head>
<body>
<body onload="document.getElementById('loading').style.display = 'none';">
    <header>
        <h1 id="pagetitle">Log in via Google - Damstede Device Portaal<span class="extra-info"> voor Leerlingen</span></h1>
        <div id="pageoptions">
            
        </div>
    </header>
    <div id="content" class="extrapadding">
        <p><b>Om gebruik te kunnen maken van het Device Portaal en een computer in de mediatheek te reserveren, moet je inloggen met jouw &commat;<?PHP echo $gSuiteDomain; ?> Google-account:</b></p>
        
        <br />
        <div id="my-signin2" data-onsuccess="onSignIn"></div>
        
        <script>

        function onGSuccess(googleUser) {
            document.getElementById('loading').style.display = 'table';
            var id_token = googleUser.getAuthResponse().id_token;
            console.log(id_token);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://devices.damstede.eu/import/google-cb.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                var response = xhr.responseText;
                console.log(response);
                if (response.indexOf("error:") == 0) {
                    var auth2 = gapi.auth2.getAuthInstance();
                    auth2.signOut().then(function () {
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById("loginerror-msg").innerHTML = response.split(":")[1];
                        showAction('loginerror');
                    });
                }
                else if (response.indexOf("success:") == 0) {
                    window.location.href = "student.php";
                }
                else {
                    var auth2 = gapi.auth2.getAuthInstance();
                    auth2.signOut().then(function () {
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById("loginerror-msg").innerHTML = "Onbekende loginstatus. Laat dit weten aan het systeembeheer via ".$contactEmail;
                        showAction('loginerror');
                    });
                }
            };
            xhr.send('id_token=' + id_token);
        }

        function onGFailure(error) {
            console.error(error);
            var doShowErrorMsg = true;
            switch (error["error"]) {
                case "popup_closed_by_user":
                    doShowErrorMsg = false;
                    break;
                case "access_denied":
                    document.getElementById("loginerror-msg").innerHTML = "Je moet toegang geven tot de gevraagde gegevens om het Device Portaal te kunnen gebruiken.";
                    break;
                default:
                    document.getElementById("loginerror-msg").innerHTML = error["error"];
                    break;
            }

            if (doShowErrorMsg) {
                showAction('loginerror');
            }
        }

        function renderButton() {
            gapi.signin2.render('my-signin2', {
                'scope': 'profile email',
                'width': 260,
                'height': 50,
                'longtitle': true,
                'theme': 'dark',
                'onsuccess': onGSuccess,
                'onfailure': onGFailure
            });
        }
        </script>

        <div class="action" id="loginerror" style="display: none;">
            <div class="inneraction">
                <div class="actioncontent">
                    <div class="actionheader">Er ging iets fout</div>
                    <div class="actionclose" data-action="loginerror" onclick="hideAction(this);">&#x2716;</div>
                    <p id="loginerror-msg">Een onbekende fout is opgetreden. Probeer het later opnieuw.</p>
                    <div class="actionbuttons">
                        <input class="button" type="button" value="Oké" data-action="loginerror" onclick="hideAction(this);" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loading" style="display: table; width: 100%; height: 100%; position: fixed; top: 0px; left: 0px; right: 0px; bottom: 0px; background-color: rgba(0,0,0,0.8);">
		<div style="display: table-cell; vertical-align: middle;">
			<div style="margin-left: auto; margin-right: auto; text-align: center;">
				<div id="spinner"></div>
			</div>
		</div>
    </div>
    
    <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
</body>
</html>