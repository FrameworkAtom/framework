<?php

namespace Atom\Routing;


use Atom\Request\Request;

class Route
{

    /**
     * Route path.
     *
     * @var string
     */
    private $path;

    /**
     * Route callable.
     *
     * @var mixed
     */
    private $callable;

    /**
     * Route matches.
     *
     * @var array
     */
    private $matches = [];

    /**
     * Route parameters.
     *
     * @var array
     */
    private $params = [];

    /**
     * Route global variables.
     *
     * @var array
     */
    private $globals = [];

    /**
     * Route middleware.
     *
     * @var array
     */
    private $middleware = [];

    /**
     * Route parent router.
     *
     * @var Router
     */
    private $router;

    /**
     * Create new instance of Route.
     *
     * @param Router $router
     * @param string $path
     * @param mixed $callable
     * @return void
     */
    public function __construct($router, $path, $callable)
    {
        $this->path = trim($path, '/');
        $this->callable = $callable;
        $this->router = $router;
    }

    /**
     * Determine if the route match the given URL.
     *
     * @param string $url
     * @return bool
     */
    public function match($url)
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";

        if (!preg_match($regex, $url, $matches)) {
            return false;
        }

        array_shift($matches);
        $this->matches = $matches;

        return true;
    }

    /**
     * Get the URL part who matches the given regex.
     *
     * @param string $regex
     * @return bool
     */
    public function setMatch($regex)
    {
        $url = $this->path;
        
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }

        array_shift($matches);
        $this->matches = $matches;

        return true;
    }

    /**
     * Replace the URL parameter with values.
     *
     * @param array $match
     * @return string
     */
    private function paramMatch(array $match)
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }

        return '([^/]+)';
    }

    /**
     * Call the route callback.
     *
     * @return Request
     */
    public function call()
    {
        if (is_string($this->callable)) {
            $params = explode('#', $this->callable);
            $controller = Router::$controllersPath . $params[0];
            $controller = new $controller;
            extract($this->globals);
            $this->callMiddleware();

            call_user_func_array([$controller, $params[1]], $this->matches);
        } else {
            $this->callMiddleware();
            call_user_func_array($this->callable, $this->matches);
        }

        return request();
    }

    /**
     * Set the route match regex.
     *
     * @param string $param
     * @param string $regex
     * @return $this
     */
    public function where($param, $regex)
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);

        return $this;
    }

    /**
     * Add global parameters for the route.
     *
     * @param array $params
     * @return $this
     */
    public function with(array $params)
    {
        foreach ($params as $param => $value) {
            if (!array_key_exists($param, $this->globals))
                $this->globals[$param] = $value;
        }

        extract($this->globals);

        return $this;
    }

    /**
     * Get the route URL.
     *
     * @param array $params
     * @return mixed|string
     */
    public function getUrl($params)
    {
        $path = $this->path;

        foreach ($params as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path == null || $path == '' ? '/' : '/' . $path;
    }

    /**
     * Set the route name.
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        if (!array_key_exists($name, $this->router->namedRoutes))
            $this->router->namedRoutes[$name] = $this;

        return $this;
    }

    /**
     * Add middleware to the route.
     *
     * @param string $class_path
     * @return Route
     */
    public function middleware($class_path)
    {
        if (!in_array($class_path, $this->middleware) && is_string($class_path)) $this->middleware[] = $class_path;

        return $this;
    }

    /**
     * Call the route middleware.
     *
     * @return void
     */
    protected function callMiddleware()
    {
        if (count($this->middleware) > 0) {
            foreach ($this->middleware as $middleware) {
                $controller = new $middleware;
                call_user_func_array([$controller, 'handle'], []);
            }
        }
    }

    public function getPath()
    {
        return $this->path;
    }

}