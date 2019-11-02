<?php declare(strict_types=1);

namespace app\models;

/**
 * Class Container
 * @package app\models
 *
 * @property-read Users $users
 */
class Container
{
    /** @var \app\services\Container */
    private $services;

    /** @var array */
    private $modelsLazy = [];

    /** @var array */
    private $modelsObjects = [];

    public function __construct(\app\services\Container $services)
    {
        $this->services = $services;

        $this->modelsLazy['users'] = Users::class;
    }

    public function __get($name)
    {
        if (!isset($this->modelsObjects[$name])) {
            $this->modelsObjects[$name] = new $this->modelsLazy[$name]($this->services);
        }

        return $this->modelsObjects[$name];
    }
}