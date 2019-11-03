<?php declare(strict_types=1);

namespace app\models;

class UsersProfileEdit extends Users
{
    public $dbFields = ['full_name'];

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
        if($error = $this->validateFullName($this->data['full_name'])) {
            $this->errors['full_name'] = $error;
        }

        return empty($this->errors);
    }

    private function save() : void
    {
        $old = $this->getAuthorised();
        $new = $this->filterFieldsList($this->data);
        $changes = array_diff($new, $old);

        $this->update($changes, $this->getAuthorised('id'));
        $this->updateAuthorised($changes);
    }
}