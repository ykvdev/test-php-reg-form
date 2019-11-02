<?php declare(strict_types=1);

namespace app\models;

class Users extends AbstractModel
{
    private const SESSION_NAME = 'user';

    public $tableName = 'users';

    public $pkName = 'id';

    public function login(string $login, string $password) : bool
    {
        // get user data from DB
        // check pw
        // if pw wrong return false
        // put user data to session
        // return true
        // ...
    }
}