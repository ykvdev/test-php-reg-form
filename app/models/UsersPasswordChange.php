<?php declare(strict_types=1);

namespace app\models;

class UsersPasswordChange extends Users
{
    public $dbFields = ['password'];

    /** @var array */
    private $data;

    /** @var array */
    private $errors = [];

    public function exec(array $data) : array
    {
        $this->data = $data;
        if($this->validate()) {
            $this->save();
        }

        return $this->errors;
    }

    private function validate() : bool
    {
        if(!$this->passwordVerify($this->data['curr_password'], (string)$this->getAuthorised('password'))) {
            $this->errors['curr_password'] = 'Current password incorrect';
        }

        if($error = $this->validatePassword($this->data['password'])) {
            $this->errors['password'] = $error;
        }

        if(!isset($this->data['repassword'])) {
            $this->errors['repassword'] = 'Repeat password is required';
        } elseif($this->data['password'] && $this->data['password'] != $this->data['repassword']) {
            $this->errors['repassword'] = 'Passwords is not match';
        }

        return empty($this->errors);
    }

    private function save() : void
    {
        $old = $this->getAuthorised();
        $new = $this->filterFieldsList($this->data);
        $new['password'] = $this->passwordHash($new['password']);
        $changes = array_diff($new, $old);

        $this->update($changes, $this->getAuthorised('id'));
        $this->updateAuthorised($changes);
    }
}