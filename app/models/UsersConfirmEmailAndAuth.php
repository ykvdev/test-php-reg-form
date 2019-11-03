<?php declare(strict_types=1);

namespace app\models;

class UsersConfirmEmailAndAuth extends Users
{
    public $dbFields = ['email_confirmed_at'];

    /** @var array */
    private $data;

    /** @var string */
    private $error;

    public function exec(array $data) : string
    {
        $this->data = $data;
        if($this->validate()) {
            $this->activate();
            $this->login($this->data['email']);
        }

        return $this->error;
    }

    private function validate() : bool
    {
        if($this->error = $this->validateEmail($this->data['email'] ?? null, true)) {
            return false;
        }

        $user = $this->getRow(['email' => $this->data['email']], ['registered_at', 'email_confirmed_at']);
        if($user['email_confirmed_at']) {
            $this->error = 'This e-mail already confirmed';
            return false;
        }

        if(strcmp(md5($this->data['email'] . $user['registered_at']), $this->data['token'] ?? null) !== 0) {
            $this->error = 'E-mail confirmation token incorrect';
            return false;
        }

        return true;
    }

    private function activate() : void
    {
        $this->update(['email_confirmed_at' => date('Y-m-d H:i:s')], ['email' => $this->data['email']]);
    }
}