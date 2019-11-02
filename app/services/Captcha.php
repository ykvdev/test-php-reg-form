<?php declare(strict_types=1);

namespace app\services;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class Captcha
{
    /** @var array */
    private $config;

    /** @var CaptchaBuilder */
    private $builder;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->builder = new CaptchaBuilder(
            APP_ENV == ENV_DEV ? $this->config['dev_phrase'] : null,
            new PhraseBuilder($this->config['length'])
        );
    }

    public function buildAndOutput() : void
    {
        $this->builder->build($this->config['width'], $this->config['height']);
        $_SESSION[$this->config['session_name']] = $this->builder->getPhrase();

        header('Content-type: image/jpeg');
        $this->builder->output();
    }

    public function getPrevPhrase()
    {
        return $_SESSION[$this->config['session_name']];
    }
}