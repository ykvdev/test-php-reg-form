<?php declare(strict_types=1);

namespace app\services;

class Flashes
{
    private const INFO = 'info';
    private const ERROR = 'error';

    public function addInfo(string $msg) : void
    {
        $_SESSION[static::INFO] = $msg;
    }

    public function addError(string $msg) : void
    {
        $_SESSION[static::ERROR] = $msg;
    }

    public function getList() : array
    {
        $messages = [];
        foreach ([static::INFO, static::ERROR] as $type) {
            if(isset($type)) {
                $messages[$type] = $_SESSION[$type];
                unset($_SESSION[$type]);
            }
        }

        return $messages;
    }
}