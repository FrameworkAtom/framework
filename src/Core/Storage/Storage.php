<?php

namespace Atom\Storage;


use Atom\Exceptions\FileNotFoundException;
use Atom\Exceptions\InvalidDiskTypeException;
use Atom\Exceptions\InvalidFileException;
use Atom\Filesystem\Filesystem;

class Storage
{

    /**
     * Storage type.
     *
     * @var string
     */
    protected $disk;

    /**
     * Storage disk path.
     *
     * @var string
     */
    protected $diskPath;

    /**
     * Available files on the disk.
     *
     * @var mixed
     */
    protected $files;

    /**
     * Disk file system.
     *
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * Create a new Storage instance.
     *
     * @param string $disk
     * @return void
     *
     * @throws InvalidDiskTypeException
     */
    public function __construct($disk = 'local')
    {
        if (!is_null($disk)) {
            if (strtolower($disk) == 'local' || strtolower($disk) == 'public')
                $this->disk = strtolower($disk);
            else
                throw new InvalidDiskTypeException("The disk type {$disk} is not valid.");
        } else {
            throw new InvalidDiskTypeException("The disk type cannot be empty.");
        }

        $this->setDiskPath();
        $this->loadFileSystem();
        $this->loadDisk();
    }

    /**
     * Load the disk file system.
     *
     * @return void
     */
    private function loadFileSystem()
    {
        $this->fileSystem = new Filesystem();
    }

    /**
     * Get the disk file system.
     *
     * @return Filesystem
     */
    public function getFileSystem()
    {
        if (isset($this->fileSystem))
            return $this->fileSystem;

        $this->loadFileSystem();

        return $this->fileSystem;
    }

    /**
     * Set the disk path.
     *
     * @return void
     *
     * @throws \Exception
     */
    private function setDiskPath()
    {
        if ($this->disk == 'local') {
            $this->diskPath = app()->appStoragePath;
        } elseif ($this->disk == 'public') {
            $this->diskPath = app()->publicStoragePath;
        }
    }

    /**
     * Get the disk path.
     *
     * @return string
     */
    public function getDiskPath()
    {
        return $this->diskPath;
    }

    /**
     * Load available files on the disk.
     *
     * @return void
     */
    public function loadDisk()
    {
        $this->files = $this->getFileSystem()->files($this->getDiskPath());
    }

    /**
     * Get the path of the specified file.
     *
     * @param string $file
     * @return string
     */
    public function path($file)
    {
        return $this->getDiskPath() . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Get the loaded files available on the disk.
     *
     * @param bool $reload
     * @return array
     */
    public function getFiles($reload = false)
    {
        if ($reload)
            $this->loadDisk();

        return $this->files;
    }

    /**
     * Get the contents of the specified file.
     *
     * @param string $file
     * @param bool $unserialize
     * @return mixed
     * 
     * @throws FileNotFoundException|InvalidFileException
     */
    public function get($file, $unserialize = false)
    {
        $path = $this->path($file);

        if ($this->getFileSystem()->exists($path)) {
            if ($this->getFileSystem()->isFile($path)) {
                if ($unserialize)
                    return unserialize($this->getFileSystem()->get($path));
                else
                    return $this->getFileSystem()->get($path);
            } else {
                throw new InvalidFileException("{$path} is not a valid file.");
            }
        } else {
            throw new FileNotFoundException("File does not exist at path {$path}");
        }
    }

    /**
     * Get the returned value of the given file.
     *
     * @param string $file
     * @return mixed
     *
     * @throws FileNotFoundException|InvalidFileException
     */
    public function requires($file)
    {
        $path = $this->path($file);

        if ($this->getFileSystem()->exists($path)) {
            if ($this->getFileSystem()->isFile($path)) {
                return $this->getFileSystem()->getRequire($path);
            } else {
                throw new InvalidFileException("{$path} is not a valid file.");
            }
        } else {
            throw new FileNotFoundException("File does not exist at path {$path}");
        }
    }

    /**
     * Determine if a file with the given name exists.
     *
     * @param string $file
     * @return bool
     */
    public function exists($file)
    {
        $path = $this->path($file);

        return $this->getFileSystem()->exists($path);
    }

    /**
     * Delete the file with the given name.
     *
     * @param string $file
     * @return bool
     *
     * @throws FileNotFoundException
     */
    public function delete($file)
    {
        $path = $this->path($file);

        if ($this->exists($file))
            return $this->getFileSystem()->delete($path);
        else
            throw new FileNotFoundException("File does not exist at path {$path}");
    }

    /**
     * Store raw contents in the given file.
     *
     * @param string $file
     * @param mixed $contents
     * @param bool $serialize
     * @return int
     */
    public function put($file, $contents, $serialize = false)
    {
        $path = $this->path($file);

        return $this->getFileSystem()->put($path, $contents, $serialize);
    }

}