<?php declare(strict_types=1);

namespace app\web;

use app\services\Container;
use app\web\actions\AbstractAction;
use app\web\actions\StaticPageAction;
use app\web\controllers\AbstractController;
use app\web\controllers\PagesController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use function FastRoute\cachedDispatcher;

class Bootstrap
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        if(CUR_ENV == ENV_DEV) {
            $whoops = new Run();
            $whoops->prependHandler(new PrettyPageHandler());
            $whoops->register();
        }
    }

    public function dispatch() : void
    {
        try {
            $dispatcher = cachedDispatcher(function(RouteCollector $r) {
                foreach ($this->config['routes'] as $routeParts) {
                    $r->addRoute($routeParts[0], $routeParts[1], [$routeParts[2], $routeParts[3]]);
                }
            }, [
                'cacheFile' => $this->config['routes_cache_file'],
                'cacheDisabled' => CUR_ENV == ENV_DEV,
            ]);

            $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $this->prepareUri());
            $result = $routeInfo[0];
            $handler = $routeInfo[1] ?? null;
            $requestParams = $routeInfo[2] ?? null;
            switch ($result) {
                case Dispatcher::NOT_FOUND:
                    $this->invokeAction(PagesController::class, 'error404Action');
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $this->invokeAction(PagesController::class, 'error405Action');
                    break;

                case Dispatcher::FOUND:
                    $this->invokeAction($handler[0], $handler[1], $requestParams);
                    break;
            }
        } catch (\Throwable $e) {
            if(CUR_ENV == ENV_DEV) {
                throw $e;
            } else {
                $this->invokeAction(PagesController::class, 'error500Action');
            }
        }
    }

    /**
     * Strip query string (?foo=bar) and decode URI
     *
     * @return string
     */
    private function prepareUri() : string
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        return $uri;
    }

    private function invokeAction(string $controllerClassName, string $actionMethodName, array $request = []) : void
    {
        $servicesContainer = new Container($this->config);
        $modelsContainer = new \app\models\Container($servicesContainer);

        /** @var AbstractController $controller */
        $controller = new $controllerClassName($this->config, $servicesContainer, $modelsContainer, $request);
        $controller->$actionMethodName();
    }
}