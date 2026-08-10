<?php

declare(strict_types=1);

use Trash\Support\Dotenv;
use Trash\Foundation\Application;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'helpers.php';

(new Dotenv())->load(dirname(__DIR__));
return new Application(dirname(__DIR__));
