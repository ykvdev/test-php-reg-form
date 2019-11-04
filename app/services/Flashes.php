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
}