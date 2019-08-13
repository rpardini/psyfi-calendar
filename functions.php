<?php


function get3ActsByStage($allActs, $allStages, $curTS) {
    $byStage = splitByStageOrderByTs($allActs, $allStages);

    $stages3 = array();
    foreach ($allStages as $stage) {
        $stageActs = find3ForStage($byStage[$stage], $curTS, $stage);
        $stages3[$stage] = $stageActs;
    }

    return $stages3;
}

function find3ForStage($acts, $curTS, $stage) {
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

    $before = $acts[$found - 1];
    $now = $exact ? $acts[$found] : null;
    $next = $acts[$found + 1];

    return array('before' => $before, 'now' => $now, 'next' => $next);

}

function splitByStageOrderByTs($allActs, $allStages) {

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


/**
 * @param $musicRaw
 * @param $lecturesRaw
 * @return array
 */
function massageAllData($musicRaw, $lecturesRaw) {
    $allDebug = true;
    $debug = $allDebug;
    $debug2 = $allDebug;
    $debug3 = $allDebug;
    $debug4 = $allDebug;
    setlocale(LC_ALL, 'en_US');

    if ($debug || $debug2 || $debug3 || $debug4) header("Content-type: text/plain");


    if ($debug3) echo "------------ MUSIC ---------------------\n";

// Music, in 30-min time slots. tricky
    $lines = explode("\n", $musicRaw);
    $i = 0;
    $musicSlots = array();
    foreach ($lines as $line) {
        $i++;
        if ($i == 1) continue; // skip header
        $cols = explode("\t", $line);
        if ($debug3) print_r($cols);
        $musicSlots[] = $cols;
    }


    if ($debug) echo "------------ LECTURES ---------------------\n";


// Lectures...
    $lines = explode("\n", $lecturesRaw);
    $i = 0;
    $lectureActs = array();
    foreach ($lines as $line) {
        $i++;
        if ($i == 1) continue; // skip header
        $cols = explode("\t", $line);
        if ($debug3) print_r($cols);
        $lectureActs[] = parseDateTime($cols[0], $cols[1], $cols[2], $cols[3], $cols[4]);
    }

    if ($debug) print_r($lectureActs);


// Now; massage the music slots into single attractions, by act name + stage.
    $musicActsSlots = [];
    foreach ($musicSlots as $musicSlot) {
        $actNameStage = trim($musicSlot[2]) . " (at " . trim($musicSlot[3]) . ")";
        if (@!$musicActsSlots[$actNameStage]) {
            $musicActsSlots[$actNameStage] = [];
        }
        $musicActsSlots[$actNameStage][] = parseDateTime($musicSlot[0], $musicSlot[6], $musicSlot[7], $musicSlot[2], $musicSlot[3]);
    }

    if ($debug2) echo "------------ MUSIC SLOTS BY ARTIST/STAGE ---------------------\n";
    if ($debug2) print_r($musicActsSlots);

// Now, massage the single attractions + slots into a single act

    $allActs = $lectureActs;
    foreach ($musicActsSlots as $act => $slots) {

        $low_ts = 32178132879312897;
        $high_ts = 0;
        $low_tsfull = null;
        $high_tsfull = null;
        $oneSlot = null;
        foreach ($slots as $slot) {
            $start = $slot['ts_start']['ts'];
            $end = $slot['ts_end']['ts'];
            if ($end > $high_ts) {
                $high_ts = $end;
                $high_tsfull = $slot['ts_end'];
            }
            if ($start < $low_ts) {
                $low_ts = $start;
                $low_tsfull = $slot['ts_start'];
            }
            $oneSlot = $slot;
        }
        $oneSlot['ts_start'] = $low_tsfull;
        $oneSlot['ts_end'] = $high_tsfull;

        $allActs[] = $oneSlot;
    }

    if ($debug4) print_r($allActs);
    return $allActs;
}

function getAllStages($allData) {
    $stages = [];

    foreach ($allData as $data) {
        $where = ($data['where']);
        $stages[$where] = true;
    }
    return array_keys($stages);
}


function parseTxtDate($txtDate, $txtStartHour) {
    global $debug2;
    $partesDate = explode(", ", trim($txtDate));
    $goodDate = $partesDate[1];
    $goodDate = str_replace(" August ", "/08/", $goodDate);
    $goodDate = str_replace(" September ", "/09/", $goodDate);
    $str = $goodDate . " " . trim($txtStartHour);
    $ts_startObj = date_create_from_format("j/n/Y G:i:s", $str);
    if ($ts_startObj === false) throw new Exception("Could not parse date: $str");
    $ts_start = $ts_startObj->getTimestamp();
    $reformat = strftime("%d/%a[%H:%M]", $ts_start);
    if ($debug2) echo "-- Date: $txtDate $txtStartHour  (TS: $ts_start) (Reformat: $reformat)\n";
    return array('ts' => $ts_start, 'format' => $reformat, 'obj' => $ts_startObj);
}

function parseDateTime($txtDate, $txtStartHour, $txtEndHour, $what, $where) {
    return array('ts_start' => parseTxtDate($txtDate, $txtStartHour), 'ts_end' => parseTxtDate($txtDate, $txtEndHour), 'what' => trim($what), 'where' => strtoupper(trim($where)));
}


/**
 * @param array $allActs
 * @param $filterWhere
 */
function emitIcalForEvents(array $allActs, $filterWhere) {
    $begin = <<<EOD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Pardini//PsyFi Event Calendar//EN
NAME:Psy-Fi 2019 $filterWhere
X-WR-CALNAME:Psy-Fi 2019 $filterWhere
DESCRIPTION:Psy-Fi 2019 $filterWhere
X-WR-CALDESC:Psy-Fi 2019 $filterWhere
TIMEZONE-ID:Europe/Amsterdam
X-WR-TIMEZONE:Europe/Amsterdam
REFRESH-INTERVAL;VALUE=DURATION:PT01H
X-PUBLISHED-TTL:PT01H
COLOR:34:50:105
CALSCALE:GREGORIAN
METHOD:PUBLISH


EOD;

    $end = <<<EOD
END:VCALENDAR

EOD;

    header("Content-type: text/calendar");

    echo fixIcalLineTerm($begin);

    foreach ($allActs as $act) {
        if (($act['where'] === $filterWhere) || ("ALL" === $filterWhere))
            emitICal($act);
    }

    echo fixIcalLineTerm($end);
}

function icalTxt($txt) {
    $result = preg_replace("/[^a-zA-Z0-9\ ]+/", "", $txt);
    $result = str_replace("  ", " ", $result);
    $result = str_replace("  ", " ", $result);
    $result = str_replace("  ", " ", $result);
    return $result;
}

function emitIcal($a) {
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

function fixIcalLineTerm($txt) {
    return str_replace("\n", "\r\n", $txt);
}

function convertIcalDate($dt) {
    $dt = ($dt - 60 * 60 * 2); // remove 2 hours for UTC time... supposedly ;-)
    //$dt = strtotime($daystr . " " . $timestr);
    //print_r($dt);
    return date("Ymd", $dt) . "T" . date("His", $dt) . "Z";


}