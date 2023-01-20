<?php
/**
 * PHP class file autoloader on class instantiation
 */

// require_once($_SERVER["DOCUMENT_ROOT"] .'/vendor/autoload.php');

require_once($_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php");

spl_autoload_register('ClassLoader');

function ClassLoader($className){
    if (substr_compare($className, "Model_", 0) >= 0) {
        return;
    }
    $path = $_SERVER['DOCUMENT_ROOT'] . '/classes/';
    include $path . $className . '.php';
}
?>