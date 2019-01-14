<?php

/**
 * Set of helpers functions for the URLs generation.
 */

/**
 * Generate URL for an asset using current scheme of the request.
 *
 * @param string $asset
 * @return string
 */
function asset($asset)
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? $_SERVER['HTTPS'] : 'http://';

    return startsWith($asset, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $asset:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $asset;
}

/**
 * Generate URL for an asset using HTTPS scheme.
 *
 * @param string $asset
 * @return string
 */
function secure_asset($asset)
{
    $scheme = 'https://';

    return startsWith($asset, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $asset:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $asset;
}

/**
 * Generate URL for the given named route.
 *
 * @param $name
 * @param array $params
 * @return string
 *
 * @throws Exception
 */
function route($name, $params = [])
{
    $url = \Atom\Routing\Router::instance()->url($name, $params);

    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? $_SERVER['HTTPS'] : 'http://';

    return startsWith($url, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $url:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $url;
}

/**
 * Generate URL from the given string.
 * 
 * @param string $url
 * @return string
 */
function url($url)
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? $_SERVER['HTTPS'] : 'https://';

    return startsWith($url, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $url:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $url;
}

/**
 * Generate secure URL from the given string.
 *
 * @param string $url
 * @return string
 */
function secure_url($url)
{
    $scheme = 'https://';

    return startsWith($url, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $url:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $url;
}
