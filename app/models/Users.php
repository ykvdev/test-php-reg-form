<?php declare(strict_types=1);

namespace app\models;

use acurrieclark\PhpPasswordVerifier\Verifier;
use Respect\Validation\Validator;

class Users extends AbstractModel
{
    public const ROLE_ALL = 'all';
    public const ROLE_GUEST = 'guest';
    public const ROLE_USER = 'user';

    public $dbTable = 'users';
    public $dbPk = 'id';
    public $dbFields = ['id', 'login', 'email', 'password', 'full_name', 'registered_at', 'email_confirmed_at',
        'last_auth_at', 'fail_auth_counter', 'pw_restore_token', 'pw_token_sent_at'];

    public function login(array $user) : void
    {
        $user['last_auth_at'] = date('Y-m-d H:i:s');
        $this->update([
            'last_auth_at' => $user['last_auth_at'],
            'fail_auth_counter' => 0,
            'pw_restore_token' => null,
            'pw_token_sent_at' => null
        ], $user['id']);

        $_SESSION[$this->config['user_session_name']] = array_merge($user, [
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip' => $this->services->getClientIp()
        ]);
    }

    /**
     * @param string|null $field
     *
     * @return mixed|null
     */
    public function getAuthorised(string $field = null)
    {
        $data = $_SESSION[$this->config['user_session_name']] ?? null;
        $data = $data ? ($field ? $data[$field] : $data) : null;

        return $data;
    }

    /**
     * @param array $changes
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function updateAuthorised(array $changes) : void
    {
        if($this->getAuthorised()) {
            $_SESSION[$this->config['user_session_name']] =
                array_replace($_SESSION[$this->config['user_session_name']], $changes);
        } else {
            throw new \RuntimeException('User not authorized');
        }
    }

    public function logout() : bool
    {
        return session_destroy();
    }

    public function makePasswordHash(string $plainPassword) : string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function validatePasswordHash(string $plainPassword, string $hashedPassword) : bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function makeEmailConfirmToken(string $email, string $registeredAt) : string
    {
        return md5($email . $registeredAt);
    }

    public function makePasswordRestoreToken() : string
    {
        return uniqid((string)time(), true);
    }

    public function validateLogin(?string $login) : ?string
    {
        $error = null;

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

        if(!$password) {
            $error = 'Password is required';
        } else {
            $verifier = (new Verifier())
                ->setCheckContainsCapitals()
                ->setCheckContainsLetters()
                ->setCheckContainsNumbers()
                ->setMinLength(6)
                ->setMaxLength(20);
            if(!$verifier->checkPassword($password)) {
                $error = 'Password should be min: 6, max: 20, contains: letters, capitals and numbers';
            }
        }

        return $error;
    }

    public function validateFullName(?string $fullName) : ?string
    {
        $error = null;

        if(!$fullName) {
            $error = 'Full name is required';
        } elseif(Validator::regex('/[^a-zа-я\' ]/i')->validate($fullName)) {
            $error = 'Full name may contains letters and symbol: \'';
        } elseif (!Validator::length(3, 100)->validate($fullName)) {
            $error = 'Full name length wrong, min: 3, max: 100 symbols';
        }

        return $error;
    }
}