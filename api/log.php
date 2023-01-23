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
// R::debug();

session_start();
$USER = $DEPLOYMENT = NULL;
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"]) || 
    !isset($_SESSION["deployment_id"]) || empty($_SESSION["deployment_id"])) {
        http_response_code(401);
        die();
}
$USER = R::findOne("user", 'id = ?', [$_SESSION["user_id"]]);
$DEPLOYMENT = R::findOne("deployment", 
    'deployment.id = ? AND @shared.user.id = ?', 
    [$_SESSION["deployment_id"], $_SESSION["user_id"]]
);
if ($USER == NULL || $DEPLOYMENT == NULL) {
    session_destroy();
    http_response_code(401);
    die();
}

// validation

$mealType_enum = ["breakfast", "lunch", "dinner"];
if (!isset($_POST["mealType"]) || array_search($_POST["mealType"], $mealType_enum) === false) {
    http_response_code(400);
    echo "mealType hiányzik vagy hibás!";
    die();
}

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
    if (isset($_POST[$k]) && !empty($_POST[$k])) {
        $_POST[$k] = trim($_POST[$k]);
        if (strlen($_POST[$k]) <= 100) $allEmpty = false;
    }
}

if ($allEmpty) {
    http_response_code(400);
    echo "valamelyik mezőt ki kell tölteni!";
    die();
}

// query

$foods = [];

foreach ($dish_fields as $k => $v) {
    if (empty($_POST[$v])) continue;
    $foods[$k] = R::findOne('food', 
    "name = ? AND deployment_id = ? AND foodtype_id = ?",
    [$_POST[$v], $DEPLOYMENT->id, R::enum("foodtype:$v")->id]);
    $mealtype_bean = R::enum("mealtype:".$_POST["mealType"]);

    if ($foods[$k] == null) {
        if ($_POST["foodEntryMode"] == "onlyExisting") {
            R::close();
            die("NOTFOUND");
        }
        $foods[$k] = R::dispense("food");
        $foods[$k]->name = $_POST[$v];
        $foods[$k]->foodtype = R::enum("foodtype:$v");
        $foods[$k]->mealtype_id = $mealtype_bean->id;
        $foods[$k]->deployment = $DEPLOYMENT;
    }

    if (($mealtype_bean->id & $foods[$k]->mealtype_id) == 0) {
        // echo "mealtype changed from ".$foods[$k]->mealtype_id."\n";
        $foods[$k]->mealtype_id += $mealtype_bean->id;
        // echo "to ".$foods[$k]->mealtype_id."\n";
    }


}

$maincourse = null;

if (!empty($foods[1])) {
    if (empty($foods[2]))
        $ccount = R::count('dishcombo', 'main_course_id = ? AND side_dish_id IS NULL', [$foods[1]->id]);
    else
        $ccount = R::count('dishcombo', 'main_course_id = ? AND side_dish_id = ?', [$foods[1]->id, $foods[2]->id]);

    if ($ccount == 0) {
        $maincourse = R::dispense("dishcombo");
        $maincourse->mainCourse = $foods[1];
        if (!empty($foods[2]))
            $maincourse->sideDish = $foods[2];

        R::store($maincourse);
    } else {
        if (empty($foods[2]))
            $maincourse = R::findOne('dishcombo', 'main_course_id = ? AND side_dish_id IS NULL', [$foods[1]->id]);
        else
            $maincourse = R::findOne('dishcombo', 'main_course_id = ? AND side_dish_id = ?', [$foods[1]->id, $foods[2]->id]);
    }
}

if ($_POST["foodEntryMode"] != "noLog" && (!empty($foods[0]) || !empty($foods[1]) || !empty($foods[3]))) {

    $logEntry = R::dispense("log");
    $logEntry->date = date("Y-m-d", strtotime($_POST["date"]));
    $logEntry->mealtype = R::enum("mealtype:".$_POST["mealType"]);
    if (!empty($foods[0]))
        $logEntry->soup = $foods[0];
    if (!empty($foods[1]))
        $logEntry->dishcombo = $maincourse;
    if (!empty($foods[3]))
        $logEntry->dessert = $foods[3];

    R::store($logEntry);
    R::storeAll($foods);
} else {
    R::storeAll($foods);
}

R::close();

?>