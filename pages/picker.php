<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}
use \RedBeanPHP\R as R;

$dish_fields = [0 => "soup", 3 => "dessert"];
$meals = [];
$mealtype = R::enum("mealtype:lunch");
foreach($dish_fields as $k => $v) {
    $meals[$k] = R::findOne('food', 'deployment_id = ? AND foodtype_id = ? AND mealtype_id & ? != 0 ORDER BY RAND() LIMIT 1', 
    [$DEPLOYMENT->id, R::enum("foodtype:$v")->id, $mealtype->id]);
}
$combo = R::findOne('dishcombo', 
    "@joined.food[as:main_course].deployment_id = ? AND 
     @joined.food[as:main_course].mealtype_id & ? != 0 
     ORDER BY RAND() LIMIT 1",
    [$DEPLOYMENT->id, $mealtype->id]);
if ($combo != NULL) {
    $meals[1] = $combo->fetchAs('food')->main_course;
    $meals[2] = $combo->fetchAs('food')->side_dish;
}



?>
<div class="container-fluid">
    <div class="">
        <h2 class="text-center">Mai ajánlat</h2>
        <table class="table mx-auto" style="width: fit-content;">
            <tbody>
                <?php if (!empty($meals[0])) { ?>
                    <tr><th>Leves:</th><td><?= htmlspecialchars($meals[0]->name) ?></td></tr>
                <?php } if (!empty($combo)) { ?>
                    <tr><th>Második:</th><td><?= htmlspecialchars(implode(" + ", array_filter([$meals[1]->name, $meals[2]->name]))) ?></td></tr>
                <?php } if (!empty($meals[3])) { ?>
                    <tr><th>Desszert:</th><td><?= htmlspecialchars($meals[3]->name) ?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>