<?php

/**
 * Set of helpers functions for the paths.
 */

/**
 * @return string
 * @throws Exception
 */
function app_path()
{
    return app()->appPath;
}

/**
 * @return string
 * @throws Exception
 */
function base_path()
{
    return app()->getBasePath();
}

/**
 * @return string
 * @throws Exception
 */
function config_path()
{
    return app()->configsPath;
}

/**
 * @return string
 * @throws Exception
 */
function public_path()
{
    return app()->publicPath;
}

/**
 * @return string
 * @throws Exception
 */
function resource_path()
{
    return app()->resourcesPath;
}

/**
 * @return string
 * @throws Exception
 */
function storage_path()
{
    return app()->storagePath;
}

/**
 * @return string
 * @throws Exception
 */
function views_path()
{
    return app()->viewsPath;
}

/**
 * @return string
 * @throws Exception
 */
function helpers_path()
{
    return app()->helpersPath;
}

/**
 * @return string
 * @throws Exception
 */
function cache_path()
{
    return app()->cachePath;
}

/**
 * @return string
 * @throws Exception
 */
function public_storage_path()
{
    return app()->publicStoragePath;
}

/**
 * @return string
 * @throws Exception
 */
function app_namespace()
{
    return app()->namespace;
}

/**
 * @return string
 */
function controllers_namespace()
{
    return app()->controllersNamespace;
}

/**
 * @return string
 */
function models_namespace()
{
    return app()->modelsNamespace;
}