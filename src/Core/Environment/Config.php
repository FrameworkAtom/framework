<?php

namespace Atom\Environment;


use Atom\Exceptions\ConfigurationException;

class Config
{

    /**
     * Configuration instance.
     *
     * @var Config
     */
    protected static $_instance = null;

    /**
     * Configuration data.
     *
     * @var mixed
     */
    protected $config;

    /**
     * Config file path.
     *
     * @var string
     */
    protected $config_file;

    /**
     * Create configuration instance.
     *
     * @throws ConfigurationException
     */
    public function __construct($config_file = null)
    {
        $this->config_file = is_null($config_file) ? config_path() . DIRECTORY_SEPARATOR . 'env.json' : $config_file;
        $this->load();
    }

    /**
     * Get configuration instance.
     *
     * @return Config
     *
     * @throws ConfigurationException
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Config();

        return static::$_instance;
    }

    /**
     * Load the configurations from file.
     *
     * @return void
     *
     * @throws ConfigurationException
     */
    protected function load()
    {
        if (file_exists($this->config_file)) {
            return $this->config = json_decode(file_get_contents($this->config_file));
        } else {
            throw new ConfigurationException("Configuration file doesn't exists.");
        }
    }

    /**
     * Get configuration data.
     *
     * @param string|null $key
     * @param string|null $default
     * @return mixed|null
     */
    public function config($key = null, $default = null)
    {
        if (!is_null($key) && isset($this->config->$key))
            return $this->config->$key;

        if (is_null($key) && isset($this->config))
            return $this->config;

        return $default;
    }

    /**
     * Insert data into configuration.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function put($key, $value)
    {
        if (!isset($this->config->$key))
            $this->config->$key = $value;

        $this->config = json_encode($this->config, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        $saved = file_put_contents($this->config_file, $this->config);

        if ($saved != false)
            $this->load();
        else
            throw new ConfigurationException("Can't write the configuration");

        return $saved != false;
    }

    /**
     * Create new config file.
     *
     * @param mixed $config
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function create($config)
    {
        $this->config = json_encode($config, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        $saved = file_put_contents($this->config_file, $this->config);
        
        if ($saved != false)
            $this->load();
        else
            throw new ConfigurationException("Can't write the configurations");

        return $saved != false;
    }

}