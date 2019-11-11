var schedule = {
    default: null,
    init: function() {

    },

    clear: function() {

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
    },

    createResElem: function(res) {
        var resElem = document.createElement("div");
        resElem.className = "reservation";
        if (res["cancelled"]) {
            resElem.className += " cancelled";
        }
        resElem.innerHTML = "Kar " + res["cart_id"] + ", gereserveerd door " + res["user"] + " namens " + res["teacher"] + " in lokaal " + res["location"];
        return resElem;
    }
};