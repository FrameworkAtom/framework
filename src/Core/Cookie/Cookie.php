<?php

namespace Atom\Cookie;


class Cookie
{

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
    public static function set($name, $value, $expire = null)
    {
        $expire = is_null($expire) ? now()->add('1 day')->timestamp() : $expire;
        return setcookie($name, $value, $expire);
    }

    /**
     * Get cookie with the given name.
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        return $_COOKIE[$name];
    }

}