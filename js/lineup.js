function updateLineUp (currTS) {
    let currMoment = moment.unix(currTS);
    let lineUpTable = document.getElementById('table-lineup');

    let currentActs = getCurrentActsByStage(currTS, currMoment);
    let allStagesHtml = "";
    let stageCounter = 1;

    let lowTime = 3131132312321312123;
    let highTime = 0;

    let allTimes = [];

    for (let stage of window.stages) {
        let actCounterInStage = 1;
        let stageHtml = "";


        let currentActsOfStage = currentActs[stage];
        for (let act of currentActsOfStage) {
            // Wtf. some acts do not adhere to the strict 30-minute slot thing.
            // in this case, show the correct time on text, but force the fucking correct slot.

            let preparedAct = prepareActForTNN(act);
            let moStart = preparedAct.moStart;
            let moEnd = preparedAct.moEnd;

            if (!((moEnd.minute() === 0) || (moEnd.minute() === 30))) {
                console.log("FIXING act", preparedAct.what, "start", moEnd.minute());
                if (moEnd.minute() > 31) {
                    moEnd.hour(moEnd.hour() + 1);
                    moEnd.minute(0);
                } else {
                    moEnd.minute(30);
                }
            }

            if (!((moStart.minute() === 0) || (moStart.minute() === 30))) {
                console.log("FIXING act", preparedAct.what, "end", moEnd.minute());
                if (moStart.minute() > 31) {
                    moStart.hour(moStart.hour() + 1);
                    moStart.minute(0);
                } else {
                    moStart.minute(30);
                }
            }

            let startId = moStart.format('dddHHmm');
            let endId = moEnd.format('dddHHmm');
            let oneActHTML = Handlebars.templates.lineup_act(
                {
                    act: preparedAct,
                    stageCounter: stageCounter,
                    startId: startId,
                    endId: endId,
                    actCounterInStage: actCounterInStage
                });

            if (moStart.unix() > highTime) highTime = moStart.unix();
            if (moStart.unix() < lowTime) lowTime = moStart.unix();
            if (moEnd.unix() > highTime) highTime = moEnd.unix();
            if (moEnd.unix() < lowTime) lowTime = moEnd.unix();

            stageHtml = stageHtml + oneActHTML;
            actCounterInStage++;
        }

        allStagesHtml = allStagesHtml + stageHtml;
        stageCounter++;
        if (stageCounter > 4) break; // @TODO: wtf.
    }

    let timeStyles = "";
    for (let oneTime = lowTime; oneTime < highTime + 1; oneTime = oneTime + (60 * 30)) {
        let fmt = moment.unix(oneTime).format('dddHHmm');
        let timeCSS = "[time-" + fmt + "] 1fr ";
        timeStyles = timeStyles + timeCSS;
    }
    document.getElementById('lineupstyle').innerText = "" +
        "    .schedule {" +
        "        grid-template-rows: " + timeStyles + ";" +
        "        grid-template-columns: [track-1-start] 1fr [track-1-end track-2-start] 1fr [track-2-end track-3-start] 1fr [track-3-end track-4-start] 1fr [track-4-end];" +
        "    }" +
        "";
    lineUpTable.innerHTML = allStagesHtml;
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
