<?php

namespace Atom\Authentication;


use Atom\Database\Model;
use Atom\Encryption\Hash;
use Atom\Environment\Config;

class Auth
{

    protected static $_instance;

    protected $config;

    protected $model;

    protected $username_field;

    protected $password_field;

    /**
     * Create new instance of authentication manager.
     *
     * @return void
     *
     * @throws \Atom\Exceptions\ConfigurationException
     */
    public function __construct()
    {
        $this->load();
        $this->loadAttributes();
    }

    /**
     * Get Auth instance.
     *
     * @return Auth
     *
     * @throws \Atom\Exceptions\ConfigurationException
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Auth();

        return static::$_instance;
    }

    /**
     * Load auth configurations.
     *
     * @throws \Atom\Exceptions\ConfigurationException|\Exception
     */
    public function load()
    {
        $config = new Config(config_path() . DIRECTORY_SEPARATOR . 'auth.json');
        $this->config = $config->config();
    }

    /**
     * Load auth configuration.
     *
     * @return void
     */
    public function loadAttributes()
    {
        $this->model = models_namespace() . $this->config->model;
        $this->username_field = $this->config->username_column;
        $this->password_field = $this->config->password_column;
    }

    /**
     * Attempt login with the given credentials.
     *
     * @param array $credentials
     * @throws \Exception
     *
     * @return bool
     */
    public function attempt($credentials)
    {
        $username = null;
        $password = null;

        foreach ($credentials as $k => $v) {
            if ($k == 'username' || $k == 'email' || $k == 'login' || $k == 'user' || $k == 'mail' || $k == 'pseudo')
                $username = $v;
            else
                $username = array_values($credentials)[0];

            if ($k == 'password' || $k == 'pass' || $k == 'pwd' || $k == 'mdp')
                $password = Hash::make($v);
            else
                $password = Hash::make(array_values($credentials)[1]);
        }

        // Querying the database for retrieving the user
        // with the given credentials and return false
        // if the user doesn't exists
        $where = $this->username_field . " = '" . $username . "' AND " . $this->password_field . " = '" . $password . "'";
        $results = $this->model::all($where);

        if (is_array($results) && count($results) >= 1) {
            $this->set(true, $results[0]);
            return true;
        }
        else
            return false;
    }

    /**
     * Check if there is a logged user for the current session.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function check()
    {
        if (session()->has('logged') && session()->has('session_user')) {
            if (session('logged') == true) {
                $user = decrypt(session('session_user'));
                $username_field = $this->username_field;
                $password_field = $this->password_field;
                $username = $user->$username_field;
                $password = $user->$password_field;

                $where = $this->username_field . " = '" . $username . "' AND " . $this->password_field . " = '" . $password . "'";
                $results = $this->model::all($where);

                if (is_array($results) && count($results) >= 1) {
                    $logged = true;
                    $this->set(true, $results[0]);
                }
                else
                    $logged = false;
            } else {
                $logged = false;
            }
        } else {
            $logged = false;
        }

        return $logged;
    }

    /**
     * Attempt login with the given user model.
     *
     * @param Model $user
     * @return bool
     *
     * @throws \Exception
     */
    public function login(Model $user)
    {
        $username_field = $this->username_field;
        $password_field = $this->password_field;
        $username = $user->$username_field;
        $password = $user->$password_field;

        $where = $this->username_field . " = '" . $username . "' AND " . $this->password_field . " = '" . $password . "'";
        $results = $this->model::all($where);

        if (is_array($results) && count($results) >= 1) {
            $logged = true;
            $this->set(true, $results[0]);
        }
        else
            $logged = false;

        return $logged;
    }

    /**
     * Close the session of the logged user.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function logout()
    {
        return $this->set(false);
    }

    /**
     * Set the logged session.
     *
     * @param bool $logged
     * @param Model|null $user
     * @return bool
     *
     * @throws \Exception
     */
    protected function set($logged = false, $user = null)
    {
        if ($logged) {
            if ($user != null && is_a($user, Model::class)) {
                session(['logged' => $logged]);
                session(['session_user' => encrypt($user)]);

                return true;
            } else {
                return false;
            }
        } else {
            session(['session_user' => null]);
            session(['logged' => false]);

            return true;
        }
    }

}