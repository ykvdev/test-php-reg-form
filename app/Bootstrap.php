<?php declare(strict_types=1);

namespace app;

use app\models\Users;
use app\controllers\AbstractController;
use app\controllers\PagesController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use ParagonIE\AntiCSRF\AntiCSRF;
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
    }

    public function run()
    {
        $this->initErrorsHandler();
        $this->validateCsrfIfNeed();
        $this->dispatch();
    }

    private function initErrorsHandler()
    {
        if(APP_ENV == ENV_DEV) {
            $handler = new PrettyPageHandler;
            $handler->setEditor($this->config['errors_handler_editor']);
            (new Run())->prependHandler($handler)->register();
        }
    }

    private function validateCsrfIfNeed()
    {
        if($_POST && !(new AntiCSRF())->validateRequest()) {
            $this->invokeAction(
                PagesController::class,
                'index',
                Users::ROLE_ALL,
                ['view' => 'error-expired']
            );
            exit();
        }
    }

    private function dispatch() : void
    {
        try {
            $dispatcher = cachedDispatcher(function(RouteCollector $r) {
                foreach ($this->config['routes'] as $routeParts) {
                    [$method, $route, $controller, $action] = $routeParts;
                    $role = $routeParts[4] ?? null;
                    $r->addRoute($method, $route, [$controller, $action, $role]);
                }
            }, [
                'cacheFile' => $this->config['routes_cache_file'],
                'cacheDisabled' => APP_ENV == ENV_DEV,
            ]);

            $routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $this->prepareUri());
            $result = $routeInfo[0];
            $handler = $routeInfo[1] ?? null;
            $requestParams = $routeInfo[2] ?? null;
            switch ($result) {
                case Dispatcher::NOT_FOUND:
                    $this->invokeAction(PagesController::class, 'error404');
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $this->invokeAction(PagesController::class, 'error405');
                    break;

                case Dispatcher::FOUND:
                    [$controller, $action, $role] = $handler;
                    $this->invokeAction($controller, $action, $role, $requestParams);
                    break;
            }
        } catch (\Throwable $e) {
            if(APP_ENV == ENV_DEV) {
                throw $e;
            } else {
                $this->invokeAction(PagesController::class, 'error500');
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

    private function invokeAction(string $controllerClassName, string $action, string $role = Users::ROLE_ALL, array $request = []) : void
    {
        /** @var AbstractController $controller */
        $controller = new $controllerClassName($this->config, $request);
        $controller->runAction($action, $role);
    }
}