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
    public static $controllersPath = "\\App\\Controllers\\";

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
        $multi = plural($model);

        $multi = strtolower($multi);
        $model = strtolower($model);

        $this->add($multi, $controller . '#index', 'GET', $multi . '.index');
        $this->add($multi . '/create', $controller . '#create', 'GET', $multi . '.create');
        $this->add($multi, $controller . '#store', 'POST', $multi . '.store');
        $this->add($multi . '/:' . $model, $controller . '#show', 'GET', $multi . '.show');
        $this->add($multi . '/:' . $model . '/edit', $controller . '#edit', 'GET', $multi . '.edit');
        $this->add($multi . '/:' . $model, $controller . '#update', 'POST', $multi . '.update');
        $this->add($multi . '/:' . $model . '/delete', $controller . '#destroy', 'GET', $multi . '.destroy');
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

        header('location: ' . route($name, $params), true, '302');
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
        $routes = [];

        foreach ($this->namedRoutes as $key => $route)
            $routes[$key] = $route->getPath();

        return $routes;
    }

}