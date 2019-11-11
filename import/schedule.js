var schedule = {
    defaults: null,
    carts: [],

    init: function() {
        return new Promise(function(resolve, reject) {
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
        document.getElementsByClassName("schedule")[0].innerHTML = schedule["defaults"];
    },

    get: function(year, week) {
        return new Promise(function(resolve, reject) {
            var dateReq = new XMLHttpRequest();
            dateReq.open('POST', 'import/weekdates.php?year='+year+'&week='+week);
            dateReq.onload = function() {
                schedule.clear();
                var dayDates = document.getElementsByClassName("daydate");
                var dates = dateReq.responseText.split(" ");
                for (var i = 0; i < 5; i++) {
                    dates[i] = dates[i].split("-");
                    dayDates[i].innerHTML = dates[i][2]+'-'+dates[i][1];
                }
                var request = new XMLHttpRequest();
                request.open('POST', 'import/dschedule.php?year='+year+'&week='+week);
                request.onload = function() {
                    try {
                        var response = JSON.parse(request.responseText);
                        console.log(response);
                        if (response["type"] == "success") {
                            resolve(response["data"]);
                        }
                        else {
                            reject(response["message"]);
                        }
                    }
                    catch(e) {
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

    load: function(reservations) {
        console.log(reservations);
        var reservationCount = reservations.length;
        for (var i = 0; i < reservationCount; i++) {
            document.getElementById("week-hour-"+reservations[i]["hour"]).children[reservations[i]["day"]+1].appendChild(schedule.createResElem(reservations[i]));
        }
        schedule.fillInResAddBtns();
    },

    createResElem: function(res) {
        var resElem = document.createElement("div");
        resElem.className = "reservation";
        if (res["cancelled"]) {
            resElem.className += " cancelled";
        }
        var contents = '<b>Kar ' + res["cart_id"] + ' (' + schedule.carts[res["cart_id"]]["dev_type"] + '),<span class="location-prefix"> lokaal</span> '+res["location"]+'</b><br/>' + res["user"];
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
        resAddBtn.setAttribute("title", "Kar reserveren");

        var lessons = document.getElementsByClassName("lesson");
        var lessonCount = lessons.length;
        for (var i = 0; i < lessonCount; i++) {
            if (lessons[i].className.indexOf("break") < 0) {
                lessons[i].appendChild(resAddBtn.cloneNode(true));
            }
        }
    }
};