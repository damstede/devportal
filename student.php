<?PHP
    // error_reporting(1); ini_set('display_errors', 1);

    session_start();
    if (!isset($_SESSION["google_signed_in"]) || empty($_SESSION["google_signed_in"]) || $_SESSION["google_signed_in"] === false) {
        header("Location: studentlink.php", 301);
        exit();
    }

    require_once("import/db.php");
    $damstedeDB = new DamstedeDB();
    $carts = $damstedeDB->getDeviceCarts();
    $defaultOpeningHours = $damstedeDB->getDefaultOpeningHours($mediatheekId);
    $mediatheek = $damstedeDB->getDeviceCart($mediatheekId);
    $upcomingReservations = $damstedeDB->getMyUpcomingReservations($_SESSION["user"]["code"], 4);

    function getCartById($id) {
        global $carts;

        foreach($carts as $cart) {
            if ($id == $cart["id"]) {
                return $cart;
            }
        }
        return null;
    }

    function openingHoursToText($hours) {
        if ($hours["opening_hours_start"] == 0 && $hours["opening_hours_end"] == 0) {
            return "gesloten";
        }
        else {
            return $hours["opening_hours_start"] . "<sup>e</sup> t/m " . $hours["opening_hours_end"] . "<sup>e</sup> uur";
        }
    }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Damstede Device Portaal</title>
    <?PHP include_once("import/headers.html"); ?>
