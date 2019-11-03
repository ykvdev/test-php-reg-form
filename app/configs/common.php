<?php declare(strict_types=1);

use \app\models\Users;
use \app\web\controllers\UserController;

return [
    'sqlite_db_file' => __DIR__ . '/../../data/sii_test_task.sqlite3',

    'view_renderer' => [
        'views_dir' => __DIR__ . '/../web/views',
        'views_ext' => 'phtml',
    ],

    'mailer' => [
        'from' => 'SSI Test Task <ssi.test.task@ukr.net>',
        'smtp_host' => 'smtp.ukr.net',
        'smtp_username' => 'ssi.test.task@ukr.net',
        'smtp_password' => 'sd12wefWE',
        'smtp_secure' => 'ssl',
    ],

    'captcha' => [
        'session_name' => 'captcha',
        'dev_phrase' => '555',
        'length' => 5,
        'width' => 131,
        'height' => 38
    ],

    'routes_cache_file' => __DIR__ . '/../../data/routes.cache',
    'routes_for_roles' => [
        Users::ROLE_USER => '/profile',
        Users::ROLE_GUEST => '/',
    ],
    'routes' => [
        [['GET', 'POST'], '/', UserController::class, 'register', Users::ROLE_GUEST],
        ['GET', '/confirm-email/{email}/{token}', UserController::class, 'confirmEmail', Users::ROLE_GUEST],
        [['GET', 'POST'], '/login', UserController::class, 'login', Users::ROLE_GUEST],

        ['GET', '/profile', UserController::class, 'profile', Users::ROLE_USER],
        ['GET', '/profile-edit', UserController::class, 'profileEdit', Users::ROLE_USER],
        ['GET', '/password-change', UserController::class, 'passwordChange', Users::ROLE_USER],
        ['GET', '/logout', UserController::class, 'passwordChange', Users::ROLE_USER],

        ['GET', '/captcha', UserController::class, 'captcha', Users::ROLE_ALL],
    ],
];