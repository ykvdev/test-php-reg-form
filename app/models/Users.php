<?php declare(strict_types=1);

namespace app\models;

use Respect\Validation\Validator;

class Users extends AbstractModel
{
    public const ROLE_ALL = 'all';
    public const ROLE_GUEST = 'guest';
    public const ROLE_USER = 'user';

    private const SESSION_NAME = 'user';

    public $dbTable = 'users';
    public $dbPk = 'id';
    public $dbFields = ['id', 'login', 'email', 'password', 'full_name', 'registered_at', 'email_confirmed_at', 'last_auth_at'];

    public function login(array $user) : void
    {
        $user['last_auth_at'] = date('Y-m-d H:i:s');
        $this->update(['last_auth_at' => $user['last_auth_at']], $user['id']);

        $_SESSION[self::SESSION_NAME] = $user;
    }

    public function getAuthorised() : ?array
    {
        return $_SESSION[self::SESSION_NAME] ?? null;
    }

    public function logout() : bool
    {
        return session_destroy();
    }

    public function passwordHash(string $plainPassword) : string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function passwordVerify(string $plainPassword, string $hashedPassword) : bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function validateLogin(?string $login) : ?string
    {
        $error = null;

        $login = trim($login);
        if(!$login) {
            $error = 'Login is required';
        } elseif(!Validator::noWhitespace()->validate($login)) {
            $error = 'Login can\'t contain white spaces';
        } elseif(!Validator::alnum('.-')->validate($login)) {
            $error = 'Login may contains numbers, latin letters, and symbols: . -';
        } elseif (!Validator::length(3, 20)->validate($login)) {
            $error = 'Login length wrong, min: 3, max: 20 symbols';
        }

        return $error;
    }

    public function validateEmail(?string $email) : ?string
    {
        $error = null;

        $email = trim($email);
        if(!$email) {
            $error = 'E-mail is required';
        } elseif(!Validator::email()->validate($email)) {
            $error = 'E-mail format is incorrect';
        } elseif (!Validator::length(null, 100)->validate($email)) {
            $error = 'E-mail length wrong, max: 100 symbols';
        }

        return $error;
    }

    public function validatePassword(?string $password) : ?string
    {
        $error = null;

        $password = trim($password);
        if(!$password) {
            $error = 'Password is required';
        } elseif (!Validator::length(6, 100)->validate($password)) {
            $error = 'Password length wrong, min: 6, max: 100 symbols';
        }

        return $error;
    }

    public function validateFullName(?string $fullName) : ?string
    {
        $error = null;

        $fullName = trim($fullName);
        if(!$fullName) {
            $error = 'Full name is required';
        } elseif(!Validator::regex('/[a-zа-я\' ]/i')->validate($fullName)) {
            $error = 'Full name may contains letters and symbol: \'';
        } elseif (!Validator::length(3, 100)->validate($fullName)) {
            $error = 'Full name length wrong, min: 3, max: 100 symbols';
        }

        return $error;
    }
}