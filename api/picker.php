<?php

$RANDOM_RANGE = 0.5;
$RANDOM_MIN = 1-($RANDOM_RANGE/2);
$RANDOM_MAX = 1+($RANDOM_RANGE/2);

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
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
$mealType_enum = ["breakfast", "lunch", "dinner"];
if (!isset($_GET["mealType"]) || array_search($_GET["mealType"], $mealType_enum) === false) {
    http_response_code(400);
    die();
}

if (!isset($_GET["users"]) || empty($_GET["users"])) {
    http_response_code(400);
    die();
}

$userIDList = explode(",", $_GET["users"]);
$userCount = R::count('user', 
    "@shared.deployment.id = ? AND user.id IN (".R::genSlots($userIDList).")", 
    array_merge([$DEPLOYMENT->id], $userIDList)
);

if ($userCount !== count($userIDList)) {
    die("NOTFOUND");
}

$courses = [];
$courses["soup"] = R::getRow(
    "SELECT f.id, f.name,
        IFNULL(DATEDIFF(NOW(), MAX(l.date)), 100) * 
        IFNULL(EXP( SUM( LOG( r.rating ) ) / COUNT( r.rating ) ), 1) *
        (RAND() * ($RANDOM_MAX - $RANDOM_MIN) + $RANDOM_MIN) as score
    FROM `food` f
    LEFT JOIN log l ON f.id = l.soup_id
    LEFT JOIN rating r ON f.id = r.food_id
    WHERE 
        f.deployment_id = ? AND 
        f.mealtype_id & ? != 0 AND 
        f.foodtype_id = ? AND 
        (r.user_id IS NULL OR r.user_id IN (".R::genSlots($userIDList)."))
    GROUP BY f.id, f.name ORDER BY `score` DESC LIMIT 1;",
    array_merge([
        $DEPLOYMENT->id, 
        R::enum("mealtype:".$_GET["mealType"])->id,
        R::enum("foodtype:soup")->id
    ], $userIDList)
);

$courses["dishcombo"] = R::getRow(
    "SELECT 
        d.id as dishcombo_id,
        f1.id as main_course_id, f1.name as main_course_name,
        f2.id as side_dish_id, f2.name as side_dish_name,
        IFNULL(DATEDIFF(NOW(), 
            GREATEST(
                IFNULL(MAX(l1.date), 0), 
                IFNULL(MAX(l2.date), 0)
            )
        ), 100) * 
        IFNULL(EXP( SUM( LOG( r.rating ) ) / COUNT( r.rating ) ), 1) *
        (RAND() * ($RANDOM_MAX - $RANDOM_MIN) + $RANDOM_MIN) as score
    FROM `dishcombo` d
    LEFT JOIN dishcombo d1 ON d.main_course_id = d1.main_course_id
    LEFT JOIN dishcombo d2 ON d.side_dish_id = d2.side_dish_id
    LEFT JOIN log l1 ON d1.id = l1.dishcombo_id
    LEFT JOIN log l2 ON d2.id = l2.dishcombo_id
    LEFT JOIN food f1 ON d.main_course_id = f1.id
    LEFT JOIN food f2 ON d.side_dish_id = f2.id
    LEFT JOIN rating r ON d.id = r.dishcombo_id
    WHERE 
        f1.deployment_id = ? AND 
        f1.mealtype_id & ? != 0 AND 
        (r.user_id IS NULL OR r.user_id IN (".R::genSlots($userIDList)."))
    GROUP BY d.id, f1.id, f2.id, f1.name, f2.name ORDER BY `score` DESC LIMIT 1;",
    array_merge([
        $DEPLOYMENT->id, 
        R::enum("mealtype:".$_GET["mealType"])->id
    ], $userIDList)
);

$courses["dessert"] = R::getRow(
    "SELECT f.id, f.name,
        IFNULL(DATEDIFF(NOW(), MAX(l.date)), 100) * 
        IFNULL(EXP( SUM( LOG( r.rating ) ) / COUNT( r.rating ) ), 1) *
        (RAND() * ($RANDOM_MAX - $RANDOM_MIN) + $RANDOM_MIN) as score
    FROM `food` f
    LEFT JOIN log l ON f.id = l.dessert_id
    LEFT JOIN rating r ON f.id = r.food_id
    WHERE 
        f.deployment_id = ? AND 
        f.mealtype_id & ? != 0 AND 
        f.foodtype_id = ? AND 
        (r.user_id IS NULL OR r.user_id IN (".R::genSlots($userIDList)."))
    GROUP BY f.id, f.name ORDER BY `score` DESC LIMIT 1;",
    array_merge([
        $DEPLOYMENT->id, 
        R::enum("mealtype:".$_GET["mealType"])->id,
        R::enum("foodtype:dessert")->id
    ], $userIDList)
);

// var_dump($courses);

// $foods = R::find("food", "deployment_id = ? AND mealtype_id & ? != 0", [$DEPLOYMENT->id, R::enum("mealtype:".$_GET["mealtype"])->id])


?>
<tbody>
    <?php if (!empty($courses["soup"])) { ?>
        <tr>
            <th>Leves:</th>
            <td><?= htmlspecialchars($courses["soup"]["name"]) ?></td>
            <!-- <td><?= round($courses["soup"]["score"], 2) ?></td> -->
        </tr>
    <?php } if (!empty($courses["dishcombo"])) { ?>
        <tr>
            <th>Második:</th>
            <td><?= htmlspecialchars(implode(" + ", array_filter([$courses["dishcombo"]["main_course_name"], $courses["dishcombo"]["side_dish_name"]]))) ?></td>
            <!-- <td><?= round($courses["dishcombo"]["score"], 2) ?></td> -->
        </tr>
    <?php } if (!empty($courses["dessert"])) { ?>
        <tr>
            <th>Desszert:</th>
            <td><?= htmlspecialchars($courses["dessert"]["name"]) ?></td>
            <!-- <td><?= round($courses["dessert"]["score"], 2) ?></td> -->
        </tr>
    <?php } ?>
</tbody>