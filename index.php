<?php
$baseUrl = "https://psyfi.helaaspindakaas.xyz/";

ob_start();
require('data_lectures.php');
require('data_music.php');
require('functions.php');

$allActs = massageAllData($musicRaw, $lecturesRaw);
$allStages = getAllStages($allActs);


ob_clean();

if (strlen($_REQUEST['stage']) > 1) {
    emitIcalForEvents($allActs, $_REQUEST['stage']);
} else {
    $curTS = time();// + (24 * 60 * 60 * 16) + (9329 * 4);
    $curTS_fmt = strftime("[%a]%H:%M", $curTS);


    $status = get3ActsByStage($allActs, $allStages, $curTS);
    header('Content-type: text/html');
    ?>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

        <meta property="og:url" content="<?= $baseUrl ?>"/>
        <meta property="og:type" content="article"/>
        <meta property="og:title" content="Psy-Fi 2019 Timetables and Calendars"/>
        <meta property="og:description"
              content="Psy-Fi 2019 now playing, next playing, weather, import iCal ICS Google Calendar Outlook"/>
        <meta property="og:image" content="<?= $baseUrl ?>img/googlecal.png"/>
        <meta property="og:image:width" content="1776"/>
        <meta property="og:image:height" content="772"/>


        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Psy-Fi 2019 timetable // calendar</title>
        <style type="text/css">
            body {
                font-family: sans-serif;
                margin: 0;
                padding: 0;
            }

            .showoff {
                max-width: 1500px;
                width: 90%;
                border-radius: 2em;
                border: 2px solid silver;
            }

            table.nowtable {
                border: 1px solid silver;
            }

            table.nowtable thead {
                background-color: silver;
                font-weight: bold;
            }

            table.nowtable td.stageline {
                background-color: silver;
                word-break: break-all;
            }

            span.actname {
                font-weight: bolder;
                word-break: break-word;
            }

            span.acttime {
                font-style: normal;
                word-break: break-word;
            }

        </style>
    </head>
    <body>

    <img lazy="true" src="img/logo.webp" width="255" height="135" style="background-color: black; border-radius: 3em;"/>

    <h2>Psy-Fi 2019 timetable // calendar</h2>


    <h3>Right now: <?= $curTS_fmt ?></h3>

    <table class="nowtable" border="2" cellpadding="2" cellspacing="0">
        <thead>
        <tr>
            <td>Stage</td>
            <td>Now</td>
            <td>Next</td>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach ($status as $stage => $data) {
            if ($stage === "MUSHROOM") continue;
            ?>
            <tr>
                <td class="stageline"><?= $stage ?></td>
                <?= show3Data($data['now']) ?>
                <?= show3Data($data['next']) ?>
            </tr>
            <?php
        }

        ?>

        </tbody>
    </table>

    <h2>Weather</h2>

    <iframe lazy="true" style="border-radius: 2em"
            src="https://gadgets.buienradar.nl/gadget/zoommap/?lat=53.21548&lng=5.87248&overname=2&zoom=11&naam=8926XK&size=2b&voor=0"
            noresize scrolling=no width=330 height=330 frameborder=no></iframe>

    <iframe lazy="true" src="//gadgets.buienradar.nl/gadget/forecastandstation/6270/" noresize scrolling=no hspace=0
            vspace=0
            frameborder=0 marginheight=0 marginwidth=0 width=300 height=190></iframe>


    <h2>Adding to your own calendar</h2>

    Below there are links to each stage's timetable in iCal (ICS) format.
    You can import it in any calendar app you want. I recommend adding each stage as a separate calendar, but there is
    also an all-stage calendar which is quite messy.

    <h3>Outlook</h3>
    Just click on the links below; download the ICS file and import into Outlook.<br/>
    Or, use the "Add Calendar" &gt; "From Internet" and paste the URL from the below list.


    <h3>Google Calendar</h3>
    Go to your Google Calendar and add a calendar by URL. <a
            href="https://calendar.google.com/calendar/r/settings/addbyurl" target="_blank">It will take you
        here</a>.<br/>
    Paste the URL from the below list.
    It will auto-update every 60 minutes!

    <h3>Calendars by stage</h3>
    <ul>
        <?php

        foreach ($allStages as $stage) {
            $url = "{$baseUrl}?stage=" . urlencode($stage);
            ?>
            <li><a href="<?php echo $url ?>"><?php echo $stage; ?></a>: <span
                        style="font-family: monospace"><?php echo $url ?></span></li>
            <?php

        }
        ?>

        <?php
        $url = "{$baseUrl}?stage=" . urlencode("ALL");
        ?>
        <li><a href="<?php echo $url ?>">All stages (bit confusing)</a>: <span
                    style="font-family: monospace"><?php echo $url ?></span></li>

    </ul>

    <h2>Backing Spreadsheet</h2>
    All the data here was gathered from Psy-Fi's posts and put into a spreadsheet.
    Access it here: <a
            href="https://docs.google.com/spreadsheets/d/14t8JLlqgyCOQul7tdf9Flb-yA4sj2lEHVkKM2z3HK7Q/edit?usp=sharing"
            target="_blank">PsyFi 2019 Timetables Google Sheets</a>


    <h2>You are gonna miss stuff, it's insane</h2>
    <img lazy="true" src="img/googlecal.png" class="showoff"/>
    <img lazy="true" src="img/schedule.png" class="showoff"/>
    <img lazy="true" src="img/outlook.png" class="showoff"/>

    </body>
    </html>
    <?php
}

function show3Data($act) {
    if (!$act) {
        return "<td><i>nothing right now</i></td>";
    }

    return "<td>"
        . "<span class='actname'>" . $act['what'] . "</span>" . "<br/>"
        . "<span class='acttime'>" . showTimespan($act['ts_start'], $act['ts_end']) . "</span>"
        . "</td>";
}

function showTimespan($startObj, $endObj) {
    $dayStart = date('d', $startObj['ts']); // 1-31
    $dayEnd = date('d', $endObj['ts']); // 1-31


    // First and easiest case is if both are on the same day...
    if ($dayStart == $dayEnd) {
        return strftime("[%a]%H:%M", $startObj['ts']) . "-" . strftime("%H:%M", $endObj['ts']);
    }

    // If not on the same day gotta indicate both.
    return strftime("[%a]%H:%M", $startObj['ts']) . "-" . strftime("%H:%M[%a]", $endObj['ts']);


}