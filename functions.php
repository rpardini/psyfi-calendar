<?php

function scriptTagWithInlineScript($scriptFile)
{
    return "<script type=\"text/javascript\">" . file_get_contents($scriptFile) . "</script>";
}

function getSiteTitle()
{
    if ($_SERVER['HTTP_HOST'] === "psyfi.helaaspindakaas.xyz") {
        return "Psy-Fi 2019";
    }
    return "Psy-Fi 2019 STG";
}

function data_uri($file, $mime)
{
    $contents = file_get_contents($file);
    $base64 = base64_encode($contents);
    return ('data:' . $mime . ';base64,' . $base64);
}

function cacheBusterLink($file)
{
    $baseUrl = "https://" . $_SERVER['HTTP_HOST'] . "/";
    //if (!file_exists($file)) throw new Exception("File $file does not exist!");
    $mtime = filemtime($file);
    //if ($mtime === false) throw new Exception("Could not get mtime for file $file");
    $md5 = md5($mtime);
    return $baseUrl . $file . "?cb=" . $md5;
}

function get3ActsByStage($allActs, $allStages, $curTS)
{
    $byStage = splitByStageOrderByTs($allActs, $allStages);

    $stages3 = array();
    foreach ($allStages as $stage) {
        $stageActs = find3ForStage($byStage[$stage], $curTS, $stage);
        $stages3[$stage] = $stageActs;
    }

    return $stages3;
}

function find3ForStage($acts, $curTS, $stage)
{
    // Find the first act that has ts_start and ts_end between $curTS -- that is the the 2o.
    $found = -1;
    $exact = false;
    $i = 0;
    foreach ($acts as $act) {
        $start = $act['ts_start']['ts'];
        $end = $act['ts_end']['ts'];
        if ($curTS > $start) {
            $found = $i;
            if ($curTS < $end) {
                $exact = true;
                break;
            }
        }
        $i++;
    }

    //echo "-- Stage: $stage -- Found: $found\n";

    $before = @$acts[$found - 1];
    $now = $exact ? $acts[$found] : null;
    $next = @$acts[$found + 1];

    return array('before' => $before, 'now' => $now, 'next' => $next);

}

function splitByStageOrderByTs($allActs, $allStages)
{

    $byStage = [];

    foreach ($allActs as $act) {
        $stage = $act['where'];
        if (@!$byStage[$stage]) {
            $byStage[$stage] = [];
        }
        $byStage[$stage][] = $act;
    }

    foreach ($allStages as $stage) {
        $onTheStage = $byStage[$stage];

        usort($onTheStage, function ($a, $b) {
            $aStart = $a['ts_start']['ts'];
            $bStart = $b['ts_start']['ts'];
            if ($aStart == $bStart) {
                return 0;
            }
            return ($aStart < $bStart) ? -1 : 1;
        });

        $byStage[$stage] = $onTheStage;
    }

    return $byStage;
}


function getAllStages($allData)
{
    $stages = [];

    foreach ($allData as $data) {
        $where = ($data['where']);
        $stages[$where] = true;
    }
    return array_keys($stages);
}


/**
 * @param array $allActs
 * @param $filterWhere
 */
function emitIcalForEvents(array $allActs, $filterWhere)
{
    $begin = <<<EOD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-// clashfinder.com
X-WR-CALNAME;VALUE=TEXT:PsyFi 2019 $filterWhere
TIMEZONE-ID:Europe/Amsterdam
X-WR-TIMEZONE:Europe/Amsterdam
REFRESH-INTERVAL;VALUE=DURATION:PT01H
X-PUBLISHED-TTL:PT01H
CALSCALE:GREGORIAN
METHOD:PUBLISH

EOD;

    $end = <<<EOD
END:VCALENDAR

EOD;

    header('Content-Type: text/calendar; charset=UTF-8');
    header("Content-Disposition: attachment; filename=psyfi2019" . strtolower(str_replace(" ", "", $filterWhere)) . ".ics;");

    echo fixIcalLineTerm($begin);

    foreach ($allActs as $act) {
        if (($act['where'] === $filterWhere) || ("ALL" === $filterWhere))
            emitICal($act);
    }

    echo fixIcalLineTerm($end);
}

function icalTxt($txt)
{
    $result = preg_replace("/[^a-zA-Z0-9\ ]+/", "", $txt);
    $result = str_replace("  ", " ", $result);
    $result = str_replace("  ", " ", $result);
    $result = str_replace("  ", " ", $result);
    return $result;
}

function emitIcal($a)
{
    $dtStart = convertIcalDate($a['ts_start']['ts']);
    $dtEnd = convertIcalDate($a['ts_end']['ts']);
    $uid = md5($a['what'] . $a['where']) . "@psy-fi.nl";
    $what = icalTxt($a['what']);
    $where = icalTxt($a['where']);

    $vCal = <<<EOD
BEGIN:VEVENT
UID:${uid}
DTSTAMP:19971210T080000Z
SUMMARY:$what
DTSTART:${dtStart}
DTEND:${dtEnd}
LOCATION:$where
DESCRIPTION:$what (at $where)
END:VEVENT

EOD;
    echo fixIcalLineTerm($vCal);


}

function fixIcalLineTerm($txt)
{
    return str_replace("\n", "\r\n", $txt);
}

function convertIcalDate($dt)
{
    $dt = ($dt - 60 * 60 * 2); // remove 2 hours for UTC time... supposedly ;-)
    //$dt = strtotime($daystr . " " . $timestr);
    //print_r($dt);
    return date("Ymd", $dt) . "T" . date("His", $dt) . "Z";


}