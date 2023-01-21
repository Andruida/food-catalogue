<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');
use \RedBeanPHP\R as R;
use \RedBeanPHP\Finder as Finder;

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
$DEPLOYMENT = R::findOne("deployment", 'id = ?', [$_SESSION["deployment_id"]]);
if ($USER == NULL || $DEPLOYMENT == NULL) {
    session_destroy();
    http_response_code(401);
    die();
}

$queryLimit = strtotime("1 month ago");


$logs = R::getAll(
    "SELECT f1.name as soup, f2.name as main_course, f3.name as side_dish, f4.name as dessert, 
            `log`.`date` as `date`
    FROM `log`
    LEFT JOIN food f1 ON f1.id = log.`soup_id`
    LEFT JOIN food f2 ON f2.id = log.`main_course_id`
    LEFT JOIN food f3 ON f3.id = log.`side_dish_id`
    LEFT JOIN food f4 ON f4.id = log.`dessert_id`
    WHERE 
        (f1.deployment_id = :did OR 
        f2.deployment_id = :did OR 
        f3.deployment_id = :did OR 
        f4.deployment_id = :did)
         AND 
        `log`.`date` > :ts
    ORDER BY `date` DESC, log.`id` DESC
    ;",
    ["did"=>$DEPLOYMENT->id, "ts" => $queryLimit]
);
// var_dump($ids);


$dayOfWeek = ["Vas", "Hét", "Ked", "Sze", "Csü", "Pén", "Szo"];

?>
<thead>
    <tr>
        <th scope="col">Dátum</th>
        <th scope="col">Étel</th>
        <th scope="col">Műveletek</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($logs as $log) {
        $ts = strtotime($log["date"]);
        $day = $dayOfWeek[intval(date("w"))];
        $logName = implode(", ", array_filter([
            $log["soup"],
            implode(" + ", array_filter([$log["main_course"], $log["side_dish"]])),
            $log["dessert"],
        ]));
    ?>
        <tr>
            <th scope="row"><?= date("m. d.") . " ($day)" ?></th>
            <td><?= htmlspecialchars($logName) ?></td>
            <td></td>
        </tr>
    <?php } ?>
</tbody>