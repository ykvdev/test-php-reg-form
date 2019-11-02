<?php declare(strict_types=1);

namespace app\models;

use Respect\Validation\Validator;

class Users extends AbstractModel
{
    private const SESSION_NAME = 'user';

    public $tableName = 'users';

    public $pkName = 'id';

    public $fields = ['id', 'login', 'email', 'password', 'full_name', 'registered_at', 'email_confirmed_at',
        'last_auth_at', 'email_confirm_token', 'email_token_sent_at'];

    public function register(array $validatedData) : void
    {
        $validatedData['password'] = $this->passwordHash($validatedData['password']);
        $validatedData['registered_at'] = date('Y-m-d H:i:s');
        $validatedData['id'] = $this->insert($validatedData);

        $url = $this->makeEmailConfirmUrl($validatedData['email'], $validatedData['registered_at']);
        $this->services->mailer()->send(
            $validatedData['full_name'], $validatedData['email'],
            'Confirm your E-mail', 'mails/register',
            ['full_name' => $validatedData['full_name'], 'url' => $url]
        );
    }

    public function activate(string $email) : void
    {
        $this->update(['email_confirmed_at' => date('Y-m-d H:i:s')], ['email' => $email]);
    }

    public function login(string $loginOrEmail) : void
    {
        $_SESSION[static::SESSION_NAME] =
            $this->getRow(['login' => $loginOrEmail])
            ?? $this->getRow(['email' => $loginOrEmail]);
    }

    public function getAuthorised() : ?array
    {
        return $_SESSION[static::SESSION_NAME] ?? null;
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

    public function makeEmailConfirmUrl(string $email, string $registeredAt) : string
    {
        return $this->config['base_url']
            . '/confirm-email/' . urlencode($email) . '/' . md5($email . $registeredAt);
    }

    public function validateEmailConfirmToken(?string $token, ?string $email) : ?string
    {
        if($error = $this->validateEmail($email ?? null, true)) {
            return $error;
        }

        $user = $this->getRow(['email' => $email], ['registered_at', 'email_confirmed_at']);
        if($user['email_confirmed_at']) {
            return 'This e-mail already confirmed';
        }

        if(strcmp(md5($email . $user['registered_at']), $token) !== 0) {
            return 'E-mail confirmation token incorrect';
        }

        return null;
    }

    public function validateLogin(?string $login, bool $mustExists = false) : ?string
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

        $exists = $this->isExists(['login' => $login]);
        if(!$mustExists && $exists) {
            $error = 'Login already exists';
        } elseif($mustExists && !$exists) {
            $error = 'Login not exists';
        }

        return $error;
    }

    public function validateEmail(?string $email, bool $mustExists = false) : ?string
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

        $exists = $this->isExists(['email' => $email]);
        if(!$mustExists && $exists) {
            $error = 'E-mail already exists';
        } elseif($mustExists && !$exists) {
            $error = 'E-mail not exists';
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