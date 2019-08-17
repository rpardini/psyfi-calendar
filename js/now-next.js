function showTimespan (moStart, moEnd) {
    if (moStart.dayOfYear() === moEnd.dayOfYear()) {
        return moStart.format("[[]ddd[]] HH:mm") + "-" + moEnd.format("HH:mm");
    }
    return moStart.format("[[]ddd[]] HH:mm") + "-" + moEnd.format("[[]ddd[]] HH:mm");
}


function prepareActForTNN (od, currTS, verb) {
    if (!od) return null;
    let moStart = moment.unix(od["ts_start"]["ts"]);
    let moEnd = moment.unix(od["ts_end"]["ts"]);
    od.when = showTimespan(moStart, moEnd);
    od.moStart = moStart;
    od.moEnd = moEnd;
    od.fromNow = "(" + verb + " " + moStart.from(currTS) + ")";
    return od;
}

function find3ForStage (acts, currTS, currMoment, stage) {
    let found = -1;
    let exact = false;
    let i = 0;
    for (let act of acts) {
        let start = act['ts_start']['ts'];
        let end = act['ts_end']['ts'];
        if (currTS > start) {
            found = i;
            if (currTS < end) {
                exact = true;
                break;
            }
        }
        i++;
    }
    let now = exact ? prepareActForTNN(acts[found], currMoment, "started") : {what: "--"};
    let next = prepareActForTNN(acts[found + 1], currMoment, "begins");
    return {where: stage, now: (now), next: (next)};
}

function get3ActsByStage (currTS, currMoment) {
    let ret = {};
    for (let stage of window.stages) {
        let actsInStage = window.actsByStage[stage];
        ret[stage] = find3ForStage(actsInStage, currTS, currMoment, stage);
    }
    return ret;
}

function getAllStagesHTML (actsByStage) {
    let allStagesHTML = "";
    for (let stage of window.stages) {
        let actsInStage = actsByStage[stage];
        let htmlForStage = Handlebars.templates.tnn_stage(actsInStage);
        allStagesHTML = allStagesHTML + htmlForStage;
    }
    return allStagesHTML;
}

function updateTableNowNext (currTS) {
    let currMoment = moment.unix(currTS);
    let nowNextTable = document.getElementById('table-now-next');
    let actsByStage = get3ActsByStage(currTS, currMoment);
    nowNextTable.innerHTML = Handlebars.templates.tnn({
        currTime: currMoment.format("[[]ddd[]] HH:mm"),
        items: getAllStagesHTML(actsByStage)
    });
}

function doUpdateTableNowNext () {
    if (window.fakeTimeForNow) {
        updateTableNowNext(window.fakeTimeForNow);
    } else {
        updateTableNowNext(Math.floor(Date.now() / 1000));
    }
}

// update now and every minute from now.
doUpdateTableNowNext();
setInterval(doUpdateTableNowNext, 60 * 1000);
