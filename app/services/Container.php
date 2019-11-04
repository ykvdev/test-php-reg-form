<?php declare(strict_types=1);

namespace app\services;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;

/**
 * Class Container
 * @package app\services
 *
 * @property-read Flashes $flashes
 */
class Container
{
    /** @var array */
    private $config;

    /** @var array */
    private $servicesLazy = [];

    /** @var array */
    private $serviceObjects = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->servicesLazy['flashes'] = Flashes::class;
    }

    public function __get(string $name) : object
    {
        if (!isset($this->serviceObjects[$name])) {
            $this->serviceObjects[$name] = new $this->servicesLazy[$name];
        }

        return $this->serviceObjects[$name];
    }

    public function viewRenderer() : ViewRenderer
    {
        if (!isset($this->serviceObjects['viewRenderer'])) {
            $this->serviceObjects['viewRenderer'] = new ViewRenderer($this->config['view_renderer']);
        }

        return $this->serviceObjects['viewRenderer'];
    }

    public function db() : EasyDB
    {
        if (!isset($this->serviceObjects['db'])) {
            $this->serviceObjects['db'] = Factory::create('sqlite:' . $this->config['sqlite_db_file']);;
        }

        return $this->serviceObjects['db'];
    }

    public function mailer() : Mailer
    {
        if (!isset($this->serviceObjects['mailer'])) {
            $this->serviceObjects['mailer'] = new Mailer($this->config['mailer'], $this);
        }

        return $this->serviceObjects['mailer'];
    }

    public function captcha() : Captcha
    {
        if (!isset($this->serviceObjects['captcha'])) {
            $this->serviceObjects['captcha'] = new Captcha($this->config['captcha']);
        }

        return $this->serviceObjects['captcha'];
    }

    public function getBaseUrl() : string
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        return $protocol . $_SERVER['HTTP_HOST'];
    }

    public function getClientIp() : ?string
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])
            && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return null;
        }
    }
}