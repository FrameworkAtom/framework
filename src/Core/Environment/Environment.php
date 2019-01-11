<?php

require_once __DIR__ . '/../helpers.php';

/**
 * Get environment variable value.
 *
 * @param string|null $key
 * @return mixed
 */
function env($key = null)
{
    if (!isset($GLOBALS['configs']))
        $GLOBALS['configs'] = json_decode(file_get_contents($GLOBALS['config_path'] . DIRECTORY_SEPARATOR . 'env.json'));

    if (!is_null($key) && isset($GLOBALS['configs']->$key))
        return $GLOBALS['configs']->$key;
    elseif (is_null($key) && isset($GLOBALS['configs']))
        return $GLOBALS['configs'];
    else
        Throw new \Atom\Environment\EnvironmentException("Environment variable " . $key . " not found.");
}

/**
 * Set environment configurations path.
 *
 * @param string $path
 */
function setEnvPath($path)
{
    $GLOBALS['config_path'] = $path;
}
