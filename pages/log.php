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
        <div class="row g-3 mb-3">
            <div class="col-sm-12 col-lg-4">
                <div class="form-floating">
                    <input type="text" list="storedSoups" class="form-control dishInput foodInput" id="soupName" placeholder="a">
                    <label for="soupName">Leves</label>
                    <div class="invalid-feedback">
                        A szöveg túl hosszú
                    </div>
                    <div class="valid-feedback">
                        Az étel létezik
                    </div>
                    <datalist id="storedSoups">
                        
                    </datalist>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="form-floating">
                    <input type="text" list="storedMainCourses" class="form-control dishInput foodInput" id="mainCourseName" placeholder="a">
                    <label for="mainCourseName">Főétel</label>
                    <div class="invalid-feedback">
                        A szöveg túl hosszú
                    </div>
                    <div class="valid-feedback">
                        Az étel létezik
                    </div>
                    <datalist id="storedMainCourses">
                        
                    </datalist>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="form-floating">
                    <input type="text" list="storedSideDishes" class="form-control dishInput foodInput" id="sideDishName" placeholder="a">
                    <label for="sideDishName">Köret (feltét)</label>
                    <div class="invalid-feedback">
                        A szöveg túl hosszú
                    </div>
                    <div class="valid-feedback">
                        Az étel létezik
                    </div>
                    <datalist id="storedSideDishes">
                        
                    </datalist>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-floating">
                    <input type="text" list="storedDesserts" class="form-control dishInput foodInput" id="dessertName" placeholder="a">
                    <label for="dessertName">Desszert</label>
                    <div class="invalid-feedback">
                        A szöveg túl hosszú
                    </div>
                    <div class="valid-feedback">
                        Az étel létezik
                    </div>
                    <datalist id="storedDesserts">
                        
                    </datalist>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-floating">
                    <input type="date" class="form-control foodInput" id="foodDate" placeholder="a" value="<?= date("Y-m-d") ?>">
                    <label for="foodDate">Dátum</label>
                    <div class="invalid-feedback">
                        A bejegyzés dátumát kötelező megadni!
                    </div>
                </div>
            </div>
        </div>
        <div class="form-check">
            <input class="form-check-input foodInput" type="radio" value="onlyExisting" name="foodEntryMode" id="foodOnlyExisting">
            <label class="form-check-label" for="foodOnlyExisting">
                Csak létező étel naplózható
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input foodInput" type="radio" checked value="createIfNeeded" name="foodEntryMode" id="foodCreateIfNeeded">
            <label class="form-check-label" for="foodCreateIfNeeded">
                Új étel felvétele, ha nem létezik még
            </label>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input foodInput" type="radio" value="noLog" name="foodEntryMode" id="foodNoLog">
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
        <div class="row" id="emptyAlert" style="display: none;">
            <div class="col-md-10 col-11 mt-3 mx-auto alert alert-warning" role="alert">
                Valamelyiket ki kell tölteni!
            </div>
        </div>
        <div class="row" id="failAlert" style="display: none;">
            <div class="col-md-10 col-11 mt-3 mx-auto alert alert-danger" role="alert">
                Uh! Jajj! oh uh ez nem jó... valami baj van! Megpróbálhatod később, de nem lesz jobb :c<br>
                Szólj valakinek, aki meg tudja javítani!
            </div>
        </div>
        <div class="row" id="notFoundAlert" style="display: none;">
            <div class="col-md-10 col-11 mt-3 mx-auto alert alert-danger" role="alert">
                Van olyan mező, amelyikben nem létező étel szerepel!
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