<?php

namespace Atom\Session;


use Atom\Exceptions\SessionException;
use Atom\Filesystem\Filesystem;

class Session
{

    /**
     * Session instance.
     *
     * @var Session
     */
    protected static $_instance = null;
    
    /**
     * Session attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Session started ?
     *
     * @var bool
     */
    private $started;

    /**
     * Create new instance of session.
     *
     * @return void
     *
     * @throws SessionException
     */
    public function __construct()
    {
        if ($this->start()) {
            $this->loadAttributes();
        } else {
            throw new SessionException("Cannot start the session");
        }
    }

    /**
     * Get session instance.
     *
     * @return Session
     *
     * @throws SessionException
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Session();

        return static::$_instance;
    }

    /**
     * Determine if the session is started.
     *
     * @return bool
     */
    private function started()
    {
        return $this->started = session_status() == PHP_SESSION_ACTIVE;
    }

    /**
     * Start the session.
     *
     * @return bool
     */
    private function start()
    {
        if (!$this->started()) {
            session_save_path(app()->sessionsPath);
            $this->started = session_start();
        }

        return $this->started;
    }

    /**
     * Load session values.
     *
     * @return void
     */
    protected function loadAttributes()
    {
        $this->attributes = $_SESSION;
    }

    /**
     * Get attribute with given key.
     *
     * @param string $key
     * @param string|callable|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->exists($key)) {
            return $this->attributes[$key];
        } else {

            if (!is_null($default)) {

                if (is_callable($default)) {
                    return call_user_func($default, []);
                }

            }

        }

        return $default;
    }

    /**
     * Get all session data.
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * Determine if an item exists in the session and it's not null.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->attributes) && !is_null($this->attributes[$key]);
    }

    /**
     * Determine if an item exists in the session.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Store data to the session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put($key, $value)
    {
        $_SESSION[$key] = $value;

        $this->loadAttributes();
    }

    /**
     * Push data to an array session item.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     *
     * @throws SessionException
     */
    public function push($key, $value)
    {
        if ($this->exists($key)) {
            if (is_array($this->get($key))) {
                if (!in_array($value, $_SESSION[$key], true))
                    array_push($_SESSION[$key], $value);
            } else {
                throw new SessionException("Can't push into {$key}. It is not an array");
            }
        } else {
            $this->put($key, [$value]);
        }

        $this->loadAttributes();
    }

    /**
     * Get data and delete it.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function pull($key, $default = null)
    {
        if ($this->exists($key)) {
            $tmp = $this->get($key, $default);
            unset($_SESSION[$key]);
            $this->loadAttributes();

            return $tmp;
        }

        return $this->get($key, $default);
    }

    /**
     * Delete data from the session.
     *
     * @param string $key
     * @return void
     */
    public function forget($key)
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                if ($this->exists($k)) {
                    unset($_SESSION[$k]);
                }
            }
            $this->loadAttributes();
        } elseif (is_string($key)) {
            if ($this->exists($key)) {
                unset($_SESSION[$key]);
                $this->loadAttributes();
            }
        }
    }

    /**
     * Delete all data from the session.
     *
     * @return bool
     */
    public function flush()
    {
        $this->attributes = [];
        $_SESSION = [];
        return session_unset();
    }

    /**
     * Regenerate session ID.
     *
     * @return bool
     */
    public function regenerate()
    {
        $result = session_regenerate_id(true);
        $this->loadAttributes();

        return $result;
    }

}