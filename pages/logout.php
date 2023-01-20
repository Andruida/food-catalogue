<?php
if (!isset($INCLUDED)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/index.php");
    die();
}

header("Location: /login");
session_destroy();
ob_end_clean();
die();
?>