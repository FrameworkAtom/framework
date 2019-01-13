<?php

namespace Atom;

require_once __DIR__ . '/Helpers/Misc.php';


use Atom\Encryption\Encrypter;
use Atom\Helpers\HelpersManager;
use Atom\Moment\Moment;
use Atom\Poison\Poison;
use Atom\Routing\Router;
use Atom\Environment\Environment;
use Atom\Session\Session;

class Application
{

    /**
     * The Atom Framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Application instance.
     *
     * @var Application
     */
    protected static $_instance = null;

    /**
     * The base path for the Atom application initialization.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Application database configurations.
     *
     * @var mixed
     */
    protected $databaseConfig;

    /**
     * The cores of the Atom application.
     *
     * @var array
     */
    protected $cores;

    /**
     * Application started.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Get the Application instance.
     *
     * @return Application
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Application();

        return static::$_instance;
    }
    
    /**
     * Create a new Atom application instance.
     *
     * @param string|null $basePath
     * @param string|null $namespace
     * @return void
     */
    public function __construct($basePath = null, $namespace = null)
    {
        if ($basePath)
            $this->setBasePath($basePath);

        if ($namespace)
            $this->setNamespace($namespace);

        $this->bindCores();

        static::$_instance = $this;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Set the base path of the application.
     *
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->bindPaths();

        return $this;
    }

    /**
     * Get the base path of the application.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set the application default namespace.
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        if (!is_null($namespace)) {
            $this->namespace = $namespace;
            $this->bindNamespaces();
        }
    }

    /**
     * Bind all of the cores in the application global.
     *
     * @return void
     */
    protected function bindCores()
    {
        Router::setDefaultNamespace($this->namespace . '\\Controllers\\');
        Poison::setViewsPath($this->viewsPath);
        Poison::setCachePath($this->viewsCachePath);
        $this->cores['router'] = Router::instance();
        $this->cores['poison'] = Poison::instance();
    }

    /**
     * Bind all the application paths.
     *
     * @return void
     */
    protected function bindPaths()
    {
        $this->appPath = $this->basePath . DIRECTORY_SEPARATOR . 'app';
        $this->controllersPath = $this->appPath . DIRECTORY_SEPARATOR . 'controllers';
        $this->modelsPath = $this->appPath . DIRECTORY_SEPARATOR . 'models';
        $this->middlewaresPath = $this->appPath . DIRECTORY_SEPARATOR . 'middlewares';
        $this->configsPath = $this->basePath . DIRECTORY_SEPARATOR . 'config';
        $this->publicPath = $this->basePath . DIRECTORY_SEPARATOR . 'public';
        $this->resourcesPath = $this->basePath . DIRECTORY_SEPARATOR . 'resources';
        $this->helpersPath = $this->appPath . DIRECTORY_SEPARATOR . 'helpers';
        $this->viewsPath = $this->resourcesPath . DIRECTORY_SEPARATOR . 'views';
        $this->storagePath = $this->basePath . DIRECTORY_SEPARATOR . 'storage';
        $this->cachePath = $this->storagePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache';
        $this->viewsCachePath = $this->storagePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views';
        $this->sessionsPath = $this->storagePath . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions';
        $this->appStoragePath = $this->storagePath . DIRECTORY_SEPARATOR . 'app';
        $this->publicStoragePath = $this->publicPath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Bind application namespaces.
     *
     * @return void
     */
    protected function bindNamespaces()
    {
        $this->controllersNamespace = $this->namespace . '\\Controllers\\';
        $this->modelsNamespace = $this->namespace . '\\Models\\';
    }

    /**
     * Load application routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        require_once $this->configsPath . DIRECTORY_SEPARATOR . 'routes.php';
    }

    protected function loadHelpers()
    {
        HelpersManager::autoloadApp();
    }

    /**
     * Determine if the application have configurations and generate if not.
     *
     * @param bool $force
     * @return bool
     */
    protected function secure($force = false)
    {
        $config = env()->config();
        $secure = false;

        if (!isset($config->app_name)) {
            $config->app_name = "Atom Application";
        }

        if ($force) {
            $config->app_key = utf8_encode(Encrypter::generateKey());
        } else {
            if (isset($config->app_key) && $config->app_key != "") {
                $secure = true;
            } else {
                $config->app_key = utf8_encode(Encrypter::generateKey());
            }
        }

        if (!isset($config->locale))
            $config->locale = "en";

        if (!isset($config->database)) {
            $database = (object) null;
            $database->host = "localhost";
            $database->port = "3306";
            $database->db_name = "database_name";
            $database->user = "root";
            $database->password = "";
            $config->database = $database;
        }

        $secure = env()->create($config);

        return $secure;
    }

    /**
     * Run the application.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function run()
    {
        if (!$this->started) {
            session_name('atom_session');
            extract($this->cores);

            $this->loadHelpers();
            HelpersManager::autoload();
            $this->secure();

            require_once $this->configsPath . DIRECTORY_SEPARATOR . 'routes.php';

            $this->started = true;
            $router->run();
        } else {
            throw new \RuntimeException("Application already started");
        }
    }

}
