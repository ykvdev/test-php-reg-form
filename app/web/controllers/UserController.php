<?php declare(strict_types=1);

namespace app\web\controllers;

class UserController extends AbstractController
{
    public function registerAction() : void
    {
        $this->renderView('user/register');
    }
}