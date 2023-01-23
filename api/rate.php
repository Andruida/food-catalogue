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

if (!isset($_POST["id"]) || empty($_POST["id"]) ||
    !isset($_POST["type"]) ||
    ($_POST["type"] != "food" && $_POST["type"] != "dishcombo") ||
    !isset($_POST["rating"]) || !is_numeric($_POST["rating"])) {
        http_response_code(400);
        echo "mező hiányzik vagy hibás!";
        die();
}

$rating = floatval($_POST["rating"]);
if ($rating < 0 || $rating > 1) {
    http_response_code(400);
    echo "rating határon kívül!";
    die();
}
// action

$type = $_POST["type"];
$toBeRated = null;
if ($type == 'food') {
    $toBeRated = R::findOne('food', 
        "deployment_id = ? AND food.id = ?",
        [$DEPLOYMENT->id, $_POST["id"]]
    );
} else {
    $toBeRated = R::findOne('dishcombo', 
        "@joined.food[as:main_course].deployment_id = ? AND dishcombo.id = ?",
        [$DEPLOYMENT->id, $_POST["id"]]
    );
}

if ($toBeRated == null) {
    R::close();
    die("NOTFOUND");
}

$ratingBean = null;
if ($type == 'food') {
    $ratingBean = R::find('rating', 
        "user_id = ? AND food_id = ? AND dishcombo_id IS NULL LIMIT 1",
        [$USER->id, $toBeRated->id], "FOR UPDATE"
    );
} else {
    $ratingBean = R::find('rating', 
        "user_id = ? AND food_id IS NULL AND dishcombo_id = ? LIMIT 1",
        [$USER->id, $toBeRated->id], "FOR UPDATE"
    );
}

if (empty($ratingBean)) {
    $ratingBean = R::dispense("rating");
    if ($type == 'food') {
        $ratingBean->food = $toBeRated;
    } else {
        $ratingBean->dishcombo = $toBeRated;
    }
    $ratingBean->user = $USER;
} else {
    $ratingBean = reset($ratingBean);
}
$ratingBean->rating = round($rating, 2);

R::store($ratingBean);

?>