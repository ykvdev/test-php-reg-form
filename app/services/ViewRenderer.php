<?php declare(strict_types=1);

namespace app\services;

use League\Plates\Engine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use ParagonIE\AntiCSRF\AntiCSRF;

class ViewRenderer
{
    /** @var Engine */
    private $renderer;

    public function __construct(array $viewRendererConfig)
    {
        $this->renderer = new Engine($viewRendererConfig['views_dir'], $viewRendererConfig['views_ext']);
        $this->renderer->loadExtension(new Asset($viewRendererConfig['assets_dir'], true));
        $this->renderer->loadExtension(new URI($_SERVER['PATH_INFO'] ?? null));
        $this->renderer->registerFunction('csrf', function(string $formActionUrl) {
            return (new AntiCSRF())->insertToken($formActionUrl, false);
        });
    }

    public function render(string $viewAlias, array $vars = []) : string
    {
        $view = $this->renderer->make($viewAlias);
        if(!$view->exists()) {
            throw new \RuntimeException('View not found, alias: ' . $viewAlias);
        }

        return $view->render($vars);
    }
}