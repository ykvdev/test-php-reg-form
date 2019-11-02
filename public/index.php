<?php declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

define('ENV_DEV', 'dev');
define('ENV_PROD', 'prod');

$env = getenv('APP_ENV');
$env = in_array($env, [ENV_DEV, ENV_PROD]) ? $env : ENV_DEV;
define('CUR_ENV', $env);

$config = array_merge_recursive(
    require __DIR__ . '/../app/configs/common.php',
    require __DIR__ . '/../app/configs/envs/' . CUR_ENV . '.php'
);

(new \app\web\Bootstrap($config))->dispatch();