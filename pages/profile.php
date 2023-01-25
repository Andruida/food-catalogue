<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}

$SCRIPTS[] = "/js/profile.js";
?>
<div class="container-fluid">
    <h4>Jelszó csere</h4>
    <div class="row mt-2 mb-4 g-3">
        <div class="col-sm">
            <div class="form-floating">
                <input type="password" class="form-control required userInput" id="newPassword" name="newPassword" placeholder="Gyula" spellcheck="false">
                <label for="newPassword">Jelszó</label>
                <div class="invalid-feedback">
                    Lehetne egy cseppet erősebb, mondjuk egyezzünk meg legalább nyolc karakterben és én boldog leszek<br>
                    (nem mintha egy ilyen felület olyan kritikus személyes adatokat tárolna...)
                </div>
            </div>
        </div>
        <div class="col-sm">
            <div class="form-floating">
                <input type="password" class="form-control required userInput" id="newPasswordAgain" name="newPasswordAgain" placeholder="Gyula" spellcheck="false">
                <label for="newPasswordAgain">Jelszó újra</label>
                <div class="invalid-feedback">
                    Ezt most nem sikerült beírni helyesen, de sebaj, próbáld meg újra és biztosan menni fog! Én hiszek benned!
                </div>
            </div>
        </div>
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
    <div class="row" id="successAlert" style="display: none;">
        <div class="col-md-10 col-11 mt-3 mx-auto alert alert-success" role="alert">
            A jelszavad sikeresen megváltoztattad!
        </div>
    </div>
    <div class="row" id="failAlert" style="display: none;">
        <div class="col-md-10 col-11 mt-3 mx-auto alert alert-danger" role="alert">
            Na ezt benéztük! Valamiért nem tudtuk megváltoztatni a jelszavad :/
        </div>
    </div>
</div>