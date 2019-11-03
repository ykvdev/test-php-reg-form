<?php declare(strict_types=1);

namespace app\models;

class UsersLogin extends Users
{
    public $dbFields = [];

    /** @var array */
    private $data;

    /** @var array */
    private $user;

    /** @var array */
    private $errors = [];

    public function exec(array $data) : array
    {
        $this->data = $data;
        if($this->validate()) {
            $this->login($this->user);
        }

        return $this->errors;
    }

    private function validate() : bool
    {
        $isIdentityLogin = strstr($this->data['identity'], '@') === false;
        if($error = $isIdentityLogin
            ? $this->validateLogin($this->data['identity'])
            : $this->validateEmail($this->data['identity'])) {
            $this->errors['identity'] = $error;
            return false;
        }

        $this->user = $this->getRow($isIdentityLogin
            ? ['login' => $this->data['identity']]
            : ['email' => $this->data['identity']]);
        if(!$this->user) {
            $this->errors['identity'] = ($isIdentityLogin ? 'Login' : 'E-mail') . ' not found';
            return false;
        }

        if(!$this->user['email_confirmed_at']) {
            $this->errors['identity'] = 'Your e-mail'
                . ($isIdentityLogin ? ' ' . $this->user['email'] : '')
                . ' is not confirmed';
            return false;
        }

        if(!$this->passwordVerify($this->data['password'], $this->user['password'])) {
            $this->errors['password'] = 'Password incorrect';
            return false;
        }

        return true;
    }
}