</head>
<body>
    <header>
        <h1 id="pagetitle"><span class="extra-extra-info">Damstede </span><span>Device Portaal</span><span class="extra-info"> voor Leerlingen</span></h1>
        <div id="pageoptions">
            <div id="addreservation" title="Nieuwe reservering aanmaken" onclick="showAction('reservationadder'); checkOpen();">+</div>
            <div class="awesome" id="signout" title="Uitloggen (ingelogd als <?PHP echo $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"]; ?>)" onclick="window.location.href='unlink.php';">&#xf08b;</div>
        </div>
    </header>

    <div id="content">
        <h2>Algemene openingstijden mediatheek</h2>
        <hr />

        <ul class="opening-schedule">
            <li><b>Maandag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[0]); ?></li>
            <li><b>Dinsdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[1]); ?></li>
            <li><b>Woensdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[2]); ?></li>
            <li><b>Donderdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[3]); ?></li>
            <li><b>Vrijdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[4]); ?></li>
        </ul>

        <br>
        
        <h2>Jouw reserveringen</h2>
        <hr />

        <ul id="reservations-list">
            <?PHP
                foreach($upcomingReservations as $res) {
                    ?>
                    <li class="reservation-list-item<?PHP echo ($res["cancelled"] ? " cancelled" : "") . (strtotime($res["date"]) < strtotime(date("Y-m-d")) ? " over" : ""); ?>"><span class="date_to_parse"><?PHP echo $res["date"]; ?></span>, <?PHP echo $res["hour"]; ?><sup>e</sup> uur: <?PHP echo $res["amount"] . " plaats" . ($res["amount"] > 1 ? "en" : ""); ?> in <?PHP echo getCartById($res["cart_id"])["name"]; ?></li>
                    <?PHP
                }
            ?>
        </ul>
        <script>
            var datesToParse = document.getElementsByClassName("date_to_parse");
            for (var i = 0; i < datesToParse.length; i++) {
                var date = new Date(datesToParse[i].innerHTML);
                var parsedDate = date.getDate() + " ";
                datesToParse[i].innerHTML = parsedDate + monthNumToName(date.getMonth()+1);
            }
        </script>
    </div>

    <script>
    function reservationSubmit() {
        console.log("Reservation form submit");
        document.getElementById('loading').style.display = 'table';
        var form = document.getElementById('reservation-form');
        var reservationError = document.getElementById("reservation-error-msg");
        var data = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.getAttribute('action'), true);
        xhr.onload = function() {
            document.getElementById('loading').style.display = 'none';
            if (xhr.status !== 200) {
                reservationError.innerHTML = xhr.statusText+". Probeer het later opnieuw.";
                showAction('reservationerror');
            }
            else {
                var res = JSON.parse(xhr.responseText);
                console.log(res);
                switch (res.type) {
                    case "success": {
                        document.getElementById("reservation-success").innerHTML = res.message;
                        showAction('reservationsuccess');
                        break;
                    }
                    default: {
                        reservationError.innerHTML = res.message;
                        showAction('reservationerror');
                        break;
                    }
                }
            }
        };
        xhr.send(data);
    }

    function setUpReservationCanceller(reservationId) {
        document.getElementById("cancel-id").value = reservationId;
    }

    function cancelSubmit() {
        console.log("Cancel form submit");
        document.getElementById('loading').style.display = 'table';
        var form = document.getElementById('cancel-form');
        var data = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.getAttribute('action'), true);
        xhr.onload = function() {
            document.getElementById('loading').style.display = 'none';
            if (xhr.status !== 200) {
                document.getElementById("cancel-error-message").innerHTML = xhr.statusText+". Probeer het later opnieuw.";
                showAction('cancelerror');
            }
            else {
                var cancelResponse = JSON.parse(xhr.responseText);
                if (cancelResponse.type == "success") {
                    document.getElementById("cancel-message").innerHTML = cancelResponse.message;
                    showAction('cancelmessage');
                }
                else {
                    document.getElementById("cancel-error-message").innerHTML = cancelResponse.message;
                    showAction('cancelerror');
                }
            }
        };
        xhr.send(data);
    }
    </script>

    <div class="action" id="reservationadder" style="display: none;">
		<div class="inneraction">
			<div class="actioncontent">
				<form id="reservation-form" action="import/studentreserve-cb.php" method="post" target="_self" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off" onsubmit="">
					<div class="actionheader">Reserveer<span class="extra-extra-info" > een computer</span><span class="extra-info" > in de Mediatheek</span></div>
					<div class="actionclose" data-action="reservationadder" onclick="hideAction(this);">&#x2716;</div>
					<table class="actiontable">
						<tr>
							<th>Datum</th>
							<td>
								<input type="date" name="date" id="date" value="<?PHP echo date("Y-m-d"); ?>" min="<?PHP echo date("Y-m-d"); ?>" max="<?PHP echo date("Y-m-d", strtotime("+4 weeks")); ?>" placeholder="YYYY-MM-DD" required />
							</td>
                        </tr>
                        <tr id="hour-row">
                            <th>Lesuur</th>
                            <td>
                                <select name="hour" id="hour" required>
                                    <option value="" selected disabled>Selecteer een lesuur...</option>
                                    <option value="1">1<sup>e</sup> uur</option>
                                    <option value="2">2<sup>e</sup> uur</option>
                                    <option value="3">3<sup>e</sup> uur</option>
                                    <option value="4">4<sup>e</sup> uur</option>
                                    <option value="5">5<sup>e</sup> uur</option>
                                    <option value="6">6<sup>e</sup> uur</option>
                                    <option value="7">7<sup>e</sup> uur</option>
                                    <option value="8">8<sup>e</sup> uur</option>
                                    <option value="9">9<sup>e</sup> uur</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;"><span id="openwarning"><br/></span></td>
                        </tr>
                        <tr id="available-space-row">
                            <td colspan="2" style="text-align: center;">Aantal plaatsen beschikbaar: <span id="available-space">--/<?PHP echo $mediatheek["dev_amount"]; ?></span></td>
                            <script>
                                var checkXhr = null;
                                function checkAvailable(event) {
                                    var chosenDate = document.getElementById("date").value;
                                    var chosenHour = document.getElementById("hour").value;
                                    if (chosenDate == null || chosenDate == "" || chosenHour == null || chosenHour == "") {
                                        document.getElementById("available-space").innerHTML = "--/<?PHP echo $mediatheek["dev_amount"]; ?>";
                                    }
                                    else {
                                        document.getElementById("available-space").innerHTML = "<i>gegevens ophalen...</i>";
                                        checkXhr = new XMLHttpRequest();
                                        checkXhr.open('GET', 'https://devices.damstede.eu/import/availabledevices.php?cart=5&date='+chosenDate+'&hour='+chosenHour);
                                        checkXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                        checkXhr.onload = function() {
                                            var response = JSON.parse(checkXhr.responseText);
                                            document.getElementById("available-space").innerHTML = response["data"]+"/<?PHP echo $mediatheek["dev_amount"]; ?>";
                                        };
                                        checkXhr.send();
                                    }
                                }

                                var openXhr = null;
                                function checkOpen(event) {
                                    var chosenDate = document.getElementById("date").value;
                                    if (chosenDate == null || chosenDate == "") {
                                        document.getElementById("openwarning").innerHTML = "<br/>";
                                    }
                                    else {
                                        document.getElementById("openwarning").innerHTML = "<br/>";
                                        openXhr = new XMLHttpRequest();
                                        openXhr.open('GET', 'https://devices.damstede.eu/import/openinghours.php?cart=5&date='+chosenDate);
                                        openXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                        openXhr.onload = function() {
                                            var response = JSON.parse(openXhr.responseText);
                                            if (response["type"] == "success") {
                                                response = response["data"];
                                                var hourInput = document.getElementById("hour");
                                                for (var i = 1; i <= 9; i ++) {
                                                    hourInput.children[i].disabled = response["opening_hours"].indexOf(i) == -1;
                                                }
                                                if (response["is_default"]) {
                                                    if (response["opening_hours"].length == 0) {
                                                        if (response["weekday"] > 0 && response["weekday"] < 6) {
                                                            document.getElementById("openwarning").innerHTML = "<b>De mediatheek is op "+response["weekday_text"]+"en gesloten.</b>";
                                                        }
                                                        else {
                                                            document.getElementById("openwarning").innerHTML = "<b>De mediatheek is in het weekend gesloten.</b>";
                                                        }
                                                        document.getElementById("available-space-row").style.display = "none";
                                                        document.getElementById("hour-row").style.display = "none";
                                                        document.getElementById("reservebtn").disabled = true;
                                                    }
                                                    else {
                                                        document.getElementById("openwarning").innerHTML = "<br/>";
                                                        document.getElementById("available-space-row").style.display = null;
                                                        document.getElementById("hour-row").style.display = null;
                                                        document.getElementById("reservebtn").disabled = false;
                                                    }
                                                }
                                                else {
                                                    if (response["opening_hours"].length > 0) {
                                                        document.getElementById("openwarning").innerHTML = "<b>Let op: gewijzigde openingstijden!</b><br/>Op deze datum is de mediatheek geopend van het "+response["opening_hours_start"]+"<sup>e</sup> t/m het "+response["opening_hours_end"]+"<sup>e</sup> uur.";
                                                        document.getElementById("available-space-row").style.display = null;
                                                        document.getElementById("hour-row").style.display = null;
                                                        document.getElementById("reservebtn").disabled = false;
                                                    }
                                                    else {
                                                        document.getElementById("openwarning").innerHTML = "<b>Let op: gewijzigde openingstijden!</b><br/>Op deze datum is de mediatheek gesloten. Probeer een andere datum.";
                                                        document.getElementById("available-space-row").style.display = "none";
                                                        document.getElementById("hour-row").style.display = "none";
                                                        document.getElementById("reservebtn").disabled = true;
                                                    }
                                                }
                                            }
                                        };
                                        openXhr.send();
                                    }
                                }

                                document.getElementById("date").addEventListener("change", checkAvailable);
                                document.getElementById("date").addEventListener("change", checkOpen);
                                document.getElementById("hour").addEventListener("change", checkAvailable);
                            </script>
                        </tr>
					</table>
					<div class="actionbuttons">
						<input class="button extra" type="button" value="Annuleren" data-action="reservationadder" onclick="hideAction(this);" />
						<input class="button" type="button" value="Reserveer" name="reserve" onclick="reservationSubmit();" id="reservebtn" />
					</div>
				</form>
			</div>
		</div>
    </div>

    <div class="action important" id="reservationerror" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Er ging iets fout</div>
                <div class="actionclose" data-action="reservationerror" onclick="hideAction(this); showAction('reservationadder'); checkOpen();">&#x2716;</div>
                <p id="reservation-error-msg">Een onbekende fout is opgetreden. Probeer het later opnieuw.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="reservationerror" onclick="hideAction(this); showAction('reservationadder'); checkOpen();" />
                </div>
            </div>
        </div>
    </div>

    <div class="action" id="reservationsuccess" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Reservering geplaatst</div>
                <div class="actionclose" data-action="reservationsuccess" onclick="hideAction(this); window.location.reload();">&#x2716;</div>
                <p id="reservation-success">Je reservering is geplaatst!</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="reservationsuccess" onclick="hideAction(this); window.location.reload();" />
                </div>
            </div>
        </div>
    </div>

    <div class="action important" id="cancelerror" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Kon reservering niet annuleren</div>
                <div class="actionclose" data-action="cancelerror" onclick="hideAction(this);">&#x2716;</div>
                <p id="cancel-error-message">Er is een onbekende fout opgetreden. Probeer het later opnieuw.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="cancelerror" onclick="hideAction(this);" />
                </div>
            </div>
        </div>
    </div>

    <div class="action" id="cancelmessage" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Reservering geannuleerd</div>
                <div class="actionclose" data-action="cancelmessage" onclick="hideAction(this); window.location.reload();">&#x2716;</div>
                <p id="cancel-message">De reservering is geannuleerd.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="cancelmessage" onclick="hideAction(this); window.location.reload();" />
                </div>
            </div>
        </div>
    </div>

    <div class="action" id="reservationcancel" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <form id="cancel-form" action="import/cancel-cb.php" method="get" target="_self" accept-charset="utf-8" autocomplete="off" onsubmit="">
                    <div class="actionheader">Reservering annuleren</div>
                    <div class="actionclose" data-action="reservationcancel" onclick="hideAction(this);">&#x2716;</div>
                    <p>Weet je zeker dat je deze reservering wilt annuleren?</p>
                    <input type="hidden" id="cancel-id" name="id" value="" />
                    <div class="actionbuttons">
                        <input class="button extra" type="button" value="Nee" data-action="reservationcancel" onclick="hideAction(this);" />
                        <input class="button" type="button" value="Ja" data-action="reservationcancel" onclick="hideAction(this); cancelSubmit();" />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loading" style="display: none; width: 100%; height: 100%; position: fixed; top: 0px; left: 0px; right: 0px; bottom: 0px; background-color: rgba(0,0,0,0.8);">
		<div style="display: table-cell; vertical-align: middle;">
			<div style="margin-left: auto; margin-right: auto; text-align: center;">
				<div id="spinner"></div>
			</div>
		</div>
	</div>
</body>