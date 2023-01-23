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
$DEPLOYMENT = R::findOne("deployment", 
    'deployment.id = ? AND @shared.user.id = ?', 
    [$_SESSION["deployment_id"], $_SESSION["user_id"]]
);
if ($USER == NULL || $DEPLOYMENT == NULL) {
    session_destroy();
    http_response_code(401);
    die();
}

$queryLimit = strtotime("1 month ago");


$logs = R::getAll(
    "SELECT f1.name as soup, f1.id as soup_id, 
            f2.name as main_course, f3.name as side_dish, 
            f4.name as dessert, f4.id as dessert_id,
            d.id as `dishcombo_id`, 
            `log`.`date` as `date`, `m`.`name` as `mealtype`,
            `r1`.`rating` as `soup_rating`,
            `r2`.`rating` as `dishcombo_rating`,
            `r4`.`rating` as `dessert_rating`
    FROM `log`
    LEFT JOIN food f1 ON f1.id = log.`soup_id`
    LEFT JOIN rating r1 ON (r1.`food_id` = log.`soup_id` AND r1.user_id = :userid)
    LEFT JOIN dishcombo d ON d.id = log.`dishcombo_id`
    LEFT JOIN rating r2 ON (r2.`dishcombo_id` = log.`dishcombo_id` AND r2.user_id = :userid)
    LEFT JOIN food f2 ON f2.id = d.`main_course_id`
    LEFT JOIN food f3 ON f3.id = d.`side_dish_id`
    LEFT JOIN food f4 ON f4.id = log.`dessert_id`
    LEFT JOIN rating r4 ON (r4.`food_id` = log.`dessert_id` AND r4.user_id = :userid)
    LEFT JOIN mealtype m ON m.id = log.`mealtype_id`
    WHERE 
        `log`.`date` > :ts AND
        (f1.deployment_id = :did OR 
        f2.deployment_id = :did OR 
        f3.deployment_id = :did OR 
        f4.deployment_id = :did)
    ORDER BY `date` DESC, log.`mealtype_id` DESC
    ;",
    ["did"=>$DEPLOYMENT->id, "ts" => $queryLimit, "userid" => $USER->id]
);
// var_dump($ids);


$dayOfWeek = ["Vas", "Hét", "Ked", "Sze", "Csü", "Pén", "Szo"];
$mealMap = [
    "BREAKFAST" => "Reggeli",
    "LUNCH" => "Ebéd",
    "DINNER" => "Vacsora"
];

?>
<thead>
    <tr>
        <th scope="col">Dátum</th>
        <th class="d-none d-sm-table-cell" scope="col">Étkezés</th>
        <th scope="col">Étel</th>
        <!-- <th scope="col">Műveletek</th> -->
    </tr>
</thead>
<tbody>
    <?php foreach ($logs as $log) {
        var_dump($log);
        $ts = strtotime($log["date"]);
        $day = $dayOfWeek[intval(date("w", $ts))];
        $logName = "";
        foreach (["soup", "main_course", "dessert"] as $k) {
            if (empty($log[$k])) continue;
            if (strlen($logName) != 0) $logName .= ', ';
            if ($k == "main_course") {
                $linkClass = ($log["dishcombo_rating"] === NULL) ? "link-primary" : "link-dark";
                $rating = ($log["dishcombo_rating"] === NULL) ? "false" : $log["dishcombo_rating"];
                $titleRating = ($log["dishcombo_rating"] === NULL) ? "" : floatval($log["dishcombo_rating"])*10;

                $logName .= '<a class="'.$linkClass.' link" onclick="openRating('.$log["dishcombo_id"].', \'dishcombo\', '.$rating.', \'';
                $logName .= htmlspecialchars(implode(" + ", array_filter([$log["main_course"],$log["side_dish"]])));
                $logName .= '\')" title="'.$titleRating.'">';
                $logName .= htmlspecialchars(implode(" + ", array_filter([$log["main_course"],$log["side_dish"]])));
                $logName .= '</a>';
            } else {
                $linkClass = ($log[$k."_rating"] === NULL) ? "link-primary" : "link-dark";
                $rating = ($log[$k."_rating"] === NULL) ? "false" : $log[$k."_rating"];
                $titleRating = ($log[$k."_rating"] === NULL) ? "" : floatval($log[$k."_rating"])*10;

                $logName .= '<a class="'.$linkClass.' link" onclick="openRating('.$log[$k."_id"].', \'food\', '.$rating.', \'';
                $logName .= htmlspecialchars($log[$k]);
                $logName .= '\')" title="'.$titleRating.'">';
                $logName .= htmlspecialchars($log[$k]);
                $logName .= '</a>';
            }
        }
    ?>
        <tr>
            <th scope="row"><?= date("m. d.", $ts) . "<br>($day)" ?></th>
            <td class="d-none d-sm-table-cell"><?= htmlspecialchars($mealMap[$log["mealtype"]]) ?></td>
            <td><?= $logName ?></td>
            <!-- <td class="text-center fs-4 align-middle">
                
                <a class="link-warning me-2 me-sm-1"><i class="fa-solid fa-star"></i></a>
                <a onclick="startEditing()" class="primary"><i class="fa-solid fa-pen-to-square"></i></a>
                
            </td> -->
        </tr>
    <?php } ?>
</tbody>