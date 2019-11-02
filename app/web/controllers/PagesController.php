<?php declare(strict_types=1);

namespace app\web\controllers;

class PagesController extends AbstractController
{
    public function indexAction() : void
    {
        $this->renderView('pages/' . $this->request['view']);
    }

    public function error404Action() : void
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
        $this->renderView('pages/error404');
    }

    public function error405Action() : void
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);
        $this->renderView('pages/error405');
    }

    public function error500Action() : void
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        $this->renderView('pages/error500');
    }
}