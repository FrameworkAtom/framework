<?php

namespace Atom\Routing;


use Atom\Exceptions\RouterException;
use Atom\Request\Request;

class Router
{

    /**
     * Router instance.
     *
     * @var Router
     */
    protected static $_instance = null;

    /**
     * Router input URL.
     *
     * @var null|string
     */
    private $url;

    /**
     * Router routes.
     *
     * @var array
     */
    private $routes = [];

    /**
     * Router named routes.
     *
     * @var array
     */
    public $namedRoutes = [];

    /**
     * Router default controllers path.
     *
     * @var string
     */
    public static $controllersPath = "App\\Controller\\";

    /**
     * Create new instance of the Router.
     *
     * @param string|null $url
     * @return void
     */
    public function __construct($url = null)
    {
        if (is_null($url)) {
            $this->url = $_SERVER['REQUEST_URI'];
        } else {
            $this->url = $url;
        }
    }

    /**
     * Get the router instance.
     *
     * @return Router
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Router();

        return static::$_instance;
    }

    /**
     * Add a GET route to the router.
     *
     * @param string $path
     * @param mixed $callable
     * @param string|null $name
     * @return Route
     */
    public function get($path, $callable, $name = null)
    {
        return $this->add($path, $callable, 'GET', $name);
    }

    /**
     * Add a POST route to the router.
     *
     * @param string $path
     * @param mixed $callable
     * @param string|null $name
     * @return Route
     */
    public function post($path, $callable, $name = null)
    {
        return $this->add($path, $callable, 'POST', $name);
    }

    /**
     * Add a RESOURCE route for the given model.
     *
     * @param string $model
     * @param string $controller
     * @return void
     */
    public function resource($model, $controller)
    {
        if (endsWith($model, 'ies'))
            $single = substr($model, 0, strlen($model) - 3) . 'y';
        elseif (endsWith($model, 's'))
            $single = substr($model, 0, strlen($model) - 1);
        else
            $single = $model;

        $this->add($model, $controller . '#index', 'GET', $model . '.index');
        $this->add($model . '/create', $controller . '#create', 'GET', $model . '.create');
        $this->add($model, $controller . '#store', 'POST', $model . '.store');
        $this->add($model . '/:' . $single, $controller . '#show', 'GET', $model . '.show');
        $this->add($model . '/:' . $single . '/edit', $controller . '#edit', 'GET', $model . '.edit');
        $this->add($model . '/:' . $single, $controller . '#update', 'POST', $model . '.update');
        $this->add($model . '/:' . $single . '/delete', $controller . '#destroy', 'GET', $model . '.destroy');
    }

    /**
     * Add a route to the router.
     *
     * @param string $path
     * @param mixed $callable
     * @param string $method
     * @param string|null $name
     * @return Route
     */
    private function add($path, $callable, $method, $name = null)
    {
        $route = new Route($this, $path, $callable);
        $this->routes[$method][] = $route;

        if (!is_null($name)) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * Start the routing process.
     *
     * @return mixed
     *
     * @throws RouterException
     */
    public function run()
    {
        if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
            throw new RouterException('REQUEST_METHOD does not exist');
        }

        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->match($this->url)) {
                return $route->call();
            }
        }

        throw new RouterException('No matching routes');
    }

    /**
     * Get the URL of the route with the given name.
     *
     * @param string $name
     * @param array $params
     * @return string
     *
     * @throws RouterException
     */
    public function url($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException("No route matches this name {$name}");
        }

        return $this->namedRoutes[$name]->getUrl($params);
    }

    /**
     * Redirect to the given route.
     *
     * @param string $name
     * @param array $params
     * @return Request
     *
     * @throws RouterException
     */
    public function redirect($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException("No route matches this name {$name}");
        }

        header('location: ' . named_url($name, $params), true, '302');
        return request();
    }

    /**
     * Set the router default controllers path.
     *
     * @param string $namespace
     * @return void
     */
    public static function setDefaultNamespace($namespace)
    {
        self::$controllersPath = $namespace;
    }

    /**
     * Get the router routes.
     *
     * @return array
     */
    public function routes()
    {
        return $this->routes;
    }

}