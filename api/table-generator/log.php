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

$queryLimit = strtotime("1 month ago");

$logs = R::find(
    'log',
    '@joined.food.deployment_id = ? AND date > ? ORDER BY `date` DESC',
    [$DEPLOYMENT->id, $queryLimit]
);
R::loadJoined($logs, 'food');

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
        $ts = strtotime($log->date);
        $day = $dayOfWeek[intval(date("w"))];
    ?>
        <tr>
            <th scope="row"><?= date("m. d.") . " ($day)" ?></th>
            <td><?= htmlspecialchars($log->food->name) ?></td>
            <td></td>
        </tr>
    <?php } ?>
</tbody>