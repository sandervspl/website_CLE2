/**
 * Created by Sandervspl on 12/3/15.
 */

function loadCalendar(m, y) {
    var xmlhttp;

    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById("calendar-table").innerHTML = xmlhttp.responseText;
        }
    };

    var url = "calendar.php?month=" + m + "&year=" + y;
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}

function getMonth(m) {
    var month = new Array(13);
    month[1] = "January";
    month[2] = "February";
    month[3] = "March";
    month[4] = "April";
    month[5] = "May";
    month[6] = "June";
    month[7] = "July";
    month[8] = "August";
    month[9] = "September";
    month[10] = "October";
    month[11] = "November";
    month[12] = "December";
    return month[m];
}

function prevMonth(sender, m, y) {
    sender.style.webKitFilter = "opacity(25%)";
    sender.style.mozFilter = "opacity(25%)";
    sender.style.filter = "opacity(25%)";
    window.document.getElementById('arrow_right').style.webKitFilter = "opacity(100%)";
    window.document.getElementById('arrow_right').style.mozFilter = "opacity(100%)";
    window.document.getElementById('arrow_right').style.filter = "opacity(100%)";

    window.document.getElementById('calendar-header-text').innerHTML = getMonth(m);

    loadCalendar(m, y);
}

function nextMonth(sender, m, y) {
    sender.style.webKitFilter = "opacity(25%)";
    sender.style.mozFilter = "opacity(25%)";
    sender.style.filter = "opacity(25%)";
    window.document.getElementById('arrow_left').style.webKitFilter = "opacity(100%)";
    window.document.getElementById('arrow_left').style.mozFilter = "opacity(100%)";
    window.document.getElementById('arrow_left').style.filter = "opacity(100%)";

    m++;
    if (m > 12) {
        m = 1;
        y++;
    }
    window.document.getElementById('calendar-header-text').innerHTML = getMonth(m);

    loadCalendar(m, y);
}

//change time information from date without reloading page (AJAX)
function showTimes(date, d, m, y) {
    if (document.getElementsByTagName('body')[0].clientWidth < 595) {
        console.log("Screen too small, not displaying times-table");

        // TODO: DO DROPDOWN LIST FOR MOBILE
    } else {
        if (date == "") {
            document.getElementById("times-table").innerHTML = "";
        } else {
            var xmlhttp;

            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    document.getElementById("times-table").innerHTML = xmlhttp.responseText;
                }
            };

            var url = "timestable.php?day=" + d + "&month=" + m + "&year=" + y;
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
    }
}

function onDateClick(d, da, mo, m, y) {
    date = d + "_" + m + "_" + y;
    showTimes(date, d, m, y);

    window.document.querySelector('#date-and-time-header').innerHTML = "<p class='header-text-small'>" + da + "</p>" + d + " " + mo + " " + y;
    window.document.getElementById('date-and-time-header').style.borderBottom = "1px solid black";

    b = window.document.getElementById('date-and-time-times-container');
    //b.style.visibility = "visible";
    b.style.height = "auto";

    window.sessionStorage.monthday = d;

    // animate
    //$('date-and-time-times-container').animate({height:'300px'}, 5000, function() {});

    //window.location.href = 'booking.php?day=' + d + "&month=" + m + "&year=" + y;
}

function onTimeClick(time, etime, month) {
    if (time == 0 || etime == 0) return;

    window.sessionStorage.time = time;
    window.sessionStorage.end_time = etime;
    window.sessionStorage.month = month;
    window.location.href = 'gegevens.php';
}