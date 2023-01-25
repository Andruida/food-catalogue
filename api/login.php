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
R::freeze($dbcreds["frozen"]);
unset($dbcreds);

$user = R::findOne('user', 'username = ?', [$_POST["username"]]);

if ($user == NULL) {
    R::close();
    die("INVALID");
}

if (password_verify($_POST["password"], $user->password)) {
    $_SESSION["user_id"] = $user->id;
    $count = R::count("deployment", 
        "@shared.user.id = ? AND deployment.id = ?", 
        [$user->id, $user->last_deployment_id]
    );
    if ($count > 0) {
        $_SESSION["deployment_id"] = $user->last_deployment_id;
    } else {
        $d = R::findOne("deployment", "@shared.user.id = ?",
            [$user->id]
        );
        if ($d == null) {
            die("INVALID");
        }
        $_SESSION["deployment_id"] = $d->id;
        $user->last_deployment_id = $d->id;
        R::store($user);
    }
} else {
    R::close();
    die("INVALID");
}

R::close();

?>