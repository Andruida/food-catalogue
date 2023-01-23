<?php
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

$dish_fields = [
    "storedSoups"       => "soup", 
    "storedMainCourses" => "mainCourse", 
    "storedSideDishes"  => "sideDish", 
    "storedDesserts"    => "dessert"
];

if (empty($_GET["field"]) || !isset($dish_fields[$_GET["field"]])) {
    http_response_code(400);
    die();
}

$foods = R::find('food', 'deployment_id = ? AND foodtype_id = ?', 
    [
        $DEPLOYMENT->id, 
        R::enum("foodtype:".$dish_fields[$_GET["field"]])->id
    ]
);

foreach ($foods as $food) { ?>
    <option><?= htmlspecialchars($food->name) ?></option>
<?php } ?>