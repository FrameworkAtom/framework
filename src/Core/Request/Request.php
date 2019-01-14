<?php

namespace Atom\Request;


use Atom\Cookie\Cookie;
use Atom\Exceptions\InvalidDiskTypeException;
use Atom\Flash\Flash;
use Atom\Session\Session;
use Atom\Storage\Storage;

class Request
{

    /**
     * Request instance.
     *
     * @var Request
     */
    protected static $_instance = null;

    /**
     * Request user inputs.
     *
     * @var array
     */
    protected $inputs = [];

    /**
     * Request user files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Request values.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Create new instance of Request.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reload();
        static::$_instance = $this;
    }

    /**
     * Get the request instance.
     *
     * @return Request
     *
     * @throws \Atom\Exceptions\SessionException|\Exception
     */
    public static function instance()
    {
        static::$_instance = new Request();
        flash()->progess();

        return static::$_instance;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Refresh the request.
     *
     * @return void
     */
    public function reload()
    {
        $this->inputs = $_POST;
        $this->params = $_SERVER;
    }

    /**
     * Get the Request param with the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->reload();

        return $this->params[$key];
    }

    /**
     * Get user inputs.
     *
     * @param string|array|null $key
     * @return array|mixed
     */
    public function input($key = null)
    {
        $this->inputs = $_POST;

        if (!is_null($key)) {
            if (array_key_exists($key, $this->inputs))
                return $this->inputs[$key];
        }

        if (count($this->inputs) == 1)
            return $this->inputs[0];
        else
            return $this->inputs;
    }

    /**
     * Get user inputs with the given keys.
     *
     * @param array $keys
     * @return mixed|array
     *
     * @throws \Exception
     */
    public function only(...$keys)
    {
        $inputs = [];
        $keys = func_get_args();

        $this->reload();

        foreach ($keys as $key) {
            if (isset($this->inputs[$key])) {
                $inputs[$key] = $this->inputs[$key];
            }
        }

        if (count($inputs) == 1)
            return array_values($inputs)[0];
        else
            return $inputs;
    }

    /**
     * Get user inputs excepts the given one.
     *
     * @param array|string $keys
     * @return array
     */
    public function except(...$keys)
    {
        $this->inputs = $_POST;
        $keys = func_get_args();

        if (is_array($keys)) {

            foreach ($keys as $key) {
                foreach ($this->inputs as $k => $v) {
                    if ($key == $k) {
                        unset($this->inputs[$k]);
                    }
                }
            }

        } else {
            foreach ($this->inputs as $k => $v) {
                if ($keys == $k) {
                    unset($this->inputs[$k]);
                }
            }
        }

        if (count($this->inputs) == 1)
            return array_values($this->inputs)[0];
        else
            return $this->inputs;
    }

    /**
     * Get the user input files.
     *
     * @return array
     *
     * @throws InvalidDiskTypeException
     */
    public function files()
    {
        $files = $_FILES;
        $storage = new Storage();

        foreach ($files as $file) {
            $this->files[] = new UploadedFile($file, $storage);
        }

        return $this->files;
    }

    /**
     * Get the user input file with the given name.
     *
     * @param string $name
     * @return UploadedFile|bool
     *
     * @throws InvalidDiskTypeException
     */
    public function file($name)
    {
        $files = $_FILES;
        $storage = new Storage();

        if (array_key_exists($name, $files))
            return new UploadedFile($files[$name], $storage);
        
        return false;
    }

    /**
     * Get request parameters.
     *
     * @param string $key
     * @return array|mixed
     */
    public function params($key = null)
    {
        if (!is_null($key)) return $this->params[$key];

        return $this->params;
    }

    /**
     * Add flash message to the next request.
     *
     * @param string $message
     * @param string $type
     * @return Flash
     *
     * @throws \Atom\Exceptions\SessionException
     */
    public function flash($message, $type = 'message')
    {
        return \flash($message, $type);
    }

    /**
     * Get cookie with the given name.
     *
     * @param string $name
     * @return mixed
     */
    public function cookie($name)
    {
        return Cookie::get($name);
    }

    /**
     * Get the session manager.
     *
     * @return Session
     *
     * @throws \Atom\Exceptions\SessionException
     */
    public function session()
    {
        return Session::instance();
    }

}