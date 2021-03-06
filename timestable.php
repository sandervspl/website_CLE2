<?php
if(!isset($_SESSION)) {
    session_start();
}

require_once "nlDate.php";
require_once "connect.php";
require_once "Barbers.php";
?>
<!DOCTYPE HTML>
<html lang="en">
<body>

<?php
$Jeroen_ = new Jeroen();
$Juno_   = new Juno();

$monthname = date("F");

if (isset($_GET['month']) && isset($_GET['year']) && isset($_GET['day'])) {
    $day = $_GET['day'];
    $month = $_GET['month'];
    $year = intval($_GET['year']);

    $d = strtotime("$year-$month-$day");

    $monthname = nlDate(date("F", $d));
} else {
    $day = date("d");
    $month = date("m");
    $year = date("Y");
}

$date = $year."-".$month."-".$day;
$hour = 9;
$end_hour = 17;
$didMorningHeader = false;
$didAfternoonHeader = false;
$didEveningHeader = false;
$dayname = date("D", mktime(0,0,0, $month, $day, $year));

if ($_SESSION['barber'] !== '') {
    $barber = $_SESSION['barber'];
} else {
    $barber = "leeg";
}

if ($dayname === "Thu") {
    $hour = 11;
    $end_hour = 17;
} else if ($dayname === "Sat") {
    $end_hour = 15;
}




