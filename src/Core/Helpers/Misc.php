<?php

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function plural($str)
{
    if (endsWith($str, 'y'))
        $multi = substr($str, 0, strlen($str) - 1) . 'ies';
    else
        $multi = $str . 's';

    return $multi;
}

/**
 * Dump expressions.
 *
 * @param mixed ...$expressions
 * @return void
 */
function dump(...$expressions)
{
    echo "<pre>";

    foreach (func_get_args() as $arg)
        var_dump($arg);

    echo "</pre>";
}

/**
 * Dump expressions and die.
 *
 * @param mixed ...$expressions
 * @return void
 */
function dd(...$expressions)
{
    echo "<pre>";

    foreach (func_get_args() as $arg)
        var_dump($arg);

    echo "</pre>";

    die();
}

/**
 * Get the global app helper.
 *
 * @return \Atom\Application|mixed
 */
function app()
{
    return \Atom\Application::instance();
}


function request($key = null, $default = null)
{
    if (!is_null($key)) {
        return \Atom\Request\Request::instance()->get($key);
    } else {
        if (!is_null($default)) return $default;
    }

    return \Atom\Request\Request::instance();
}

/**
 * Get the global session helper.
 *
 * @param string|array|null $key
 * @param mixed|null $default
 * @return \Atom\Session\Session|mixed
 *
 * @throws Exception
 */
function session($key = null, $default = null)
{
    $session = \Atom\Session\Session::instance();

    if (isset($key)) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $session->put($k, $v);
            }
            return $session;
        } elseif (is_string($key)) {
            return $session->get($key, $default);
        }
    } else {
        return $session;
    }
}

/**
 * Render a view.
 *
 * @param string $view
 * @param array $params
 * @return void
 *
 * @throws Exception
 */
function view($view, $params = [])
{
    \Atom\Poison\Poison::instance()->render($view, $params);
}

/**
 * Throws an HTTP Exception.
 *
 * @param int $code
 * @param string|null $exception_msg
 *
 * @return void
 */
function abort($code, $exception_msg = null)
{
    if (isset($exception_msg))
        header('HTTP/1.0 ' . strval($code) . ' ' . $exception_msg, true, $code);
    else
        header('HTTP/1.0 ' . strval($code), true, $code);
}

/**
 * Throws an HTTP Exception if the given expression is evaluated to true.
 *
 * @param bool $expression
 * @param int $code
 * 
 * @return void
 */
function abort_if($expression, $code)
{
    if ($expression) abort($code);
}

/**
 * Throws an HTTP Exception if the given expression is evaluated to false.
 *
 * @param bool $expression
 * @param int $code
 *
 * @return void
 */
function abort_unless($expression, $code)
{
    if (!$expression) abort($code);
}

/**
 * Determine whether the given value is blank.
 *
 * @param mixed $value
 * @return bool
 */
function blank($value)
{
    return is_null($value) || $value == "" || $value == '' || $value == array();
}

/**
 * Get the global encryption helper.
 *
 * @return \Atom\Encryption\Encrypter
 */
function crypter()
{
    return \Atom\Encryption\Encrypter::instance();
}

/**
 * Decrypt the given value.
 *
 * @param mixed $encrypted_value
 * @return mixed
 * @throws Exception
 */
function decrypt($encrypted_value)
{
    return crypter()->decrypt($encrypted_value);
}

/**
 * Encrypt the given value.
 *
 * @param mixed $value
 * @return mixed
 * @throws Exception
 */
function encrypt($value)
{
    return crypter()->encrypt($value);
}

/**
 * Determine whether the given value is filled.
 *
 * @param mixed $value
 * @return bool
 */
function filled($value)
{
    return !blank($value);
}

/**
 * Get the global router helper.
 *
 * @return \Atom\Routing\Router|mixed
 */
function router()
{
    return \Atom\Routing\Router::instance();
}

/**
 * Get the global renderer helper.
 *
 * @return \Atom\Poison\Poison|mixed
 * @throws Exception
 */
function poison()
{
    return \Atom\Poison\Poison::instance();
}

