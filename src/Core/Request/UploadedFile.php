<?php

namespace Atom\Request;

require __DIR__ . '/../Helpers/String.php';


use Atom\Exceptions\ConfigurationException;
use Atom\Exceptions\FileNotFoundException;
use Atom\Exceptions\InvalidDiskTypeException;
use Atom\Exceptions\InvalidFileException;
use Atom\Exceptions\PropertyNotFoundException;
use Atom\Filesystem\Filesystem;
use Atom\Storage\Storage;

class UploadedFile
{

    /**
     * Name of the uploaded file.
     *
     * @var string
     */
    protected $name;

    /**
     * Type of the uploaded file.
     *
     * @var string
     */
    protected $type;

    /**
     * Temporary path of the uploaded file.
     *
     * @var string
     */
    protected $tmp_name;

    /**
     * Real path of the uploaded file after storing.
     *
     * @var string
     */
    protected $real_path;

    /**
     * Real name of the uploaded file after storing.
     *
     * @var string
     */
    protected $real_name;

    /**
     * Size of the uploaded file.
     *
     * @var int
     */
    protected $size;

    /**
     * Uploaded file extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * Uploaded file filesystem manager.
     *
     * @var Storage
     */
    protected $file_system;

    /**
     * Uploaded file storage manager.
     *
     * @var Storage
     */
    protected $storage;

    /**
     * Create new instance of an uploaded file.
     *
     * @param array $file_params
     * @param Storage|null $storage
     * @return void
     *
     * @throws InvalidDiskTypeException
     */
    public function __construct($file_params, $storage = null)
    {
        foreach ($file_params as $k => $v) {
            $this->$k = $v;
        }

        $this->initialize($storage);
    }

    /**
     * Initialize the file properties.
     *
     * @param Storage|null $storage
     * @return void
     * 
     * @throws InvalidDiskTypeException
     */
    private function initialize($storage = null)
    {
        $this->extension = explode('.', $this->name)[count(explode('.', $this->name)) - 1];
        $this->file_system = new Filesystem();

        if (!is_null($storage))
            $this->storage = $storage;
        else
            $this->storage = new Storage();
    }

    /**
     * Store the uploaded file on the given disk.
     *
     * @param bool $public
     * @param bool $serialize
     * @return $this|bool
     *
     * @throws InvalidDiskTypeException
     * @throws FileNotFoundException
     * @throws InvalidFileException
     * @throws ConfigurationException
     */
    public function store($public = false, $serialize = false)
    {
        if ($public)
            $this->storage = new Storage('public');

        $file_name = uniqid(snake_case(env('app_name')) . '_') . '.' . $this->extension;

        if ($this->storage->put($file_name, $this->file_system->get($this->tmp_name), $serialize) != false) {
            $this->real_name = $file_name;
            $this->real_path = $this->storage->path($file_name);
            $this->storage->loadDisk();

            return $this;
        } else {
            return false;
        }
    }

    /**
     * Store the uploaded file with the given name on the given disk.
     *
     * @param string $file_name
     * @param bool $public
     * @param bool $serialize
     * @return $this|bool
     *
     * @throws FileNotFoundException
     * @throws InvalidDiskTypeException
     * @throws InvalidFileException
     */
    public function storeAs($file_name, $public = false, $serialize = false)
    {
        if ($public)
            $this->storage = new Storage('public');

        $file_name .= '.' . $this->extension;

        if ($this->storage->put($file_name, $this->file_system->get($this->tmp_name), $serialize) != false) {
            $this->real_name = $file_name;
            $this->real_path = $this->storage->path($file_name);
            $this->storage->loadDisk();

            return $this;
        } else {
            return false;
        }
    }

    /**
     * Get property with the given key.
     *
     * @param $key
     * @return mixed
     *
     * @throws PropertyNotFoundException
     */
    public function get($key)
    {
        if (isset($this->$key))
            return $this->$key;

        throw new PropertyNotFoundException("The property {$key} doesn't exists in " . UploadedFile::class);
    }

}