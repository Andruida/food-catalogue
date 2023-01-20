<?php

session_start(); 

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die();
};

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    http_response_code(400);
    echo "mező hiányzik!";
    die();
}

require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');
use \RedBeanPHP\R as R;

$dbcreds = Config::getMySQLCredentials();
R::setup($dbcreds["conn_str"], $dbcreds["username"], $dbcreds["password"]);
unset($dbcreds);

$user = R::findOne('user', 'username = ?', [$_POST["username"]]);

if ($user == NULL) {
    R::close();
    die("INVALID");
}

if (password_verify($_POST["password"], $user->password)) {
    $_SESSION["user_id"] = $user->id;
    $_SESSION["deployment"] = $user->deployment_id;
} else {
    R::close();
    die("INVALID");
}

R::close();

?>