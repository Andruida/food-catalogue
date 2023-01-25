<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}

if (!empty($USER)) {
    header("Location: /");
    ob_end_clean();
    die();
}

$SCRIPTS[] = "/js/login.js";
?>
<div class="container-fluid">
    <h4>Bejelentkezés</h4>
    <div class="form-floating mt-4 mb-3">
        <input type="text" class="form-control required userInput" id="username" placeholder="username" spellcheck="false">
        <label for="username">Felhasználónév</label>
        <div class="invalid-feedback">
            Vagy nem létezik ilyen címmel fiók
        </div>
    </div>
    <div class="form-floating mb-3">
        <input type="password" class="form-control required userInput" id="password" placeholder="Gyula" spellcheck="false">
        <label for="password">Jelszó</label>
        <div class="invalid-feedback">
            Vagy téves jelszót adtál meg!
        </div>
    </div>
    <div class="row col-sm-4 mt-4 mx-auto">
        <button type="button" onclick="submit()" id="submitBtn" class="btn btn-primary">
            Bejelentkezés
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