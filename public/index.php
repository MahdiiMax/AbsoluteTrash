<?php

declare(strict_types=1);

define("BASE_PATH", dirname(__DIR__));
require BASE_PATH . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
$app = require BASE_PATH . DIRECTORY_SEPARATOR . "bootstrap" . DIRECTORY_SEPARATOR . "app.php";

$app->handle();
