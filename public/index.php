<?php declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

define('ENV_DEV', 'dev');
define('ENV_PROD', 'prod');

$env = getenv('APP_ENV');
$env = in_array($env, [ENV_DEV, ENV_PROD]) ? $env : ENV_DEV;
define('APP_ENV', $env);

// ONLY FOR PHP BUILT-IN SERVER...
if(APP_ENV == ENV_DEV && in_array($_SERVER['REQUEST_URI'], [
    '/assets/css/common.css',
    '/favicon.ico'
], true)) {
    header("Content-Type: text/css");
    header("X-Content-Type-Options: nosniff");
    echo file_get_contents(__DIR__ . $_SERVER['REQUEST_URI']);
    exit;
}

$config = array_merge_recursive(
    require __DIR__ . '/../app/configs/common.php',
    require __DIR__ . '/../app/configs/envs/' . APP_ENV . '.php'
);

(new \app\web\Bootstrap($config))->run();