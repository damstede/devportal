var schedule = {
    user: null,
    defaults: null,
    carts: [],
    dates: ["", "", "", "", ""],
    currentlyLoaded: [0, 0],

    init: function(user) {
        return new Promise(function(resolve, reject) {
            schedule["user"] = user;
            schedule["defaults"] = document.getElementsByClassName("schedule")[0].innerHTML;

            var cartsReq = new XMLHttpRequest();
            cartsReq.open('POST', 'import/dcarts.php');
            cartsReq.onload = function() {
                try {
                    var response = JSON.parse(cartsReq.responseText);
                    for (var i = 0; i < response["data"].length; i++) {
                        schedule.carts[response["data"][i]["id"]] = response["data"][i];
                    }
                    resolve();
                }
                catch(e) {
                    reject(e);
                }
            };
            cartsReq.send();
        });
    },

    clear: function() {
        schedule.dates = ["", "", "", "", ""];
        schedule.currentlyLoaded = [0, 0];
        document.getElementsByClassName("schedule")[0].innerHTML = schedule["defaults"];
        document.getElementsByClassName("week-name")[0].innerHTML = "bezig met laden...";
        var dayDates = document.getElementsByClassName("daydate");
        for (var i = 0; i < 5; i++) {
            dayDates[i].innerHTML = "";
        }
        var lessons = document.getElementsByClassName("lesson");
        var lessonCount = lessons.length;
        for (var i = 0; i < lessonCount; i++) {
            if (lessons[i].className.indexOf("break") < 0) {
                lessons[i].setAttribute("data-location", "");
            }
        }
    },

    reload: function() {
        schedule.getAndLoad(schedule.currentlyLoaded[0], schedule.currentlyLoaded[1]).catch(function(error) {
            showAction('loadingerror');
        });
    },

    get: function(year, week) {
        document.getElementById('loading').style.display = 'table';
        return new Promise(function(resolve, reject) {
            var dateReq = new XMLHttpRequest();
            dateReq.open('POST', 'import/weekdates.php?year='+year+'&week='+week);
            dateReq.onload = function() {
                schedule.clear();
                schedule.currentlyLoaded = [year, week];
                var dayDates = document.getElementsByClassName("daydate");
                var dates = dateReq.responseText.split(" ");
                for (var i = 0; i < 6; i++) {
                    dates[i] = dates[i].split("-");
                    if (i > 0) {
                        dayDates[i-1].innerHTML = dates[i][2]+'-'+dates[i][1];
                    }
                    else {
                        document.getElementsByClassName("week-name")[0].innerHTML = "week " + dates[i][1];
                    }
                }
                dates.shift();
                schedule["dates"] = dates;
                var request = new XMLHttpRequest();
                request.open('POST', 'import/dschedule.php?year='+year+'&week='+week);
                request.onload = function() {
                    try {
                        var response = JSON.parse(request.responseText);
                        console.log(response);
                        if (response["type"] == "success") {
                            var zRequest = new XMLHttpRequest();
                            zRequest.open('POST', 'import/zschedule.php?year='+year+'&week='+week);
                            zRequest.onload = function() {
                                try {
                                    var zResponse = JSON.parse(zRequest.responseText);
                                    console.log(zResponse);
                                    if (zResponse["type"] == "success") {
                                        document.getElementById('loading').style.display = 'none';
                                        resolve([response["data"], zResponse["data"]]);
                                    }
                                    else {
                                        document.getElementById('loading').style.display = 'none';
                                        reject(zResponse["message"]);
                                    }
                                }
                                catch(e) {
                                    document.getElementById('loading').style.display = 'none';
                                    reject(e);
                                }
                            };
                            zRequest.send();
                        }
                        else {
                            document.getElementById('loading').style.display = 'none';
                            reject(response["message"]);
                        }
                    }
                    catch(e) {
                        document.getElementById('loading').style.display = 'none';
                        reject(e);
                    }
                };
                request.send();
            };
            dateReq.send();
        });
    },

    getCurrentWeekInfo: function() {
        return new Promise(function(resolve, reject) {
            var request = new XMLHttpRequest();
            request.open('POST', 'import/weeknr.php');
            request.onload = function() {
                var d = new Date();
                resolve([d.getFullYear(), parseInt(request.responseText), d.getDay()]);
            };
            request.send();
        });
    },

    getAndLoad: function(year, week) {
        return new Promise(function(resolve, reject) {
            schedule.get(year, week).then(function(results) {
                schedule.load(results[0], results[1]);
                resolve();
            })
            .catch(function(reason) {
                reject(reason);
            });
        });
    },

    load: function(reservations, zermeloSchedule) {
        console.log(reservations);
        console.log(zermeloSchedule);
        document.getElementById('loading').style.display = 'table';
        var reservationCount = reservations.length;
        for (var i = 0; i < reservationCount; i++) {
            document.getElementById("week-hour-"+reservations[i]["hour"]).children[reservations[i]["day"]+1].appendChild(schedule.createResElem(reservations[i]));
        }
        var lessonsCount = zermeloSchedule.length;
        for (var i = 0; i < lessonsCount; i++) {
            if (zermeloSchedule[i]["startTimeSlot"] != null) {
                document.getElementById("week-hour-"+zermeloSchedule[i]["startTimeSlot"]).children[zermeloSchedule[i]["day"]+1].setAttribute("data-location", zermeloSchedule[i]["locations"][0]);
                document.getElementById("week-hour-"+zermeloSchedule[i]["startTimeSlot"]).children[zermeloSchedule[i]["day"]+1].setAttribute("data-students", zermeloSchedule[i]["students"]);
            }
        }
        schedule.fillInResAddBtns();
        document.getElementById('loading').style.display = 'none';
    },

    createResElem: function(res) {
        var resElem = document.createElement("div");
        resElem.className = "reservation";
        if (res["cancelled"]) {
            resElem.className += " cancelled";
            resElem.setAttribute("title", 'Geannuleerde reservering voor ' + schedule.carts[res["cart_id"]]["name"] + ' (' + schedule.carts[res["cart_id"]]["dev_type"] + ') op ' + new Date(Date.parse(res["date"])).toLocaleDateString() + ', het ' + res["hour"] + 'e uur');
        }
        else {
            resElem.setAttribute("title", 'Reservering voor ' + schedule.carts[res["cart_id"]]["name"] + ' (' + schedule.carts[res["cart_id"]]["dev_type"] + ') op ' + new Date(Date.parse(res["date"])).toLocaleDateString() + ', het ' + res["hour"] + 'e uur');
        }
        var contents = '';
        if (res["user"] === schedule["user"] && !res["cancelled"]) {
            resElem.className += " cancellable";
            contents += '<a class="reservation-cancel" title="Reservering annuleren" href="javascript:void(0)" onclick="setUpReservationCanceller('+res["id"]+'); showAction(\'reservationcancel\');">&#x2716;</a>';
        }
        contents += '<b>' + schedule.carts[res["cart_id"]]["name"] + (res["cart_type"] == 1 ? ',<span class="extra-info"> lokaal</span> '+res["location"] : ', '+res["amount"]+' plaatsen') + '</b><br/>' + res["user"];
        if (res["teacher"] != null) {
            contents += ', namens:<br/><i>' + res["teacher"] + '</i>';
        }
        resElem.innerHTML = contents;
        return resElem;
    },

    fillInResAddBtns: function() {
        var resAddBtn = document.createElement("div");
        resAddBtn.className = "reservation add-btn";
        resAddBtn.innerHTML = "+";
        resAddBtn.setAttribute("title", "Nieuwe reservering aanmaken");
        resAddBtn.setAttribute("onclick", "showAction('reservationadder');");

        var lessons = document.getElementsByClassName("lesson");
        var lessonCount = lessons.length;
        for (var i = 0; i < lessonCount; i++) {
            if (lessons[i].className.indexOf("break") < 0) {
                var lessonDay = parseInt(lessons[i].getAttribute("data-lesson").split("-")[0]);
                var lessonHour = parseInt(lessons[i].getAttribute("data-lesson").split("-")[1]);
                var lessonLocation = lessons[i].getAttribute("data-location");
                var lessonStudents = lessons[i].getAttribute("data-students");
                var clonedBtn = resAddBtn.cloneNode(true);
                clonedBtn.setAttribute("onclick", "showAction('reservationadder'); setUpReservationAdder('"+schedule.dates[lessonDay-1].join("-")+"', "+lessonHour+", '"+lessonLocation+"', "+lessonStudents+");");
                lessons[i].appendChild(clonedBtn);
            }
        }
    }
};