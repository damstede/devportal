<?PHP

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Log in via Zermelo</title>
    <?PHP include_once("import/headers.html"); ?>
    <script type="module">
        import QrScanner from './import/qr-scanner.min.js';
        QrScanner.WORKER_PATH = 'import/qr-scanner-worker.min.js';
        var scanner = false;
        QrScanner.hasCamera().then(function(hasCamera) {
            if (hasCamera) {
                scanner = new QrScanner(document.getElementById('qr-preview'), function(result) {
                    console.log(result);
                    try {
                        var zResult = JSON.parse(result);
                        if (Object.keys(zResult).indexOf("code") == -1 || Object.keys(zResult).indexOf("institution") == -1) {
                            alert("Ongeldige Zermelo QR-code");
                        }
                        else {
                            document.getElementById("loading").style.display = "table";
                            scanner.destroy();
                            scanner = null;
                            console.log("QR-scanner gestopt");
                            document.getElementById("loading").style.display = "none";
                            if (zResult["institution"] === "damstedelyceum") {
                                document.getElementById('zermelo-code').value = zResult["code"];
                                showAction('zermelolink');
                                document.getElementById("zermelo-code-form").submit();
                            }
                            else {
                                alert("School wordt niet ondersteund. Dit portaal is alleen bedoeld voor het Damstede Lyceum.");
                            }
                        }
                    }
                    catch(e) {
                        console.log(e);
                        alert("Ongeldige Zermelo QR-code");
                    }
                });
            }
        });
        window.requestCameraAccess = function() {
            if (scanner !== false) {
                document.getElementById("loading").style.display = "table";
                scanner.start().then(function() {
                    console.log("QR-scanner gestart");
                    showAction('qrscanner');
                    document.getElementById("loading").style.display = "none";
                }).catch(function(e) {
                    alert("Een onverwachte fout is opgetreden.\nGebruik een toegangscode om in te loggen op dit apparaat.");
                    document.getElementById("loading").style.display = "none";
                })
            }
            else {
                alert("Geen camera's gevonden om te gebruiken, of je hebt geen toegang tot je camera gegeven.\nGebruik een toegangscode om in te loggen op dit apparaat.");
                document.getElementById("loading").style.display = "none";
            }
        }
        window.pauseCameraAccess = function() {
            scanner.pause();
        }
    </script>
</head>
<body onload="document.getElementById('loading').style.display = 'none';">
    <header>
        <h1 id="pagetitle">Log in via Zermelo - Damstede Device Portaal</h1>
    </header>
    <div id="content" class="extrapadding">
        <p><b>Om gebruik te kunnen maken van het Device Portaal en een iPad-kar voor je les te reserveren, moet je inloggen via Zermelo:</b></p>
        <ol>
            <li>Open het <a href="https://damstedelyceum.zportal.nl/main/#connectionsModule-connectApp" target="_blank">Zermelo Portal</a> en log in</li>
            <li>Ga naar menu Koppelingen</li>
            <li>Druk op Koppel App. Je ziet nu een pagina met een QR-code en een toegangscode.</li>
            <li>Kies hieronder hoe je wilt inloggen: door de QR-code te scannen, of handmatig de toegangscode in te vullen.</li>
        </ol>
        <div id="signin-chooser">
            <button id="signin-btn-camera" onclick="requestCameraAccess();">QR-code scannen</button>
            <button id="signin-btn-code" onclick="showAction('zermelolink');">Handmatig inloggen</button>
        </div>

        <script>
        function showAction(name) {
            var actions = document.getElementsByClassName("action");
            for (var i = 0; i < actions.length; i++) {
                actions[i].style.display = "none";
            }
            
            var action = document.getElementById(name);
            action.style.display = "table";
        }
        
        function hideAction(elem) {
            var actionName = elem.getAttribute("data-action");
            
            var action = document.getElementById(actionName);
            action.style.display = "none";
        }
        </script>

        <div class="action" id="qrscanner" style="display: none;">
            <div class="inneraction">
                <div class="actioncontent">
                    <div class="actionheader">QR-code scannen</div>
                    <div class="actionclose" data-action="qrscanner" onclick="hideAction(this); pauseCameraAccess();">&#x2716;</div>
                    <video id="qr-preview"></video>
                    <div class="actionbuttons">
                        <input class="button extra" type="button" value="Annuleren" data-action="qrscanner" onclick="hideAction(this); pauseCameraAccess();" />
                    </div>
                </div>
            </div>
        </div>

        <div class="action" id="zermelolink" style="display: none;">
            <div class="inneraction">
                <div class="actioncontent">
                    <form id="zermelo-code-form" action="import/link-cb.php" method="post" target="_self" accept-charset="utf-8" autocomplete="off" onsubmit="document.getElementById('loading').style.display = 'table';">  
                        <div class="actionheader">Toegangscode invullen</div>
                        <div class="actionclose" data-action="zermelolink" onclick="hideAction(this);">&#x2716;</div>
                        <table class="actiontable">
                            <tr>
                                <th>Schoolnaam</th>
                                <td><input required type="text" readonly id="zermelo-school" name="zermelo-school" autocomplete="off" placeholder=">schoolnaam<.zportal.nl" size="65" value="damstedelyceum" /></td>
                            </tr>
                            <tr>
                                <th>Toegangscode</th>
                                <td><input required type="number" id="zermelo-code" name="zermelo-code" autocomplete="off" placeholder="XXX XXX XXX XXX" size="65" min="0" max="999999999999" value="" /></td>
                            </tr>
                        </table>
                        <div class="actionbuttons">
                            <input class="button extra" type="button" value="Annuleren" data-action="zermelolink" onclick="hideAction(this);" />
                            <input class="button" type="submit" value="Inloggen" name="login" />
                        </div>
                    </form>
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
</body>
</html>