/**
 * Redirect to the given named route.
 *
 * @param string $to
 * @param array $params
 * @return \Atom\Request\Request
 *
 * @throws Exception
 */
function redirect($to, $params = [])
{
    return \Atom\Routing\Router::instance()->redirect($to, $params);
}

/**
 * Call given callable with given value.
 *
 * @param mixed $value
 * @param callable|string|null $callable
 * @return mixed
 *
 * @throws Exception
 */
function tap($value, $callable = null)
{
    if (!is_null($callable)) {
        if (is_callable($callable)) {
            return call_user_func($callable, func_get_arg(0));
        } elseif (is_string($callable)) {
            $params = explode('#', $callable);
            $controller = app()->controllersNamespace . $params[0];
            $controller = new $controller();

            return call_user_func_array([$controller, $params[1]], array(func_get_arg(0)));
        } else {
            throw new RuntimeException("Invalid callback function");
        }
    } else {
        return $value;
    }
}

/**
 * Throw given exception if expression evaluated to true.
 *
 * @param bool $expression
 * @param Throwable $exception
 */
function throw_if($expression, $exception)
{
    if ($expression) {
        throw new $exception;
    }
}

/**
 * Throw given exception if expression evaluated to false.
 *
 * @param bool $expression
 * @param Throwable $exception
 */
function throw_unless($expression, $exception)
{
    if (!$expression) {
        throw new $exception;
    }
}

/**
 * Executes callable on value and return result.
 *
 * @param mixed $value
 * @param callable $callable
 * @return mixed
 *
 * @throws Exception
 */
function transform($value, $callable)
{
    if (!is_null($value) && !is_null($callable)) {
        if (is_callable($callable)) {
            return call_user_func($callable, $value);
        } else {
            throw new Exception("Invalid callback function");
        }
    } else {
        return $value;
    }
}

/**
 * Return the given value.
 *
 * @param callable|mixed $value
 * @return mixed
 */
function value($value)
{
    if (is_callable($value)) {
        return call_user_func($value, []);
    }

    return $value;
}

/**
 * Get the global environment helper.
 *
 * @param null $key
 * @param null $default
 * @return \Atom\Environment\Config|mixed|null
 *
 * @throws \Atom\Exceptions\ConfigurationException
 */
function env($key = null, $default = null)
{
    if (!is_null($key))
        return \Atom\Environment\Config::instance()->config($key, $default);

    return \Atom\Environment\Config::instance();
}

/**
 * Get the global flash helper.
 *
 * @param string|null $message
 * @param string|null $type
 * @return \Atom\Flash\Flash
 *
 * @throws \Atom\Exceptions\SessionException
 */
function flash($message = null, $type = null)
{
    if (!is_null($message))
        \Atom\Flash\Flash::instance()->add($message, $type);

    return \Atom\Flash\Flash::instance();
}

/**
 * Get the global auth helper.
 *
 * @return \Atom\Authentication\Auth
 *
 * @throws \Atom\Exceptions\ConfigurationException
 */
function auth()
{
    return \Atom\Authentication\Auth::instance();
}

/**
 * Hash the given value.
 *
 * @param string $value
 * @param string $iv
 * @return string
 *
 * @throws \Atom\Exceptions\ConfigurationException
 */
function hash_make($value, $iv)
{
    return \Atom\Encryption\Hash::make($value, $iv);
}

/**
 * Check if hashed value equals to given value.
 *
 * @param string $hashed
 * @param string $value
 * @param string|null $iv
 * @return bool
 *
 * @throws \Atom\Exceptions\ConfigurationException
 */
function hash_check($hashed, $value, $iv = null)
{
    return \Atom\Encryption\Hash::check($hashed, $value, $iv);
}

/**
 * Set cookie.
 *
 * @param string $name
 * @param mixed $value
 * @param int|null $expire
 * @return bool
 *
 * @throws \Exception
 */
function cookie($name, $value, $expire = null)
{
    return \Atom\Cookie\Cookie::set($name, $value, $expire);
}