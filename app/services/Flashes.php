<?php declare(strict_types=1);

namespace app\services;

class Flashes
{
    public const INFO = 'success';
    public const ERROR = 'danger';

    public function addInfo(string $msg) : void
    {
        $_SESSION[self::INFO] = $msg;
    }

    public function addError(string $msg) : void
    {
        $_SESSION[self::ERROR] = $msg;
    }

    public function getList() : array
    {
        $messages = [];
        foreach ([self::INFO, self::ERROR] as $type) {
            if(isset($type)) {
                $messages[$type] = $_SESSION[$type];
                unset($_SESSION[$type]);
            }
        }

        return $messages;
    }
}