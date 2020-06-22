<?PHP
    error_reporting(1); ini_set('display_errors', 1);

    session_start();
    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        header("Location: link.php", 301);
        exit();
    }

    require_once("import/db.php");
    $damstedeDB = new DamstedeDB();
    $carts = $damstedeDB->getDeviceCarts();

    $defaultOpeningHours = $damstedeDB->getDefaultOpeningHours($mediatheekId);
    $scheduledOpeningHours = $damstedeDB->getScheduledOpeningHours($mediatheekId);

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
    <title>Damstede Beheer Mediatheek</title>
    <?PHP include_once("import/headers.html"); ?>
    <script src="import/schedule.js"></script>
</head>
<body>
    <header>
        <h1 id="pagetitle"><span class="extra-extra-info">Damstede </span><span class="extra-extra-info">Beheer </span><span>Mediatheek</span></h1>
        <div id="pageoptions">
            <div class="extra-extra-info" id="addreservation" title="Nieuwe reservering aanmaken" onclick="showAction('reservationadder'); setUpReservationAdder('','', '', 32);">+</div>
            <div class="awesome" id="signout" title="Uitloggen (ingelogd als <?PHP echo $_SESSION["user"]["firstName"]." ".$_SESSION["user"]["lastName"]; ?>)" onclick="window.location.href='unlink.php';">&#xf08b;</div>
        </div>
    </header>
    <div id="content">
        <table class="columns">
            <tr>
                <td>
                    <h2>Algemene openingstijden</h2>
                    <hr />

                    <ul class="opening-schedule">
                        <li><b>Maandag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[0]); ?></li>
                        <li><b>Dinsdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[1]); ?></li>
                        <li><b>Woensdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[2]); ?></li>
                        <li><b>Donderdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[3]); ?></li>
                        <li><b>Vrijdag:</b> <?PHP echo openingHoursToText($defaultOpeningHours[4]); ?></li>
                    </ul>
                </td>
                <td>
                    <h2>Afwijkende openingstijden</h2>
                    <hr />

                    <?PHP
                        if (count($scheduledOpeningHours) > 0) {
                            ?>
                            <ul class="opening-schedule">
                                <?PHP foreach ($scheduledOpeningHours as $scheduledDay) { ?>
                                <li><?PHP echo '<b>'.ucfirst($scheduledDay["weekday_text"]).' <span class="date_to_parse">'.$scheduledDay["date"].'</span>:</b> '.openingHoursToText($scheduledDay); ?></li>
                                <?PHP } ?>
                            </ul>
                            <?PHP
                        }
                        else {
                            echo '<p><i>Er zijn momenteel geen toekomstige afwijkende openingstijden ingesteld.</i></p>';
                        }
                    ?>
                </td>
            <tr>
        </table>

        <br/>
        
        <h2>Reserveringen voor vandaag<button id="refresh-btn" style="float: right;" title="Herladen"><div id="refresh-icon" class="awesome">&#xf021;</div></button></h2>
        <hr/>
        
        <div id="reservations">
            <i>Bezig met laden...</i>
        </div>

        <script>
        var resXhr = null;
        var refBtn = document.getElementById("refresh-btn");
        var refIcn = document.getElementById("refresh-icon");
        var resList = document.getElementById("reservations");
        var refInterval = null;
        
        function refreshReservations() {
            clearInterval(refInterval);

            var now = new Date();
            var firstOfJan = new Date(now.getFullYear(), 0, 1);
            var weekNumber = Math.ceil( (((now.getTime() - firstOfJan.getTime()) / 86400000) + firstOfJan.getDay() + 1) / 7 );
            
            refBtn.disabled = true;
            refIcn.className = "awesome fa-spin";

            resXhr = new XMLHttpRequest();
            resXhr.open('GET', 'https://devices.damstede.eu/import/dschedule.php?cart=5&year='+now.getYear()+"&week="+weekNumber);
            resXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            resXhr.onload = function() {
                var response = JSON.parse(resXhr.responseText);
                resList.innerHTML = "";

                if (response["data"].length > 0) {

                }
                else {
                    resList.innerHTML = "<i>Geen reserveringen gevonden.</i>";
                }

                setTimeout(function() {
                    // timeout to make sure it feels as if the loading took a while even though it didn't
                    // especially useful when there are no reservations on the list
                    refBtn.disabled = false;
                    refIcn.className = "awesome";
                    refInterval = setInterval(refreshReservations, 60000);
                }, 600);
            };
            resXhr.send();
        }

        refreshReservations();
        refBtn.addEventListener("click", refreshReservations);
        </script>
    </div>
    <script>
        var datesToParse = document.getElementsByClassName("date_to_parse");
        for (var i = 0; i < datesToParse.length; i++) {
            var date = new Date(datesToParse[i].innerHTML);
            var parsedDate = date.getDate() + " ";
            datesToParse[i].innerHTML = parsedDate + monthNumToName(date.getMonth()+1);
        }
    </script>
</body>
</html>