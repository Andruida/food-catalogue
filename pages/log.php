<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}

$SCRIPTS[] = "/js/log.js";

?>
<div class="container-fluid">
    <div class="mb-4">
        <h4>Étel naplózása</h4>
        <div class="row">
            <div class="col-md">
                <div class="form-floating mb-3">
                    <input type="text" list="storedFoods" class="form-control required foodInput" id="foodName" placeholder="a">
                    <label for="foodName">Étel neve</label>
                    <div class="invalid-feedback" id="nameEmptyError" style="display: none;">
                        Az étel nevét kötelező megadni!
                    </div>
                    <div class="invalid-feedback" id="nameNotFoundError" style="display: none;">
                        Nem létezik :c
                    </div>
                    <datalist id="storedFoods">
                        
                    </datalist>
                </div>
            </div>
            <div class="col-md">
                <div class="form-floating mb-3">
                    <input type="date" class="form-control required foodInput" id="foodDate" placeholder="a" value="<?= date("Y-m-d") ?>">
                    <label for="foodDate">Dátum</label>
                    <div class="invalid-feedback" id="dateEmptyError">
                        A bejegyzés dátumát kötelező megadni!
                    </div>
                </div>
            </div>
        </div>
        <div class="form-check">
            <input class="form-check-input foodInput" type="radio" checked value="" name="foodEntryMode" id="foodOnlyExisting">
            <label class="form-check-label" for="foodCreateIfNeeded">
                Csak létező étel naplózható
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input foodInput" type="radio" checked value="" name="foodEntryMode" id="foodCreateIfNeeded">
            <label class="form-check-label" for="foodCreateIfNeeded">
                Új étel felvétele, ha nem létezik még
            </label>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input foodInput" type="radio" value="" name="foodEntryMode" id="foodNoLog">
            <label class="form-check-label" for="foodNoLog">
                Új étel felvétele naplózás nélkül
            </label>
        </div>
        <div class="row col-sm-4 mx-auto">
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
        <div class="row" id="successAlert" style="display: none;">
            <div class="col-md-10 col-11 mt-3 mx-auto alert alert-success" role="alert">
                Sikeresen hozzáadva!
            </div>
        </div>
        <div class="row" id="failAlert" style="display: none;">
            <div class="col-md-10 col-11 mt-3 mx-auto alert alert-danger" role="alert">
                Uh! Jajj! oh uh ez nem jó... valami baj van! Megpróbálhatod később, de nem lesz jobb :c<br>
                Szólj valakinek, aki meg tudja javítani!
            </div>
        </div>
    </div>
    <hr>
    <div>
        <h4>Napló</h4>
        <table class="table table-striped mt-4 loadTable" id="logTable" data-generator="/api/table-generator/log.php">
            
        </table>
    </div>
</div>