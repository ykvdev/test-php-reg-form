<?php declare(strict_types=1);

// ONLY FOR PHP BUILT-IN SERVER ---------------------------------------
if(in_array($_SERVER['REQUEST_URI'], [
    '/assets/css/common.css',
    '/favicon.ico'
], true)) {
    header("Content-Type: text/css");
    header("X-Content-Type-Options: nosniff");
    echo file_get_contents(__DIR__ . $_SERVER['REQUEST_URI']);
    exit;
}
// ---------------------------------------------------------------------

session_start();

require __DIR__ . '/../vendor/autoload.php';

define('ENV_DEV', 'dev');
define('ENV_PROD', 'prod');

$env = getenv('APP_ENV');
$env = in_array($env, [ENV_DEV, ENV_PROD]) ? $env : ENV_DEV;
define('APP_ENV', $env);

$config = array_replace(
    require __DIR__ . '/../app/configs/common.php',
    require __DIR__ . '/../app/configs/envs/' . APP_ENV . '.php'
);

(new \app\Bootstrap($config))->run();