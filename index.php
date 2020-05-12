<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Damstede Device Portal</title>
    <?PHP include_once("import/headers.html"); ?>
    <style>
        html, body {
            width: 100%;
            height: 100%;
            max-width: 100%;
            min-width: 270px;
            white-space: nowrap;
            overflow: hidden;
            margin: 0px;
            padding: 0px;
            font-family: Roboto, Verdana, Arial, Sans-Serif;
            background-color: #333333;
            color: #EDEDED;
        }
        #selectortitle {
            display: block;
            width: 100%;
            text-align: center;
            position: fixed;
            top: 40px;
            pointer-events: none;
            font-size: 38px;
        }
        .selector {
            display: inline-table;
            width: 50%;
            height: 100%;
            text-align: center;
        }
        a {
            display: table-cell;
            width: 100%;
            height: 100%;
            vertical-align: middle;
            color: #EDEDED !important;
            text-decoration: none;
            font-size: 32px;
            transition: 0.15s;
        }
        .selectortext {
            text-align: center;
        }
        a:hover, a:focus {
            background-color: #B5131B;
            transition: 0.05s;
        }
    </style>
</head>
<body><h1 id="selectortitle">Ik ben een...</h1><div class="selector"><a href="portal.php"><span class="selectortext">docent</span></a></div><div class="selector"><a href="student.php"><span class="selectortext">leerling</span></a></div></body>
</html>