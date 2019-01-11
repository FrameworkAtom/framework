<?php

namespace Atom\Encryption;


class Hash
{

    /**
     * Hash the given value.
     *
     * @param string $value
     * @param string|null $iv
     * @return string
     *
     * @throws \Atom\Exceptions\ConfigurationException
     */
    public static function make($value, $iv = null)
    {
        $iv = is_null($iv) ? base64_encode(env('app_name')) : base64_encode($iv);
        return hash_hmac('sha256', $iv.$value, env('app_key'));
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
    public static function check($hashed, $value, $iv = null)
    {
        return hash_equals($hashed, static::make($value, $iv));
    }

}