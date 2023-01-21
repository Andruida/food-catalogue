<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}
use \RedBeanPHP\R as R;

$dish_fields = [0 => "soup", 3 => "dessert"];
$meals = [];
foreach($dish_fields as $k => $v) {
    $meals[$k] = R::findOne('food', 'type_id = ? ORDER BY RAND() LIMIT 1', [R::enum("foodtype:$v")->id]);
}
$combo = R::findOne('dishcombo', 'ORDER BY RAND() LIMIT 1');
$meals[1] = $combo->fetchAs('food')->main_course;
$meals[2] = $combo->fetchAs('food')->side_dish;



?>
<div class="container-fluid">
    <div class="">
        <h2 class="text-center">Mai ajánlat</h2>
        <table class="table mx-auto" style="width: fit-content;">
            <tbody>
        <tr><th>Leves:</th><td><?= htmlspecialchars($meals[0]->name) ?></td></tr>
        <tr><th>Második:</th><td><?= htmlspecialchars(implode(" + ", array_filter([$meals[1]->name, $meals[2]->name]))) ?></td></tr>
        <tr><th>Desszert:</th><td><?= htmlspecialchars($meals[3]->name) ?></td></tr>
            </tbody>
        </table>
    </div>
</div>