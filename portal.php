<?PHP
    session_start();
    if (!isset($_SESSION["zermelo_access_token"]) || empty($_SESSION["zermelo_access_token"])) {
        header("Location: link.php", 301);
        exit();
    }
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
            <div class="awesome" id="signout" title="Uitloggen" onclick="window.location.href='unlink.php';">&#xf08b;</div>
        </div>
    </header>
    <div id="content">
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
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-2">
                    <th class="hour">2.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-3">
                    <th class="hour">3.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-4">
                    <th class="hour">4.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-5">
                    <th class="hour">5.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr>
                    <th class="hour break"></th>
                    <td class="lesson break" colspan="5"></td>
                </tr>
                <tr id="week-hour-6">
                    <th class="hour">6.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-7">
                    <th class="hour">7.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-8">
                    <th class="hour">8.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
                <tr id="week-hour-9">
                    <th class="hour">9.</th>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                    <td class="lesson"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <script>
    schedule.getCurrentWeekInfo().then(function(weekInfo) {
        schedule.get(weekInfo[0], weekInfo[1]).then(function(reservations) {
            schedule.load(reservations);
        });
    });
    </script>
</body>
</html>