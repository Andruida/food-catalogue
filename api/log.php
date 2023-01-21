<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die();
};

require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');
use \RedBeanPHP\R as R;

$dbcreds = Config::getMySQLCredentials();
R::setup($dbcreds["conn_str"], $dbcreds["username"], $dbcreds["password"]);
unset($dbcreds);

session_start();
$USER = $DEPLOYMENT = NULL;
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || 
    !isset($_SESSION["deployment_id"]) || empty($_SESSION["deployment_id"])) {
        http_response_code(401);
        die();
}
$USER = R::findOne("user", 'id = ?', [$_SESSION["user_id"]]);
$DEPLOYMENT = R::findOne("deployment", 'id = ?', [$_SESSION["deployment_id"]]);
if ($USER == NULL || $DEPLOYMENT == NULL) {
    session_destroy();
    http_response_code(401);
    die();
}

// validation

$foodEntryMode_enum = ["noLog", "createIfNeeded", "onlyExisting"];
if (!isset($_POST["foodEntryMode"]) || array_search($_POST["foodEntryMode"], $foodEntryMode_enum) === false) {
    http_response_code(400);
    echo "foodEntryMode hiányzik vagy hibás!";
    die();
}

if ($_POST["foodEntryMode"] != "noLog" && (!isset($_POST["date"]) || empty($_POST["date"]))) {
    http_response_code(400);
    echo "date hiányzik!";
    die();
}

$allEmpty = true;
$dish_fields = ["soup", "mainCourse", "sideDish", "dessert"];
foreach ($dish_fields as $k) {
    if (isset($_POST[$k]) && !empty($_POST[$k]) && strlen($_POST[$k]) <= 100) $allEmpty = false;
}
if ($allEmpty) {
    http_response_code(400);
    echo "valamelyik mezőt ki kell tölteni!";
    die();
}

// query

$foods = [];

if ($_POST["foodEntryMode"] == "createIfNeeded" || $_POST["foodEntryMode"] == "noLog" ) {
    foreach ($dish_fields as $k => $v) {
        if (empty($_POST[$v])) continue;
        $hasBeenCreated = false;
        $foods[$k] = R::findOrCreate('food', [
            'name' => $_POST[$v],
            'type_id' => R::enum("foodtype:$v")->id,
            'deployment_id' => $DEPLOYMENT->id
        ], '', $hasBeenCreated);

        if ($hasBeenCreated) {
            $foods[$k]->type = R::enum("foodtype:$v");
            $foods[$k]->deployment = $DEPLOYMENT;
        }
    }
} else {
    foreach ($dish_fields as $k => $v) {
        if (empty($_POST[$v])) continue;
        $foods[$k] = R::findOne('food', 'name = ? AND deployment_id = ?', [$_POST[$v], $DEPLOYMENT->id]);
        if ($foods[$k] == NULL) {
            R::close();
            die("NOTFOUND");
        }
    }
}

if ($_POST["foodEntryMode"] != "noLog") {

    $logEntry = R::dispense("log");
    $logEntry->date = date("Y-m-d", strtotime($_POST["date"]));
    foreach ($dish_fields as $k => $v) {
        if (empty($foods[$k])) 
            continue;
        $logEntry->$v = $foods[$k];
    }

    R::store($logEntry);
} else {
    R::storeAll($foods);
}

if (!empty($foods[1])) {
    $ccount = R::count('dishcombo', 'main_course_id = ? AND side_dish_id = ?', [$foods[1]->id, $foods[2]->id]);

    if ($ccount == 0) {
        $maincourse = R::dispense("dishcombo");
        $maincourse->mainCourse = $foods[1];
        if (!empty($foods[2]))
            $maincourse->sideDish = $foods[2];

        R::store($maincourse);
    }
}

R::close();

?>