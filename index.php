<?php
$baseUrl = "https://psyfi.helaaspindakaas.xyz/";

require('clashfinder_data.php');
require('functions.php');

ob_start();

$allActs = getAllActsFromClashFinder();
$allStages = getAllStages($allActs);

ob_clean();


if (@strlen($_REQUEST['stage']) > 1) {
    emitIcalForEvents($allActs, $_REQUEST['stage']);
} else {
    $curTS = time() + (24 * 60 * 60 * 16) + (9329 * 4);
    $curTS = time();

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
                font-family: 'Roboto', sans-serif;
                background-color: black;
                color: white;
                margin: 0;
                padding: 0;
            }

            a {
                color: aqua;
            }

            .showoff {
                max-width: 1500px;
                width: 90%;
                border-radius: 1em;
                border: 2px solid darkgray;
            }

            td.url {
                font-family: 'Roboto Condensed', sans-serif;
            }

            table.nowtable {
                font-family: 'Roboto Condensed', sans-serif;
                border: 1px solid gray;
            }

            table.nowtable thead {
                font-weight: bold;
            }

            table.nowtable td.stageline {
                color: yellow;
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

            .logo {
                background-color: black;
                border-radius: 3em;
            }
        </style>
        <script type="text/javascript">
            WebFontConfig = {
                google: {families: ['Roboto', 'Roboto Condensed']}
            };
            (function () {
                var wf = document.createElement('script');
                wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
                    '://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js';
                wf.type = 'text/javascript';
                wf.async = 'true';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(wf, s);
            })();
            // ]]>
        </script>
    </head>
    <body>

    <img align="right" lazy="true" src="img/logo.webp" width="255" height="135" class="logo"/>

    <h2>Psy-Fi 2019 timetable // calendar</h2>


    <h3>Right now: <?= $curTS_fmt ?></h3>

    <table class="nowtable" border="2" cellpadding="3" cellspacing="0">
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

    <!--    <h2>Weather</h2>

        <iframe lazy="true" style="border-radius: 2em"
                src="https://gadgets.buienradar.nl/gadget/zoommap/?lat=53.21548&lng=5.87248&overname=2&zoom=11&naam=8926XK&size=2b&voor=0"
                noresize scrolling=no width=330 height=330 frameborder=no></iframe>

        <iframe lazy="true" src="//gadgets.buienradar.nl/gadget/forecastandstation/6270/" noresize scrolling=no hspace=0
                vspace=0 style="border-radius: 2em"
                frameborder=0 marginheight=0 marginwidth=0 width=300 height=190></iframe>

    -->

    <hr/>

    <h3>Calendars</h3>

    <table border="1" cellpadding="2" cellspacing="0">
        <thead>
        <tr>
            <td>Stage</td>
            <td>Add to Google</td>
            <td>iCal/Outlook</td>
            <td>URL</td>
        </tr>
        </thead>
        <tbody>

        <?php

        foreach ($allStages as $stage) {
            $url = "{$baseUrl}?stage=" . urlencode($stage);
            $webCal = str_replace("https://", "webcal://", $url) /*. "&added_inside=" . time()*/
            ;
            ?>
            <tr>
                <td><?= $stage ?></td>
                <td>
                    <a href="https://www.google.com/calendar/render?cid=<?= urlencode($webCal) ?>&added_at=<?= time() ?>"
                       target="gcalendar"><?= "Add $stage to Google Calendar" ?></a></td>
                <td><a href="<?= $url ?>"><?= $stage ?></a></td>
                <td class="url"><?= $url ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <?php
    $url = "{$baseUrl}?stage=" . urlencode("ALL");
    ?>
    <br/><br/>
    Also, there's this version with all stages in a single calendar: <a href="<?php echo $url ?>">All stages (bit
        confusing)</a>.

    <hr/>

    <h2>Adding to your own calendar</h2>
    Above are links to each stage's timetable in iCal (ICS) format.
    You can import it in any calendar app you want.
    I recommend adding each stage as a separate calendar (so each has its own color etc), but there is also an all-stage
    calendar which is quite messy.

    <h4>Google Calendar</h4>
    Click on the column "Add to Google" above, one for each stage. Each time, a new tab/window will open in Google
    Calendar, and you just click "Yes" or "Add" in the confirmation dialog. Google will take a few seconds after that to
    display the calendar.
    It will auto-update every 60 minutes, so you're always set!
    Unfortunately Google will take a while to read the calendar's Title, and will instead show the webcal://xxx URL for
    a while. It should fix itself in a few hours.

    <h4>Outlook</h4>
    Just click on the links above; download the ICS file and import into Outlook.<br/>
    Or, use the "Add Calendar" &gt; "From Internet" and paste the URL from the list above; this way it will auto-update
    as well.

    <h2>Backing Data</h2>
    All the backing data is stored in ClashFinder.
    Thanks to the people who started it and keep it updated.
    Check it out: <a href="https://clashfinder.com/s/psyfiseedsofscience/" target="clashfinder">Psy Fi Seeds of Science
        Holland 2019 Clashfinder</a>. You can also check all the <a
            href="https://clashfinder.com/l/psyfiseedsofscience/?revs" target="clashfinder">changes</a> made over time.


    <hr/>

    <h2>It will look like this...</h2>

    <img lazy="true" src="img/outlook-min.png" class="showoff"/>
    <img lazy="true" src="img/gcal-min.png" class="showoff"/>


    <br/>
    <br/>
    <hr>
    <br/>
    <br/>
    by <a href="mailto:ricardo@pardini.net">rpardini</a>


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