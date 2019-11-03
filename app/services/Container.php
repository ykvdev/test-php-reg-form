<?php declare(strict_types=1);

namespace app\services;

use League\Plates\Engine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use Nette\Mail\SmtpMailer;
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
    private $servicesObjects = [];

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->servicesLazy['flashes'] = Flashes::class;
    }

    public function __get($name)
    {
        if (!isset($this->servicesObjects[$name])) {
            $this->servicesObjects[$name] = new $this->servicesLazy[$name];
        }

        return $this->servicesObjects[$name];
    }

    public function viewRenderer() : ViewRenderer
    {
        if (!isset($this->servicesObjects['viewRenderer'])) {
            $this->servicesObjects['viewRenderer'] = new ViewRenderer($this->config['view_renderer']);
        }

        return $this->servicesObjects['viewRenderer'];
    }

    public function db() : EasyDB
    {
        if (!isset($this->servicesObjects['db'])) {
            $this->servicesObjects['db'] = Factory::create('sqlite:' . $this->config['sqlite_db_file']);;
        }

        return $this->servicesObjects['db'];
    }

    public function mailer() : Mailer
    {
        if (!isset($this->servicesObjects['mailer'])) {
            $this->servicesObjects['mailer'] = new Mailer($this->config['mailer'], $this);
        }

        return $this->servicesObjects['mailer'];
    }

    public function captcha() : Captcha
    {
        if (!isset($this->servicesObjects['captcha'])) {
            $this->servicesObjects['captcha'] = new Captcha($this->config['captcha']);
        }

        return $this->servicesObjects['captcha'];
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