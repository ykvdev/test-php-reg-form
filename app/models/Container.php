<?php declare(strict_types=1);

namespace app\models;

/**
 * Class Container
 * @package app\models
 *
 * @property-read Users $users
 * @property-read UsersRegister $usersRegister
 * @property-read UsersConfirmEmailAndAuth $usersConfirmEmailAndAuth
 */
class Container
{
    /** @var array */
    private $config;

    /** @var \app\services\Container */
    private $services;

    /** @var array */
    private $modelsLazy = [];

    /** @var array */
    private $modelsObjects = [];

    public function __construct(array $config, \app\services\Container $services)
    {
        $this->config = $config;
        $this->services = $services;

        $this->modelsLazy['users'] = Users::class;
        $this->modelsLazy['usersRegister'] = UsersRegister::class;
        $this->modelsLazy['usersConfirmEmailAndAuth'] = UsersConfirmEmailAndAuth::class;
    }

    public function __get($name)
    {
        if (!isset($this->modelsObjects[$name])) {
            $this->modelsObjects[$name] = new $this->modelsLazy[$name]($this->config, $this->services);
        }

        return $this->modelsObjects[$name];
    }
}