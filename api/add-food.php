<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die();
};

if (!isset($_POST["name"])) {
    http_response_code(400);
    echo "name hiányzik!";
    die();
}

require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');
use \RedBeanPHP\R as R;

$dbcreds = Config::getMySQLCredentials();
R::setup($dbcreds["conn_str"], $dbcreds["username"], $dbcreds["password"]);
unset($dbcreds);

$count = R::count("food", "name = ?", [$_POST["name"]]);

if ($count == 0) {
    $food = R::dispense("food");
    $food->name = $_POST["name"];
    $food->description = $_POST["description"];

    R::store($food);
} else {
    R::close();
    die("DUPLICATE");
}

R::close();

?>