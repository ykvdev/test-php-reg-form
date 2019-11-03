<?php declare(strict_types=1);

namespace app\models;

class UsersConfirmEmailAndAuth extends Users
{
    public $dbFields = ['email_confirmed_at', 'last_auth_at', 'fail_auth_counter'];

    /** @var array */
    private $data;

    /** @var array */
    private $user;

    /** @var string */
    private $error;

    public function exec(array $data) : string
    {
        $this->data = $data;
        if($this->validate()) {
            $this->activate();
            $this->login($this->user);
        }

        return $this->error;
    }

    private function validate() : bool
    {
        if($this->error = $this->validateEmail($this->data['email'] ?? null)) {
            return false;
        }

        $this->user = $this->getRow(['email' => $this->data['email']]);
        if(!$this->user) {
            $this->error = 'E-mail not exists';
            return false;
        }

        if($this->user['email_confirmed_at']) {
            $this->error = 'This e-mail already confirmed';
            return false;
        }

        if(strcmp(
            $this->makeEmailConfirmToken($this->data['email'], $this->user['registered_at']),
            $this->data['token'] ?? null) !== 0
        ) {
            $this->error = 'E-mail confirmation token incorrect';
            return false;
        }

        return true;
    }

    private function activate() : void
    {
        $this->update(['email_confirmed_at' => date('Y-m-d H:i:s')], $this->user['id']);
    }
}