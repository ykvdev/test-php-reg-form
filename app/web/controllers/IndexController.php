<?php declare(strict_types=1);

namespace app\web\controllers;

class IndexController extends AbstractController
{
    public function indexAction() : void
    {
        $this->renderView('index/index');
    }
}