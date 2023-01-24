<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}
use \RedBeanPHP\R as R;

$dish_fields = [0 => "soup", 3 => "dessert"];
$ms = [];
$mealtype = R::enum("mealtype:lunch");
foreach($dish_fields as $k => $v) {
    $ms[$k] = R::findOne('food', 'deployment_id = ? AND foodtype_id = ? AND mealtype_id & ? != 0 ORDER BY RAND() LIMIT 1', 
    [$DEPLOYMENT->id, R::enum("foodtype:$v")->id, $mealtype->id]);
}
$combo = R::findOne('dishcombo', 
    "@joined.food[as:main_course].deployment_id = ? AND 
     @joined.food[as:main_course].mealtype_id & ? != 0 
     ORDER BY RAND() LIMIT 1",
    [$DEPLOYMENT->id, $mealtype->id]);
if ($combo != NULL) {
    $ms[1] = $combo->fetchAs('food')->main_course;
    $ms[2] = $combo->fetchAs('food')->side_dish;
}

$users = R::find('user', '@shared.deployment.id = ? ORDER BY `user`.`name` ASC', [$DEPLOYMENT->id]);
$SCRIPTS[] = "/js/picker.js"

?>
<div class="container-fluid">
    <div class="row g-3 mb-4">
        <div class="col-sm order-sm-1 order-2">
            <h5>Kik esznek?</h5>
            <?php foreach ($users as $u) { ?>
            <div class="form-check">
                <input class="form-check-input userSelect" type="checkbox" value="<?= $u["id"] ?>" checked name="userSelect" id="userSelect-<?= $u["id"] ?>">
                <label class="form-check-label" for="userSelect-<?= $u["id"] ?>">
                    <?= htmlspecialchars((empty($u["name"])) ? $u["username"] : $u["name"] ); ?>
                </label>
            </div>
            <?php } ?>
        </div>
        <div class="col-sm order-sm-2 order-1">
            <div class="form-floating">
                <select class="form-select mealSelect" id="mealSelect" placeholder="a">
                    <option value="breakfast">Reggeli</option>
                    <option value="lunch" selected>Ebéd</option>
                    <option value="dinner">Vacsora</option>
                </select>
                <label for="mealSelect">Étkezés</label>
            </div>
        </div>
    </div>
    <div class="row col-sm-4 mx-auto">
        <button type="button" onclick="submit()" id="submitBtn" class="btn btn-primary">
            Generálás
        </button>
    </div>
    <div class="row" id="loadingSpinner" style="display: none;">
        <div class="col-1 mt-2 mx-auto">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    <div class="row" id="failAlert" style="display: none;">
        <div class="col-md-10 col-11 mt-3 mx-auto alert alert-danger" role="alert">
            A generátornak valami nem tetszett! <br>
            Tölts újra az oldalt és próbáld meg mégegyszer!
        </div>
    </div>
    <hr>
    <table class="table mx-auto" id="mealTable" style="width: fit-content;">
        
    </table>
</div>