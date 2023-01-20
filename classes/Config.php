<?php
class Config {
    public static function getConfig() {
        return parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/config/config.ini', true);
    }

    public static function getMySQLCredentials() {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/config/config.ini', true);

        return [
            "conn_str" => 'mysql:host='.$config["mysql"]["host"].';port='.$config["mysql"]["port"].';dbname='.$config["mysql"]["db"],
            "username" => $config["mysql"]["user"],
            "password" => $config["mysql"]["pw"],
        ];
    }
}
?>