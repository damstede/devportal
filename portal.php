<?PHP
    session_start();
    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        header("Location: link.php", 301);
        exit();
    }

    require_once("import/db.php");
    $damstedeDB = new DamstedeDB();
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
        <h1 id="pagetitle">Damstede Device Portaal</h1>
        <div id="pageoptions">
            <div id="addreservation" title="Kar reserveren" onclick="showAction('reservationadder'); setUpReservationAdder('','');">+</div>
            <div class="awesome" id="signout" title="Uitloggen (ingelogd als <?PHP echo $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"]; ?>)" onclick="window.location.href='unlink.php';">&#xf08b;</div>
        </div>
    </header>
    <div id="content" class="nopadding">
        <table class="schedule">
            <thead>
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
                    <td class="lesson" data-lesson="1-1"></td>
                    <td class="lesson" data-lesson="2-1"></td>
                    <td class="lesson" data-lesson="3-1"></td>
                    <td class="lesson" data-lesson="4-1"></td>
                    <td class="lesson" data-lesson="5-1"></td>
                </tr>
                <tr id="week-hour-2">
                    <th class="hour">2.</th>
                    <td class="lesson" data-lesson="1-2"></td>
                    <td class="lesson" data-lesson="2-2"></td>
                    <td class="lesson" data-lesson="3-2"></td>
                    <td class="lesson" data-lesson="4-2"></td>
                    <td class="lesson" data-lesson="5-2"></td>
                </tr>
                <tr id="week-hour-3">
                    <th class="hour">3.</th>
                    <td class="lesson" data-lesson="1-3"></td>
                    <td class="lesson" data-lesson="2-3"></td>
                    <td class="lesson" data-lesson="3-3"></td>
                    <td class="lesson" data-lesson="4-3"></td>
                    <td class="lesson" data-lesson="5-3"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-4">
                    <th class="hour">4.</th>
                    <td class="lesson" data-lesson="1-4"></td>
                    <td class="lesson" data-lesson="2-4"></td>
                    <td class="lesson" data-lesson="3-4"></td>
                    <td class="lesson" data-lesson="4-4"></td>
                    <td class="lesson" data-lesson="5-4"></td>
                </tr>
                <tr id="week-hour-5">
                    <th class="hour">5.</th>
                    <td class="lesson" data-lesson="1-5"></td>
                    <td class="lesson" data-lesson="2-5"></td>
                    <td class="lesson" data-lesson="3-5"></td>
                    <td class="lesson" data-lesson="4-5"></td>
                    <td class="lesson" data-lesson="5-5"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-6">
                    <th class="hour">6.</th>
                    <td class="lesson" data-lesson="1-6"></td>
                    <td class="lesson" data-lesson="2-6"></td>
                    <td class="lesson" data-lesson="3-6"></td>
                    <td class="lesson" data-lesson="4-6"></td>
                    <td class="lesson" data-lesson="5-6"></td>
                </tr>
                <tr id="week-hour-7">
                    <th class="hour">7.</th>
                    <td class="lesson" data-lesson="1-7"></td>
                    <td class="lesson" data-lesson="2-7"></td>
                    <td class="lesson" data-lesson="3-7"></td>
                    <td class="lesson" data-lesson="4-7"></td>
                    <td class="lesson" data-lesson="5-7"></td>
                </tr>
                <tr id="week-hour-8">
                    <th class="hour">8.</th>
                    <td class="lesson" data-lesson="1-8"></td>
                    <td class="lesson" data-lesson="2-8"></td>
                    <td class="lesson" data-lesson="3-8"></td>
                    <td class="lesson" data-lesson="4-8"></td>
                    <td class="lesson" data-lesson="5-8"></td>
                </tr>
                <tr id="week-hour-9">
                    <th class="hour">9.</th>
                    <td class="lesson" data-lesson="1-9"></td>
                    <td class="lesson" data-lesson="2-9"></td>
                    <td class="lesson" data-lesson="3-9"></td>
                    <td class="lesson" data-lesson="4-9"></td>
                    <td class="lesson" data-lesson="5-9"></td>
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
    }
    
    function hideAction(elem) {
        var actionName = elem.getAttribute("data-action");
        
        var action = document.getElementById(actionName);
        action.style.display = "none";
    }
    </script>
    <script>
    function setUpReservationAdder(lessonDate, lessonHour) {
        document.getElementById("date").value = lessonDate;
        document.getElementById("hour").value = lessonHour;
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
                    reservationError.innerHTML = "Serverfout "+xhr.statusText+". Probeer het later opnieuw.";
                    showAction('reservationerror');
                }
                else {
                    var loginResponse = JSON.parse(xhr.responseText);
                    switch (loginResponse.type) {
                        case "success": {
                            schedule.reload();
                            showAction('reservationsuccess');
                            break;
                        }
                        default: {
                            reservationError.innerHTML = loginResponse.message;
                            showAction('reservationerror');
                            break;
                        }
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
					<div class="actionheader">Kar reserveren</div>
					<div class="actionclose" data-action="reservationadder" onclick="hideAction(this);">&#x2716;</div>
					<table class="actiontable">
						<tr>
							<th>Datum</th>
							<td>
								<input type="date" name="date" id="date" min="<?PHP echo date("Y-m-d"); ?>" required />
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
                            <th>Kar</th>
                            <td>
                                <select name="cart" id="cart" required>
                                    <option value="" selected disabled>Selecteer een kar...</option>
                                    <?PHP
                                        $carts = $damstedeDB->getDeviceCarts();
                                        foreach ($carts as $cart) {
                                            ?>
                                            <option value="<?PHP echo $cart["id"]; ?>"<?PHP echo ($cart["available"]?"":" disabled"); ?>>Kar <?PHP echo $cart["id"]; ?> (<?PHP echo $cart["dev_type"]; ?>, <?PHP echo $cart["dev_amount"]; ?> stuks)</option>
                                            <?PHP
                                        }
                                    ?>
                                </select>
                            </td>
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

    <div class="action" id="reservationerror" style="display: none;">
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

    <div id="loading" style="display: table; width: 100%; height: 100%; position: fixed; top: 0px; left: 0px; right: 0px; bottom: 0px; background-color: rgba(0,0,0,0.8);">
		<div style="display: table-cell; vertical-align: middle;">
			<div style="margin-left: auto; margin-right: auto; text-align: center;">
				<div id="spinner"></div>
			</div>
		</div>
	</div>
    
    <script>
    schedule.init().then(function() {
        schedule.getCurrentWeekInfo().then(function(weekInfo) {
            schedule.get(weekInfo[0], weekInfo[1]).then(function(reservations) {
                schedule.load(reservations);
            });
        });
    });
    </script>
</body>
</html>