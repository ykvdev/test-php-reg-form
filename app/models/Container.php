<?php declare(strict_types=1);

namespace app\models;

/**
 * Class Container
 * @package app\models
 *
 * @property-read Users $users
 * @property-read UsersRegister $usersRegister
 * @property-read UsersConfirmEmail $usersConfirmEmail
 * @property-read UsersLogin $usersLogin
 * @property-read UsersProfileEdit $usersProfileEdit
 * @property-read UsersPasswordChange $usersPasswordChange
 * @property-read UsersPasswordRestoreRequest $usersPasswordRestoreRequest
 * @property-read UsersPasswordRestore $usersPasswordRestore
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
    private $modelObjects = [];

    public function __construct(array $config, \app\services\Container $services)
    {
        $this->config = $config;
        $this->services = $services;

        $this->modelsLazy['users'] = Users::class;
        $this->modelsLazy['usersRegister'] = UsersRegister::class;
        $this->modelsLazy['usersConfirmEmail'] = UsersConfirmEmail::class;
        $this->modelsLazy['usersLogin'] = UsersLogin::class;
        $this->modelsLazy['usersPasswordRestoreRequest'] = UsersPasswordRestoreRequest::class;
        $this->modelsLazy['usersPasswordRestore'] = UsersPasswordRestore::class;
        $this->modelsLazy['usersProfileEdit'] = UsersProfileEdit::class;
        $this->modelsLazy['usersPasswordChange'] = UsersPasswordChange::class;
    }

    public function __get(string $name) : AbstractModel
    {
        if (!isset($this->modelObjects[$name])) {
            $this->modelObjects[$name] = new $this->modelsLazy[$name]($this->config, $this->services);
        }

        return $this->modelObjects[$name];
    }
}