// loop start
for ($i = 0; $i <= $end_hour; $i++) {
    if ($i % 2 == 0) {
        if ($i > 0) {
            $hour++;
        }

        $m = "00";
    }

    if ($i % 2) {
        $m = "30";
    }

    if ($hour < 10) {
        $u = "0" . $hour;
    } else {
        $u = $hour;
    }

    $time1 = $u . ":" . $m;
    if ($m === "30") {
        $uu = $u + 1;
        $mm = "00";
    } else {
        $uu = $u;
        $mm = "30";
    }
    $time2 = $uu . ":" . $mm;

    // lunch break
    if ($hour === 13) {
        continue;
    }

    // database connection information
    $db =  mysqli_connect($host, $user, $pw, $database) or die('Error: '.mysqli_connect_error());

    $sql = sprintf("SELECT kapper FROM afspraken WHERE datum = '%s' AND tijd = '%s'",
        mysqli_real_escape_string($db, $date),
        mysqli_real_escape_string($db, $time1));

    $taken = false;
    $result = mysqli_query($db, $sql);
    $row = mysqli_fetch_row($result);
    mysqli_close($db);

    // if both barbers have appointments at this time we set it as taken
    if (mysqli_num_rows($result) == 2) {
        $taken = true;
    }

    // if the chosen barber has an appointment at this time we set it as taken
    else if (mysqli_num_rows($result) == 1) {
        if ($_SESSION['barber'] === $row[0]) {
            $taken = true;
        }

        // if we have no preference but only one of the barbers has an appointment at this time, the second barber is selected
        if ($_SESSION['barber'] === 'geen voorkeur') {
            if ($row[0] === "Jeroen") {
                $barber = "Juno";
            } else {
                $barber = "Jeroen";
            }
        }
    }

    // if no barber has an appointment at this time we random one if it's not Tue, Thu or Sat.
    else if ($_SESSION['barber'] === 'geen voorkeur') {
        $starting_day = "$year-$month-$day";
        $time = strtotime($starting_day);
        $weekday = date("D", mktime(0,0,0, $time, $time, $time));


        $allBarbersAvailable = true;
        $jeroenAvailable = true;
        $junoAvailable   = true;

        if (!$Jeroen_->isAvailable($weekday)) {
            $allBarbersAvailable = false;
            $jeroenAvailable = false;
        }

        if (!$Juno_->isAvailable($weekday)) {
            $allBarbersAvailable = false;
            $junoAvailable = false;
        }

        if (!$allBarbersAvailable) {
            if (!$jeroenAvailable) {
                $barber = "juno";
            }

            if (!$junoAvailable) {
                $barber = "Jeroen";
            }
        } else {
            $b = rand(0, 2);
            if ($b) {
                $barber = "Jeroen";
            } else {
                $barber = "Juno";
            }
        }
    }

    // if today is chosen and we are past current time, we disable the option
    $curtime = time();
    $buttontime = mktime($hour, $m, 0, $month, $day, $year);
    if ($curtime >= $buttontime) {
        $taken = true;
    }

    if ($taken) {
        $class_p = "time-button-taken";
        $class_i = "times-icon-taken";
        $class_d = "times-container-taken";
        $func1 = 0;
        $func2 = 0;
        $img_src = "images/booking/timer_clear2-taken.png";
        $isDisabled = "disabled";
    } else {
        $class_p = "time-button";
        $class_i = "times-icon";
        $class_d = "times-container";
        $func1 = "\"" . $time1 . "\"";  // add "" around data to avoid syntax error in onTimeClick() parameters
        $func2 = "\"" . $time2 . "\"";
        $img_src = "images/booking/timer_clear2.png";
        $isDisabled = "";
    }


    // admin check
    $isAdmin = false;

    if (isset($_SESSION['user'])) {
        $isAdmin = $_SESSION['user']['level'];
    }

    if ($hour < 12 && !$didMorningHeader) { ?>
        <div id="morning-header">
            <p class="o-m-a-header">Ochtend</p>
        </div>
        <div class="times-container-day white-background">
        <?php
        $didMorningHeader = true;
    }

    if ($hour > 11 && $hour < 18 && !$didAfternoonHeader) { ?>
        </div>
        <div id="afternoon-header">
            <p class="o-m-a-header">Middag</p>
        </div>
        <div class="times-container-day white-background">
        <?php
        $didAfternoonHeader = true;
    }

    if ($hour > 18 && !$didEveningHeader) { ?>
        </div>
        <div id="evening-header">
            <p class="o-m-a-header">Avond</p>
        </div>
        <div class="times-container-day white-background">
        <?php
        $didEveningHeader = true;
    }

    $mn = "\"" . $monthname . "\"";
    ?>

    <?php
    if ($isAdmin) {
        if (!$taken) { ?>
        <a id="admin-kalender-times-link" href="admin_nieuwe_afspraak.php?t=<?= $time1 ?>&tt=<?= $time2 ?>&d=<?= $date ?>&k=<?= $barber ?>">
    <?php
        }
    ?>
            <div id="<?= $time1 ?>" class="<?= $class_d ?>">
                <img id="<?= $time1 ?>" class="<?= $class_i ?>" src="<?= $img_src ?>">

                <div>
                    <p id="<?= $time1 ?>" class="<?= $class_p ?>"><?= $time1 ?></p>
                    <br/>
                    <script src="scripts/select.js"></script>
                    <script type="text/javascript">lockButton("time-button-taken")</script>

                    <p class="<?= $class_p ?>"><?= $time2 ?></p>
                </div>
            </div>
    <?php
        if (!$taken) {
    ?>
            </a>
    <?php
        }
    } else {
        ?>
        <div id="<?= $time1 ?>" class="<?= $class_d ?>" onclick='onTimeClick(<?= $func1 ?>, <?= $func2 ?>, <?= $mn ?>)'>
            <img id="<?= $time1 ?>" class="<?= $class_i ?>" src="<?= $img_src ?>">

            <div>
                <button id="<?= $time1 ?>" type="submit" name="timestablebutton" class="<?= $class_p ?>"
                        value="<?= $time1 ?>|<?= $barber ?>" <?= $isDisabled ?>><?= $time1 ?></button>
                <br/>
                <script src="scripts/select.js"></script>
                <script type="text/javascript">lockButton("time-button-taken")</script>

                <p class="<?= $class_p ?>"><?= $time2 ?></p>
            </div>
        </div>
        <?php
    }
}

if ($didEveningHeader) {
    ?></div><?php
}
?>

<script src="scripts/select.js"></script>
</body>
</html>