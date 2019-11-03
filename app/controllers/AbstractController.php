<?php declare(strict_types=1);

namespace app\controllers;

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

    public function runAction(string $alias, string $role)
    {
        if($realRole = $this->getRealRole($role)) {
            $this->redirect($this->config['routes_for_roles'][$realRole]);
        } elseif(!$this->validateAuthCookies() && $this->models->users->logout()) {
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } else {
            $this->{$alias . 'Action'}();
        }
    }

    private function getRealRole(string $actionRole) : ?string
    {
        $realRole = $this->models->users->getAuthorised() ? Users::ROLE_USER : Users::ROLE_GUEST;
        if($actionRole == Users::ROLE_ALL || $realRole == $actionRole) {
            return null;
        } else {
            return $realRole;
        }
    }

    private function validateAuthCookies() : bool
    {
        $user = $this->models->users->getAuthorised();
        return !$user || (strcmp($user['ip'], $this->services->getClientIp()) === 0
                && strcmp($user['browser'], $_SERVER['HTTP_USER_AGENT']) === 0);
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