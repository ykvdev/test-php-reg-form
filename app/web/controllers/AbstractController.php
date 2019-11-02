<?php declare(strict_types=1);

namespace app\web\controllers;

use app\services\Container;

abstract class AbstractController
{
    /** @var array */
    protected $config;

    /** @var Container */
    protected $services;

    /** @var \app\models\Container */
    protected $models;

    /** @var array */
    protected $request;

    public function __construct(array $config, Container $services, \app\models\Container $models, $request)
    {
        $this->config = $config;
        $this->services = $services;
        $this->models = $models;
        $this->request = $request;
    }

    protected function renderView(string $viewAlias, array $vars = []) : void
    {
        echo $this->services->viewRenderer()->render($viewAlias, $vars);
        exit();
    }

    /**
     * @param array|object|string $data
     */
    protected function renderJson($data) : void
    {
        header('Content-Type: application/json');

        if(is_string($data)) {
            echo $data;
        } elseif(is_array($data) || is_object($data)) {
            json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            throw new \RuntimeException('Data type for JSON render is wrong');
        }

        exit();
    }

    protected function goBack() : void
    {
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    protected function redirect(string $toUrl, $code = 301) : void
    {
        header('Location: ' . $toUrl, true, $code);
        exit();
    }
}