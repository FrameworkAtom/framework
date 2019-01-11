<?php

namespace Atom\Helpers;

require_once 'Misc.php';


use Atom\Filesystem\Filesystem;

class HelpersManager
{

    /**
     * Load all the application helpers.
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function autoload()
    {
        $helpers_dir = app()->helpersPath;
        $file_system = new Filesystem();

        foreach ($file_system->files($helpers_dir) as $file) {
            $file_system->requireOnce($helpers_dir . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     * Load all the Atom Framework helpers.
     *
     * @return void
     */
    public static function autoloadApp()
    {
        $helpers_dir = __DIR__;
        $file_system = new Filesystem();

        foreach ($file_system->files($helpers_dir) as $file) {
            if (!startsWith($file, 'HelpersManager'))
                $file_system->requireOnce($helpers_dir . DIRECTORY_SEPARATOR . $file);
        }
    }

}