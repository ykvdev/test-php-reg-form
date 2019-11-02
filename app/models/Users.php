<?php declare(strict_types=1);

namespace app\models;

class Users extends AbstractModel
{
    private const SESSION_NAME = 'user';

    public $tableName = 'users';

    public $pkName = 'id';

    public function register(array $formData) : array
    {

    }
}