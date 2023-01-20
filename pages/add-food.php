<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}
?>
<div class="container-fluid">
    <h4>Étel hozzáadása</h4>
    <div class="form-floating mt-4 mb-3">
        <input type="text" class="form-control required foodInput" id="foodName" placeholder="a">
        <label for="floatingInput">Étel neve</label>
        <div class="invalid-feedback" id="nameEmptyError">
            Az étel nevét kötelező megadni!
        </div>
        <div class="invalid-feedback" id="nameDuplicateError" style="display: none;">
            Ilyen étel már van az adatbázisban!
        </div>
    </div>
    <div class="form-floating mb-3">
        <textarea class="form-control foodInput" id="foodDesc" placeholder="a" style="height: 100px"></textarea>
        <label for="floatingInput">Leírás</label>
    </div>
    <div class="form-check">
        <input class="form-check-input foodInput" type="checkbox" value="" id="foodAddToLog">
        <label class="form-check-label" for="flexCheckDefault">
            Hozzáadás a naplóhoz a mai napra
        </label>
    </div>
    <div class="row col-sm-4 mt-4 mx-auto">
        <button type="button" onclick="submit()" id="submitBtn" class="btn btn-primary">
            Mentés
        </button>
    </div>
    <div class="row" id="loadingSpinner" style="display: none;">
        <div class="col-1 mt-2 mx-auto">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
<script src="/js/add-food.js"></script>