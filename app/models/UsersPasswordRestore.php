<?php declare(strict_types=1);

namespace app\models;

class UsersPasswordRestore extends Users
{
    public $dbFields = ['password', 'last_auth_at', 'fail_auth_counter', 'pw_restore_token', 'pw_token_sent_at'];

    /** @var array */
    private $data;

    /** @var array */
    private $user;

    /** @var array */
    private $errors = [];

    public function validateToken(array $data) : ?string
    {
        if (!isset($data['token']) || !$data['token']) {
            return 'Token not received';
        }

        $this->user = $this->getRow(['pw_restore_token' => $data['token']]);
        if (!$this->user) {
            return 'Token not exists';
        }

        $tokenHours = (new \DateTime())->diff(new \DateTime($this->user['pw_token_sent_at']))->h;
        if ($tokenHours > $this->config['user_password_token_ttl_hours']) {
            return 'Token is expired, you may send new again';
        }

        return null;
    }

    public function exec(array $data) : array
    {
        $this->data = $data;
        if($this->validate()) {
            $this->changePassword();
            $this->login($this->user);
        }

        return $this->errors;
    }

    private function validate() : bool
    {
        if($error = $this->validatePassword($this->data['password'])) {
            $this->errors['password'] = $error;
        } elseif(in_array($this->data['password'], [$this->user['login'], $this->user['email']])) {
            $this->errors['password'] = 'Password can\'t equal to login or e-mail';
        }

        if(!isset($this->data['repassword']) || !$this->data['repassword']) {
            $this->errors['repassword'] = 'Repeat password is required';
        } elseif($this->data['password'] && $this->data['password'] != $this->data['repassword']) {
            $this->errors['repassword'] = 'Passwords is not match';
        }

        return !$this->errors;
    }

    private function changePassword() : void
    {
        $this->update(['password' => $this->makePasswordHash($this->data['password'])], $this->user['id']);
    }
}