<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die();
};

require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');
use \RedBeanPHP\R as R;

$dbcreds = Config::getMySQLCredentials();
R::setup($dbcreds["conn_str"], $dbcreds["username"], $dbcreds["password"]);
R::freeze($dbcreds["frozen"]);
unset($dbcreds);

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

if (!isset($_POST["id"]) || empty($_POST["id"])) {
    http_response_code(400);
    echo "id hiányzik!";
    die();
}

$d = R::findOne("deployment", "deployment.id = ? AND @shared.user.id = ?", [$_POST["id"], $USER->id]);
if ($d == NULL) {
    echo "NOTFOUND";
    die();
}

$USER->last_deployment = $d;
R::store($USER);
$_SESSION["deployment_id"] = $d->id;

?>