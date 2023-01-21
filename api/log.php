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

if (!isset($_POST["name"]) || empty($_POST["name"]) || 
    !isset($_POST["date"]) || empty($_POST["date"])) {
    http_response_code(400);
    echo "mező hiányzik!";
    die();
}

$food == NULL;

if (isset($_POST["createIfNeeded"]) && $_POST["createIfNeeded"] == "true") {
    $food = R::findOrCreate('food', [
        'name' => $_POST["name"],
        'deployment_id' => $DEPLOYMENT->id
    ]);
} else {
    $food = R::findOne('food', 'name = ? AND deployment_id = ?', [$_POST["name"], $DEPLOYMENT->id]);
    if ($food == NULL) {
        R::close();
        die("NOTFOUND");
    }
}

$logEntry = R::dispense("log");
$logEntry->date = date("Y-m-d", strtotime($_POST["date"]));
$logEntry->food = $food;

R::store($logEntry);

R::close();

?>