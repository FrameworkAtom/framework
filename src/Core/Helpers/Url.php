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
    $scheme = 'http://';

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
    return \Atom\Routing\Router::instance()->url($name, $params);
}

function url($url)
{
    $scheme = 'http://';

    return startsWith($url, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $url:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $url;
}

function secure_url($url)
{
    $scheme = 'https://';

    return startsWith($url, '/') ?
        $scheme . $_SERVER['HTTP_HOST'] . $url:
        $scheme . $_SERVER['HTTP_HOST'] . '/' . $url;
}

/**
 * Generate fully qualified URL for the given named route.
 *
 * @param string $name
 * @param array $params
 * @return string
 *
 * @throws Exception
 */
function named_url($name, $params = [])
{
    return url(route($name, $params));
}

/**
 * Generate fully qualified secure URL for the given named route.
 *
 * @param string $name
 * @param array $params
 * @return string
 *
 * @throws Exception
 */
function secure_named_url($name, $params = [])
{
    return secure_url(route($name, $params));
}