<?php
require_once "common.php";
require_once "nlDate.php";


// level check -- only admins can enter this page
$canEnterPage = false;

if(isset($_SESSION['user'])) {
    if ($_SESSION['user']['level'] == 1) {
        $canEnterPage = true;
    }
}

// redirect forbidden users
if (!$canEnterPage) {
    header("Location: forbidden.php");
    die("Redirecting to forbidden.php");
}

// set date variables
if (isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year'])) {
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
$datetime = strtotime($date);

?>
<!DOCTYPE HTML>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<body>
<?php
if ($_GET['p'] == 1) {
    $barber = "Jeroen";
} else {
    $barber = "Juno";
}
?>

<div id="basic-wrapper">
    <div class="white-background">
        <div class="admin-wrapper-wide margin-t-40">
            <div id="admin-kalender-times">
                <?php
                $monthname = date("F");

                $hour = 9;
                $end_hour = 17;
                $didMorningHeader = false;
                $didAfternoonHeader = false;
                $didEveningHeader = false;
                $dayname = date("D", mktime(0, 0, 0, $month, $day, $year));

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

                    // lunch break
                    if ($hour === 13) {
                        continue;
                    }

                    // determine hour, minute and how to write it down on page
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

                    // check if we need to start a new section
                    if ($hour < 12 && !$didMorningHeader) {
                        ?>
                        <div class="o-m-a-header-wrapper">
                            <p class="admin-o-m-a-header">Ochtend</p>
                        </div>

                        <div class="times-container-day">
                        <?php

                        $didMorningHeader = true;
                    }

                    if ($hour > 11 && $hour < 18 && !$didAfternoonHeader) {
                        ?>
                        </div>

                            <div class="o-m-a-header-wrapper">
                                <p class="admin-o-m-a-header">Middag</p>
                            </div>

                        <div class="times-container-day">
                        <?php

                        $didAfternoonHeader = true;
                    }

                    if ($hour > 18 && !$didEveningHeader) {
                        ?>
                        </div>

                        <div class="o-m-a-header-wrapper">
                            <p class="admin-o-m-a-header">Avond</p>
                        </div>

                        <div class="times-container-day">
                        <?php

                        $didEveningHeader = true;
                    }


                    // check if current prepared timeslot is in the past or present / future
                    $curtime   = time();
                    $starttime = mktime($u, $m, 0, $month, $day, $year);
                    $endtime   = mktime($uu, $mm, 0, $month, $day, $year);

                    if ($curtime >= $starttime && $curtime < $endtime) {
                        $isCurrentTimeSlot = "admin-times-container-current";
                    } else {
                        $isCurrentTimeSlot = "";
                    }

                    if ($curtime > $starttime && $isCurrentTimeSlot === "") {
                        $isPast = true;
                    } else {
                        $isPast = false;
                    }

                    if ($isPast) {
                        $class_p = "time-button-taken";
                        $class_i = "times-icon-taken";
                        $class_txt = "time-user-info-past";
                        $img_src = "images/booking/timer_clear2-taken.png";
                    } else {
                        $class_p = "time-button";
                        $class_i = "times-icon";
                        $class_txt = "";
                        $img_src = "images/booking/timer_clear2.png";
                    }
                    ?>

                    <div id="<?=$time1?>|<?=$img_src?>" class="admin-times-container <?=$isCurrentTimeSlot?>" onmouseover="timesContainerHover(this.id);" onmouseout="timesContainerOut(this.id)">
                        <div class="admin-times-time-container">
                            <img id="<?= $time1 ?>" class="admin-times-img <?=$class_i?>" src="<?=$img_src?>">

                            <div>
                                <p class="<?=$class_p?>"><?= $time1 ?></p><br/>
                                <p class="<?=$class_p?> small-text-time"><?= $time2 ?></p>
                            </div>
                        </div>

                        <div class="admin-time-appointment-info">
                    <?php

                    $db = mysqli_connect($host, $user, $pw, $database) or die('Error: ' . mysqli_connect_error());

                    $sql = "SELECT
                              id,
                              voornaam,
                              achternaam,
                              knipbeurt,
                              telefoon
                            FROM
                              afspraken
                            WHERE
                              tijd = ?
                            AND
                              datum = ?
                            AND
                              kapper = ?
                            ";

                    if ($stmt = $db->prepare($sql)) {
                        $stmt->bind_param('sss', $time1, $date, $barber);

                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($fid, $fvnaam, $fanaam, $fcut, $fphone);

                            while ($stmt->fetch()) { ?>
                                <p class="<?=$class_txt?>"><?= ucfirst($fvnaam) . " " . ucfirst($fanaam) ?></p>
                                <p class="<?=$class_txt?>"><?= ucfirst($fcut) ?></p>
                                <p class="<?=$class_txt?>"><?= $fphone ?></p>
                                <p class="small-text"><a href="admin_verwijder_afspraak.php?id=<?=$fid?>&p=1&b=<?=$_GET['p']?>">Verwijder afspraak</a> </p><?php
                            }
                        } else {

                            // if we are past current time, we disable the option
                            $curtime = time();
                            $timeslot = mktime($hour, $m, 0, $month, $day, $year);
                            if ($curtime < $timeslot) {?>
                                <p>
                                    <a href="admin_nieuwe_afspraak.php?t=<?= $time1 ?>&tt=<?= $time2 ?>&d=<?= $date ?>&k=<?= $barber ?>">Voeg afspraak toe</a>
                                </p> <?php
                            }
                        }

                        $stmt->close();
                    } else {
                        die("Error: 1");
                    }
                    ?>
                        </div>
                    </div>
                    <?php
                }

                // close evening header div
                if ($didEveningHeader) { ?>
                </div> <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="scripts/admin.js"></script>
</body>
</html>