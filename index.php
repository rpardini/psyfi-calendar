<?php
$isMobile = preg_match("/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile/", $_SERVER['HTTP_USER_AGENT']);
$baseUrl = "https://psyfi.helaaspindakaas.xyz/";
$autoEnableFluid = !$isMobile;
$enableExternalFont = true;
$vid = urlencode(md5(file_get_contents('index.php') . file_get_contents('js/pwa.js') . file_get_contents('js/fluid-config.js')));
$sid = urlencode(md5(file_get_contents('styles.css') . $vid));
$mid = urlencode(md5(file_get_contents('manifest.json') . $vid));
$lid = urlencode(md5(file_get_contents('img/logo.main.png') . $vid));
require('clashfinder_data.php');
require('functions.php');

ob_start();

$allActs = getAllActsFromClashFinder();
$allStages = getAllStages($allActs);

ob_clean();

if (@strlen($_REQUEST['stage']) > 1) {
    emitIcalForEvents($allActs, $_REQUEST['stage']);
} else {
    $curTS = 1567189119; //time() + (24 * 60 * 60 * 16) + (9329 * 2);
    //if (@!$_REQUEST['fake']) $curTS = time();

    $curTS_fmt = strftime("[%a]%H:%M:%S", $curTS);

    $status = get3ActsByStage($allActs, $allStages, $curTS);
    header('Content-type: text/html');
    ?>
    <html lang="en">
    <head>
        <title>Psy-Fi 2019</title>
        <link rel="manifest" href="manifest.json?mid=<?= $mid ?>"/>
        <meta name="theme-color" content="#0c1d2d"/>
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Psy-Fi 2019">
        <link rel="apple-touch-icon" href="img/pwa/icon-192x192.png">

        <link href="img/splashscreens/iphone5_splash.png"
              media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/iphone6_splash.png"
              media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/iphoneplus_splash.png"
              media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/iphonex_splash.png"
              media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/iphonexr_splash.png"
              media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/iphonexsmax_splash.png"
              media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/ipad_splash.png"
              media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/ipadpro1_splash.png"
              media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/ipadpro3_splash.png"
              media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>
        <link href="img/splashscreens/ipadpro2_splash.png"
              media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)"
              rel="apple-touch-startup-image"/>

        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta property="og:url" content="<?= $baseUrl ?>"/>
        <meta property="og:type" content="article"/>
        <meta property="og:title" content="Psy-Fi 2019 Timetables and Calendars"/>
        <meta property="og:description"
              content="Psy-Fi 2019 now playing, next playing, weather, import iCal ICS Google Calendar Outlook"/>
        <meta property="og:image" content="<?= $baseUrl ?>img/shortgcal-min.png"/>
        <meta property="og:image:width" content="876"/>
        <meta property="og:image:height" content="479"/>
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <?php
        if ($enableExternalFont) {
            ?>
            <link href="https://fonts.googleapis.com/css?family=Barlow&display=swap" rel="stylesheet">
            <?php
        }
        ?>

        <link rel="stylesheet" type="text/css" href="styles.css?sid=<?= $sid ?>">
        <?= scriptTagWithInlineScript('js/pwa.js') ?>
    </head>
    <body>

    <canvas></canvas>

    <header>
        <div class="logo"><img src="img/logo.main.png?lid=<?= $lid ?>" width="233" height="132"
                               loading="eager" alt="PsyFi 2019"/></div>
    </header>

    <section id="timetable">
        <h2>Now and Next&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $curTS_fmt ?></h2>

        <?php
        foreach ($status as $stage => $data) {
            ?>
            <dl>
                <dt>Stage</dt>
                <dd class="stageTitle"><?= $stage ?></dd>
                <dt class="act">Now</dt>
                <dd><?= show3Data($data['now']) ?></dd>
                <dt class="act">Next</dt>
                <dd><?= show3Data($data['next']) ?></dd>
            </dl>
            <?php
        }
        ?>
    </section>

    <section id="weather">
        <h2>Weather</h2>
        <div class="weather">
            <!--            <iframe src="https://gadgets.buienradar.nl/gadget/weathersymbol" noresize scrolling=no hspace=0 vspace=0
                                loading="lazy"
                                frameborder=0 marginheight=0 marginwidth=0 width=50 height=40></iframe>
            -->        </div>
        <div class="content">
            <a class="button" href="https://www.buienradar.nl/weer/tytsjerk/nl/2746311/14daagse" target="buien">
                Coming soon! Go check weather yourself for now
            </a>
        </div>
    </section>

    <section id="calendars">
        <h2>Lineup / Timetable / Calendars</h2>

        <?php
        foreach ($allStages as $stage) {
            $url = "{$baseUrl}?stage=" . urlencode($stage);
            $webCal = str_replace("https://", "webcal://", $url);
            ?>
            <dl class="dl--wide">
                <dt>Stage</dt>
                <dd><?= $stage ?></dd>
                <dt>Google</dt>
                <dd><a class="button"
                       href="https://www.google.com/calendar/render?cid=<?= urlencode($webCal) ?>&added_at=<?= time() ?>"
                       target="gcalendar">Add <?= $stage ?> to Google</a></td></dd>
                <dt>iCal/Outlook</dt>
                <dd><a class="button" href="<?= $url ?>">Get <?= $stage ?> iCal</a></dd>
            </dl>
            <?php
        }
        ?>
    </section>

    <section id="text">
        <h2>Adding to your own calendar</h2>
        <div class="content">
            <p>Above are links to each stage's timetable in iCal (ICS) format.</p>
            <p>You can import it in any calendar app you want.</p>
            <p>I recommend adding each stage as a separate calendar (so each has its own color etc), but there is also
                an <a href="#single">all-stages calendar</a> which is quite messy.</p>

            <h3>Google Calendar</h3>
            <p>Click on the "add" of the corresponding stage. Each time, a new tab/window will open in Google Calendar,
                and you just click "Yes" or "Add" in the confirmation dialog. Google will take a few seconds after that
                to display the calendar.</p>
            <p>It will auto-update every 60 minutes, so you're always set!</p>
            <p>Unfortunately Google will take a while to read the calendar's Title, and will instead show the
                webcal://xxx URL for a while. It should fix itself in a few hours.</p>

            <h3>Outlook</h3>
            <p>Just click on the links above; download the ICS file and import into Outlook.</p>
            <p>Or, use the "Add Calendar" &gt; "From Internet" and paste the URL from the link above (click and hold to
                copy); this way it will auto-update as well.</p>

            <h3>It will look like this...</h3>

            <img loading="lazy" src="img/shortgcal-min.png" width="876" height="479"/>
        </div>
    </section>

    <section id="clashfinder">
        <h2>Backing Data</h2>
        <div class="content">
            <p>All the backing data is stored in ClashFinder.</p>
            <p>Thanks to the people who started it and keep it updated.</p>
            <p>Check it out: <a href="https://clashfinder.com/s/psyfiseedsofscience/" target="clashfinder">Psy Fi Seeds
                    of Science Holland 2019 Clashfinder</a>.</p>
            <p>You can also check all the <a href="https://clashfinder.com/l/psyfiseedsofscience/?revs"
                                             target="clashfinder">changes</a> made over time.</p>
        </div>


    </section>

    <section id="single">
        <h2>All-stages calendar</h2>

        <div class="content">
            <?php
            $url = "{$baseUrl}?stage=" . urlencode("ALL");
            ?>
            <p>Also, there's this version with all stages in a single calendar: <a href="<?php echo $url ?>">All stages
                    (bit confusing)</a>.</p>
        </div>
    </section>

    <section id="install">
        <a class="button" href="javascript:installPwa()" onclick="installPwa()">Install App</a>
    </section>


    <footer>
        by <a href="mailto:ricardo@pardini.net">rpardini</a> & <a href="mailto:dine@dine.tk">dine</a> 💚️
    </footer>

    <?php
    if ($autoEnableFluid) {
        ?>
        <?= scriptTagWithInlineScript('js/fluid-config.js') ?>
        <script async src="fluid/script.js?vid=<?= $vid ?>"></script>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
}

function show3Data($act) {
    if (!$act) {
        return "--";
    }

    return "<div class='act'>"
        . "<span class='acttime'>" . showTimespan($act['ts_start'], $act['ts_end']) . "</span>"
        . "<span class='actname'>" . $act['what'] . "</span>"
        . "</div>";
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