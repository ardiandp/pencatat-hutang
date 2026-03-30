<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

requireAuth();

$title = "Home";
$content = __DIR__ . "/../views/home.php";
require __DIR__ . "/../views/layouts/app.php";

?>
