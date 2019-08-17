function updateLineUp (currTS) {
    let currMoment = moment.unix(currTS);
    let lineUpTable = document.getElementById('table-lineup');

    let currentActs = getCurrentActsByStage(currTS, currMoment);
    let allStagesHtml = "";
    let stageCounter = 1;


    let allTimes = [];

    for (let stage of window.stages) {
        let actCounterInStage = 1;
        let stageHtml = "";


        let currentActsOfStage = currentActs[stage];
        for (let act of currentActsOfStage) {
            let preparedAct = prepareActForTNN(act);
            let startId = preparedAct.moStart.format('dddHHmm');
            let endId = preparedAct.moEnd.format('dddHHmm');
            let oneActHTML = Handlebars.templates.lineup_act(
                {
                    act: preparedAct,
                    stageCounter: stageCounter,
                    startId: startId,
                    endId: endId,
                    actCounterInStage: actCounterInStage
                });
            allTimes[preparedAct.moStart.unix()] = {
                fmt: preparedAct.moStart.format('dddHHmm'),
                desc: preparedAct.moStart.format('ddd[-]HH:mm')
            };
            allTimes[preparedAct.moEnd.unix()] = {
                fmt: preparedAct.moEnd.format('dddHHmm'),
                desc: preparedAct.moEnd.format('ddd[-]HH:mm')
            };

            stageHtml = stageHtml + oneActHTML;
            actCounterInStage++;
        }

        allStagesHtml = allStagesHtml + stageHtml;
        stageCounter++;
        if (stageCounter > 4) break; // @TODO: wtf.
    }

    let timeStyles = "";
    for (let time in allTimes) {
        let timeObj = allTimes[time];
        let timeCSS = "[time-" + timeObj.fmt + "] 1fr ";
        timeStyles = timeStyles + timeCSS;
    }


    document.getElementById('lineupstyle').innerText = "" +
        "    .schedule {" +
        "        grid-template-rows: " + timeStyles + ";" +
        "        grid-template-columns: [track-1-start] 1fr [track-1-end track-2-start] 1fr [track-2-end track-3-start] 1fr [track-3-end track-4-start] 1fr [track-4-end];" +
        "    }" +
        "";


    lineUpTable.innerHTML = /*timesHTML +*/ allStagesHtml;


}

function findCurrentActs (acts, currTS, currMoment, stage) {
    let ret = [];
    for (let act of acts) {
        let start = act['ts_start']['ts'];
        let end = act['ts_end']['ts'];
        if (currTS > end) {
            // act has ended. not point in showing it anymore...
        } else {
            ret.push(act);
        }
    }
    return ret;
}

function getCurrentActsByStage (currTS, currMoment) {
    let ret = {};
    for (let stage of window.stages) {
        let actsInStage = window.actsByStage[stage];
        ret[stage] = findCurrentActs(actsInStage, currTS, currMoment, stage);
    }
    return ret;
}


function doUpdateTableLineUp () {
    if (window.fakeTimeForNow) {
        updateLineUp(window.fakeTimeForNow);
    } else {
        updateLineUp(Math.floor(Date.now() / 1000));
    }
}

// update now and every minute from now.
doUpdateTableLineUp();
//setInterval(doUpdateTableLineUp, 60 * 1000);
