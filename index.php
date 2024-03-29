<?php
// Set a global constant, so scripts can not be called directly from browser
$INCLUDED = true;
require_once($_SERVER["DOCUMENT_ROOT"] . '/classloader.php');

use \RedBeanPHP\R as R;

$dbcreds = Config::getMySQLCredentials();
R::setup($dbcreds["conn_str"], $dbcreds["username"], $dbcreds["password"]);
R::freeze($dbcreds["frozen"]);
unset($dbcreds);

ob_start();

session_start([
    'cookie_lifetime' => 86400,
    'gc_maxlifetime' => 86400,
]);

$USER = NULL;
if (isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {
    $USER = R::findOne("user", 'id = ?', [$_SESSION["user_id"]]);
}

$DEPLOYMENT = NULL;
if (!empty($USER) && isset($_SESSION["deployment_id"]) && !empty($_SESSION["deployment_id"])) {
    if ($USER->last_deployment_id !== $_SESSION["deployment_id"]) {
        $_SESSION["deployment_id"] = $USER->last_deployment_id;
    }
    $DEPLOYMENT = R::findOne("deployment", 
        'deployment.id = ? AND @shared.user.id = ?', 
        [$_SESSION["deployment_id"], $_SESSION["user_id"]]
    );
}
if ($USER == NULL || $DEPLOYMENT == NULL) {
    session_destroy();
    $USER = null;
    $DEPLOYMENT = null;
}

$queryMarker = strpos($_SERVER["REQUEST_URI"], "?");
$URI = ($queryMarker) ? substr($_SERVER["REQUEST_URI"], 0, $queryMarker) : $_SERVER["REQUEST_URI"];
if (strlen($URI) != 1 && strlen($URI) - 1 == strrpos($URI, "/")) {
    $URI = substr($URI, 0, strlen($URI) - 1); // Get rid of trailing slash
}


$pageNames = [];

$curpage = "login";

$map = [
    "/login" => "login",
    "/logout" => "logout"
];

$deployments = [];

if (!empty($USER)) {
    $pageNames["/log"] = "Napló";

    $curpage = "picker";
    $map["/"] = "picker";
    $map["/log"] = "log";
    $map["/add-food"] = "add-food";
    $map["/settings"] = "settings";
    $map["/profile"] = "profile";

    $deployments = R::find("deployment", 
        "@shared.user.id = ? AND deployment.id != ? ORDER BY `deployment`.`name` ASC", 
        [$USER->id, $DEPLOYMENT->id]
    );
} else {
    $pageNames["/login"] = "Bejelentkezés";
}

if (isset($map[$URI])) {
    $curpage = $map[$URI];
}

$required_page_file = $_SERVER["DOCUMENT_ROOT"] . "/pages/" . $curpage . ".php";

$SCRIPTS = [];

?>
<!doctype html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mit egyek ma?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/625577689c.js" crossorigin="anonymous"></script>
    <style>
        .link {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-expand-md bg-light mt-2">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Mit egyek ma?</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mb-2 mb-md-0 w-100">
                        <?php foreach ($pageNames as $link => $name) { ?>
                            <li class="nav-item">
                                <a class="nav-link<?= ($URI == $link) ? " active\" aria-current=\"page" : "" ?>" href="<?= $link ?>"><?= $name ?></a>
                            </li>
                        <?php } ?>
                        <?php if (!empty($USER)) { ?>
                        <li class="nav-item dropdown ms-md-auto">
                            <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Háztartás: [<?= htmlspecialchars($DEPLOYMENT->name) ?>]
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/settings" 
                                    class="dropdown-item<?= ($URI == "/settings") ? " active\" aria-current=\"page" : "" ?>" >
                                    Beállítások
                                </a></li>
                                <?php if (count($deployments) > 0) { ?>
                                <li><hr></li>
                                <?php foreach ($deployments as $d) { ?>
                                <li><a class="dropdown-item link" onclick="changeDeployment(<?= $d->id ?>)"><?= htmlspecialchars($d->name) ?></a></li>
                                <?php }} ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($URI == "/profile") ? " active\" aria-current=\"page" : "" ?>" href="/profile">[<?= htmlspecialchars($USER->name ?? $USER->username)?>]</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($URI == "/logout") ? " active\" aria-current=\"page" : "" ?>" href="/logout">Kijelentkezés</a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="mx-auto mt-4" style="max-width: 600px;">
            <?php require($required_page_file); ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/global.js"></script>
    <?php foreach ($SCRIPTS as $src) { ?>
        <script src="<?= $src ?>"></script>
    <?php } ?>
</body>

</html>

<?php
R::close();

?>