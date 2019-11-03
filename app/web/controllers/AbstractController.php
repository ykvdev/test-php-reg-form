<?php declare(strict_types=1);

namespace app\web\controllers;

use app\models\Users;
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

    public function __construct(array $config, array $request)
    {
        $this->config = $config;
        $this->request = $request;

        $this->services = new Container($this->config);
        $this->models = new \app\models\Container($this->config, $this->services);
    }

    public function runAction(string $alias, string $availableForRole)
    {
        $realRole = $this->models->users->getAuthorised() ? Users::ROLE_USER : Users::ROLE_GUEST;
        if($realRole != $availableForRole) {
            $this->redirect($this->config['routes_for_roles'][$realRole]);
        } else {
            $this->{$alias . 'Action'}();
        }
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