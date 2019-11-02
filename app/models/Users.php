<?php declare(strict_types=1);

namespace app\models;

use ParagonIE\AntiCSRF\AntiCSRF;
use Respect\Validation\Validator;

class Users extends AbstractModel
{
    private const SESSION_NAME = 'user';

    public $tableName = 'users';

    public $pkName = 'id';

    public $fields = ['id', 'login', 'email', 'password', 'full_name', 'registered_at', 'email_confirmed_at',
        'last_auth_at', 'email_confirm_token', 'email_token_sent_at'];

    public function validate(array $data) : array
    {
        $errors = [];
        if($error = $this->validateLogin($data['login'])) {
            $errors['login'] = $error;
        }
        if($error = $this->validateEmail($data['email'])) {
            $errors['email'] = $error;
        }
        if($error = $this->validatePassword($data['password'])) {
            $errors['password'] = $error;
        }
        if($error = $this->validateFullName($data['full_name'])) {
            $errors['full_name'] = $error;
        }

        return $errors;
    }

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

    public function getAuthorised() : ?array
    {
        return $_SESSION[static::SESSION_NAME] ?? null;
    }

    public function logout() : bool
    {
        return session_destroy();
    }

    //------------------------------------------------------------------------------------------------------------------

    private function passwordHash(string $plainPassword) : string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    private function passwordVerify(string $plainPassword, string $hashedPassword) : bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function makeEmailConfirmUrl(string $email, string $registeredAt) : string
    {
        return $this->config['base_url']
            . '/confirm-email/' . urlencode($email) . '/' . md5($email . $registeredAt);
    }

    private function validateEmailConfirmToken(string $token, string $email, string $registeredAt) : bool
    {
        return strcmp(md5($email . $registeredAt), $token) === 0;
    }

    private function validateLogin(string $login) : ?string
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
        } elseif($this->isExists(['login' => $login])) {
            $error = 'Login already exists';
        }

        return $error;
    }

    private function validateEmail(string $email) : ?string
    {
        $error = null;

        $email = trim($email);
        if(!$email) {
            $error = 'E-mail is required';
        } elseif(!Validator::email()->validate($email)) {
            $error = 'E-mail format is incorrect';
        } elseif (!Validator::length(null, 100)->validate($email)) {
            $error = 'E-mail length wrong, max: 100 symbols';
        } elseif($this->isExists(['email' => $email])) {
            $error = 'E-mail already exists';
        }

        return $error;
    }

    private function validatePassword(string $password) : ?string
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

    private function validateFullName(string $fullName) : ?string
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