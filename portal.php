<?PHP
    session_start();
    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        header("Location: link.php", 301);
        exit();
    }

    require_once("import/db.php");
    $damstedeDB = new DamstedeDB();
    $carts = $damstedeDB->getDeviceCarts();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Damstede Device Portaal</title>
    <?PHP include_once("import/headers.html"); ?>
    <script src="import/schedule.js"></script>
</head>
<body>
    <header>
        <h1 id="pagetitle"><span class="extra-extra-info">Damstede </span><span>Device Portaal</span></h1>
        <div id="pageoptions">
            <div class="extra-extra-info" id="addreservation" title="Nieuwe reservering aanmaken" onclick="showAction('reservationadder'); setUpReservationAdder('','', '', 32);">+</div>
            <div class="awesome" id="info" title="Informatie" onclick="showAction('basic-info');">&#xf05a;</div>
            <div class="awesome" id="manual" title="Handleiding openen (PDF)" onclick="window.open('HandleidingDevicePortalDamstede.pdf');">&#xf02d;</div>
            <div class="awesome" id="signout" title="Uitloggen (ingelogd als <?PHP echo $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"]; ?>)" onclick="window.location.href='unlink.php';">&#xf08b;</div>
        </div>
    </header>
    <div id="content" class="nopadding">
        <table class="schedule">
            <thead>
                <tr>
                    <th class="hour"></th>
                    <td class="week-changer prev"><a href="javascript:schedule.getAndLoad(schedule.currentlyLoaded[0], schedule.currentlyLoaded[1]-1)"><span class="awesome">&#xf053;</span><span class="extra-extra-info"> vorige</span><span class="extra-info"> week</span></a></td>
                    <td class="week-name" colspan="3">bezig met laden...</td>
                    <td class="week-changer next"><a href="javascript:schedule.getAndLoad(schedule.currentlyLoaded[0], schedule.currentlyLoaded[1]+1)"><span class="extra-extra-info">volgende </span><span class="extra-info">week </span><span class="awesome">&#xf054;</span></a></td>
                </tr>
                <tr>
                    <th class="hour"></th>
                    <th class="day"><span class="dayname">Ma</span><span class="daydate"></span></th>
                    <th class="day"><span class="dayname">Di</span><span class="daydate"></span></th>
                    <th class="day"><span class="dayname">Wo</span><span class="daydate"></span></th>
                    <th class="day"><span class="dayname">Do</span><span class="daydate"></span></th>
                    <th class="day"><span class="dayname">Vr</span><span class="daydate"></span></th>
                </tr>
            </thead>
            <tbody>
                <tr id="week-hour-1">
                    <th class="hour">1.</th>
                    <td class="lesson" data-lesson="1-1" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-1" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-1" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-1" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-1" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-2">
                    <th class="hour">2.</th>
                    <td class="lesson" data-lesson="1-2" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-2" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-2" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-2" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-2" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-3">
                    <th class="hour">3.</th>
                    <td class="lesson" data-lesson="1-3" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-3" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-3" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-3" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-3" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-4">
                    <th class="hour">4.</th>
                    <td class="lesson" data-lesson="1-4" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-4" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-4" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-4" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-4" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-5">
                    <th class="hour">5.</th>
                    <td class="lesson" data-lesson="1-5" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-5" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-5" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-5" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-5" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-6">
                    <th class="hour">6.</th>
                    <td class="lesson" data-lesson="1-6" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-6" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-6" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-6" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-6" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-7">
                    <th class="hour">7.</th>
                    <td class="lesson" data-lesson="1-7" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-7" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-7" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-7" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-7" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-8">
                    <th class="hour">8.</th>
                    <td class="lesson" data-lesson="1-8" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-8" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-8" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-8" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-8" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
                <tr id="week-hour-9">
                    <th class="hour">9.</th>
                    <td class="lesson" data-lesson="1-9" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="2-9" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="3-9" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="4-9" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                    <td class="lesson" data-lesson="5-9" data-location="" data-students="<?PHP echo $defaultClassSize; ?>"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
    function showAction(name) {
        var actions = document.getElementsByClassName("action");
        for (var i = 0; i < actions.length; i++) {
            actions[i].style.display = "none";
        }
        
        var action = document.getElementById(name);
        action.style.display = "table";
        if (action.className.indexOf("important") > -1) {
            action.className += " anim";
        }
    }
    
    function hideAction(elem) {
        var actionName = elem.getAttribute("data-action");
        
        var action = document.getElementById(actionName);
        action.style.display = "none";
        if (action.className.indexOf("important") > -1) {
            action.className = action.className.replace("anim", "").trim();
        }
    }
    </script>
    <script>
    function setUpReservationAdder(lessonDate, lessonHour, lessonLocation, preferredAmount) {
        document.getElementById("date").value = lessonDate;
        document.getElementById("hour").value = lessonHour;
        document.getElementById("location").value = lessonLocation;
        document.getElementById("amount").value = preferredAmount;
        document.getElementById("amount").setAttribute("data-value-preferred", preferredAmount);
    }

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
                        schedule.reload();
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
                    schedule.reload();
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
				<form id="reservation-form" action="import/reserve-cb.php" method="post" target="_self" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off" onsubmit="">
					<div class="actionheader">Reserveren</div>
					<div class="actionclose" data-action="reservationadder" onclick="hideAction(this);">&#x2716;</div>
					<table class="actiontable">
						<tr>
							<th>Datum</th>
							<td>
								<input type="date" name="date" id="date" min="<?PHP echo date("Y-m-d"); ?>" placeholder="YYYY-MM-DD" required />
							</td>
                        </tr>
                        <tr>
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
							<th>Lokaal</th>
							<td><input type="text" id="location" name="location" autocomplete="off" placeholder="Voor welk lokaal reserveer je?" size="65" maxlength="6" /></td>
						</tr>
                        <tr>
                            <th>Kar/lokaal</th>
                            <td>
                                <select name="cart" id="cart" required>
                                    <option value="" selected disabled>Selecteer wat je wilt reserveren...</option>
                                    <?PHP
                                        foreach ($carts as $cart) {
                                            ?>
                                            <option value="<?PHP echo $cart["id"]; ?>"<?PHP echo ($cart["available"]?"":" disabled"); ?>><?PHP echo $cart["name"]; ?> (<?PHP echo $cart["dev_type"]; ?>, <?PHP echo $cart["dev_amount"]; ?> stuks)</option>
                                            <?PHP
                                        }
                                    ?>
                                </select>
                                <script>
                                    document.getElementById("cart").addEventListener("change", function(event) {
                                        var amount = document.getElementById("amount");
                                        var maxAmount = 1;
                                        switch (event.target.value) {
                                            <?PHP
                                                foreach ($carts as $cart) {
                                                    ?>
                                                    case "<?PHP echo $cart["id"]; ?>":
                                                        maxAmount = <?PHP echo $cart["dev_amount"]; ?>;
                                                        break;
                                                    <?PHP
                                                }
                                            ?>
                                        }
                                        amount.setAttribute("max", maxAmount);
                                        var valuePreferred = parseInt(amount.getAttribute("data-value-preferred"));
                                        if (maxAmount < valuePreferred) {
                                            amount.value = maxAmount;
                                        }
                                        else {
                                            amount.value = valuePreferred;
                                        }
                                    });
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <th>Aantal<span class="extra-info"> apparaten</span></th>
                            <td><input type="number" name="amount" id="amount" min="1" step="1" max="32" value="32" data-value-preferred="32" placeholder="Het aantal te reserveren apparaten" required /></td>
                        </tr>
						<tr>
							<th>Namens</th>
							<td><input type="text" id="teacher" name="teacher" autocomplete="off" placeholder="Namens wie reserveer je?" size="65" maxlength="32" value="<?PHP echo $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"]; ?>" /></td>
						</tr>
					</table>
					<div class="actionbuttons">
						<input class="button extra" type="button" value="Annuleren" data-action="reservationadder" onclick="hideAction(this);" />
						<input class="button" type="button" value="Reserveer" name="reserve" onclick="reservationSubmit();" />
					</div>
				</form>
			</div>
		</div>
    </div>

    <div class="action important" id="loadingerror" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Er ging iets fout</div>
                <div class="actionclose" data-action="loadingerror" onclick="hideAction(this);">&#x2716;</div>
                <p>Kon het rooster voor de gekozen week niet ophalen. Probeer het later opnieuw.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="loadingerror" onclick="hideAction(this);" />
                </div>
            </div>
        </div>
    </div>

    <div class="action important" id="reservationerror" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Er ging iets fout</div>
                <div class="actionclose" data-action="reservationerror" onclick="hideAction(this); showAction('reservationadder');">&#x2716;</div>
                <p id="reservation-error-msg">Een onbekende fout is opgetreden. Probeer het later opnieuw.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="reservationerror" onclick="hideAction(this); showAction('reservationadder');" />
                </div>
            </div>
        </div>
    </div>

    <div class="action" id="reservationsuccess" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Reservering geplaatst</div>
                <div class="actionclose" data-action="reservationsuccess" onclick="hideAction(this);">&#x2716;</div>
                <p id="reservation-success">Je reservering is geplaatst!</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="reservationsuccess" onclick="hideAction(this);" />
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
                <div class="actionclose" data-action="cancelmessage" onclick="hideAction(this);">&#x2716;</div>
                <p id="cancel-message">De reservering is geannuleerd.</p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Oké" data-action="cancelmessage" onclick="hideAction(this);" />
                </div>
            </div>
        </div>
    </div>

    <div class="action" id="basic-info" style="display: none;">
        <div class="inneraction">
            <div class="actioncontent">
                <div class="actionheader">Informatie</div>
                <div class="actionclose" data-action="basic-info" onclick="hideAction(this);">&#x2716;</div>
                <p><b>Status en locaties:</b></p>
                <table class="action-table">
                    <thead>
                        <tr>
                            <th>Kar/lokaal</th>
                            <th>Type</th>
                            <th>Aantal</th>
                            <th>Beschikbaar</th>
                            <th>Locatie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?PHP
                        foreach ($carts as $cart) {
                            ?>
                                <tr>
                                    <td><?PHP echo $cart["name"]; ?></td>
                                    <td><?PHP echo $cart["dev_type"]; ?></td>
                                    <td><?PHP echo $cart["dev_amount"]; ?></td>
                                    <td><?PHP echo ($cart["available"] ? "ja" : "nee"); ?></td>
                                    <td><?PHP echo $cart["default_location"]; ?></td>
                                </tr>
                            <?PHP
                        }
                        ?>
                    </tbody>
                </table>
                <p style="margin-top: 32px;"><b>Mist er een app op een apparaat?</b></p>
                <p style="font-size: smaller;">Is er een specifieke app nodig voor jouw les? Laat het <?PHP echo $contactName; ?> weten per e-mail:<br><a href="mailto:<?PHP echo $contactEmail; ?>" target="_blank"><?PHP echo $contactEmail; ?></a></p>
                <div class="actionbuttons">
                    <input class="button" type="button" value="Sluiten" data-action="basic-info" onclick="hideAction(this);" />
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

    <div id="loading" style="display: table; width: 100%; height: 100%; position: fixed; top: 0px; left: 0px; right: 0px; bottom: 0px; background-color: rgba(0,0,0,0.8);">
		<div style="display: table-cell; vertical-align: middle;">
			<div style="margin-left: auto; margin-right: auto; text-align: center;">
				<div id="spinner"></div>
			</div>
		</div>
	</div>
    
    <script>
    schedule.init("<?PHP echo $_SESSION["user"]["code"]; ?>").then(function() {
        schedule.getCurrentWeekInfo().then(function(weekInfo) {
            schedule.getAndLoad(weekInfo[0], weekInfo[1]).catch(function(error) {
                showAction('loadingerror');
            });
        });
    });
    </script>
</body>
</html>