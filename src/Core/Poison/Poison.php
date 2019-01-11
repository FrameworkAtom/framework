<?php

namespace Atom\Poison;

require_once __DIR__ . '/../Helpers/Misc.php';


use Atom\Exceptions\PoisonException;

class Poison
{

    /**
     * Poison instance.
     *
     * @var Poison
     */
    protected static $_instance = null;

    /**
     * Global renderer views path.
     *
     * @var string
     */
    private static $viewsPath;

    /**
     * Global renderer cache path.
     *
     * @var string
     */
    private static $cachePath;

    /**
     * Global renderer view extension.
     *
     * @var string
     */
    private static $viewExtension = '.poison.php';

    /**
     * Current renderer views path.
     *
     * @var string
     */
    private $views_path;

    /**
     * Current renderer cache path.
     *
     * @var string
     */
    private $cache_path;

    /**
     * Current renderer view extension.
     *
     * @var string
     */
    private $view_extension = '.poison.php';

    /**
     * Globals accessible variables.
     *
     * @var array
     */
    private $globals = [];

    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Poison();

        return static::$_instance;
    }

    /**
     * Create new instance of the renderer.
     *
     * @param string $path Path to the views directory.
     * @param string $cache Path to the cache directory.
     * @return void
     */
    public function __construct($path = null, $cache = null)
    {
        if (!is_null($path))
            $this->views_path = $path;
        else
            $this->views_path = static::$viewsPath;

        if (!is_null($cache))
            $this->cache_path = $cache;
        else
            $this->cache_path = static::$cachePath;
    }

    /**
     * Set global renderer views path.
     *
     * @param string $path
     * @return void
     */
    public static function setViewsPath($path)
    {
        if (!is_null($path))
            self::$viewsPath = $path;
    }

    /**
     * Set global renderer cache path.
     *
     * @param string $path
     * @return void
     */
    public static function setCachePath($path)
    {
        if (!is_null($path))
            self::$cachePath = $path;
    }

    /**
     * Change the global renderer views extension.
     *
     * @param string $extension View extension in the format ".extension"
     * @return void
     */
    public static function setViewExtension($extension)
    {
        if (!is_null($extension))
            self::$viewExtension = $extension;
    }

    /**
     * Change the current renderer views extension.
     *
     * @param string $extension View extension in the format ".extension"
     * @return void
     */
    public function setExtension($extension)
    {
        if (!is_null($extension))
            $this->view_extension = $extension;
    }

    /**
     * Clear the views cache folder.
     *
     * @return void
     */
    private function clearCache()
    {
        $files = scandir($this->cache_path);

        if (count($files) >= 8) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..')
                    unlink($this->cache_path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    /**
     * Parse the given tag to valid PHP tag.
     *
     * @param $tag
     * @return mixed|string
     */
    private function parseTag($tag)
    {
        $real = $tag;

        if (startsWith($tag, '@include')) {
            $real = str_replace("@include", "<?= \$poison->include", $tag);
            $real = $real . '; ?>';
        }

        if (startsWith($tag, '{{') && endsWith($tag, '}}')) {
            $real = str_replace("{{", "<?=", $tag);
            $real = str_replace("}}", ";?>", $real);
        }

        if (startsWith($tag, '@url')) {
            $real = str_replace("@url", "<?= \$router->url", $tag);
            $real = str_replace(")", "); ?>", $real);
        }

        if(startsWith($tag, '@foreach')) {
            $real = str_replace("@foreach", "<?php foreach", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@endforeach')) {
            $real = str_replace("@endforeach", "<?php endforeach; ?>", $tag);
        }

        if(startsWith($tag, '@if')) {
            $real = str_replace("@if", "<?php if", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@else')) {
            $real = str_replace("@else", "<?php else: ?>", $tag);
        }

        if(startsWith($tag, '@elseif')) {
            $real = str_replace("@elseif", "<?php elseif", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@endif')) {
            $real = str_replace("@endif", "<?php endif; ?>", $tag);
        }

        if (startsWith($tag, '{?')) {
            $real = str_replace("{?", "<?php", $tag);
        }

        if (startsWith($tag, '?}')) {
            $real = str_replace("?}", "?>", $tag);
        }

        if (startsWith($tag, '@extend')) {
            $real = str_replace('@extend', '', $tag);
            $real = str_replace('(', '[', $real);
            $real = str_replace(')', ']', $real);
            $real = "<?php \$extension=" . $real;
            $real = $real . "; ?>";
            $real = str_replace("\"", "'", $real);
        }

        if (startsWith($tag, '@content')) {
            $real = str_replace("@content", "<?php ob_start(); ?>", $tag);
        }

        if (startsWith($tag, '@endcontent')) {
            $real = str_replace("@endcontent", "<?php \$extension[1]['content'] = ob_get_clean(); \$poison->render(\$extension[0], \$extension[1]); ?>", $tag);
        }

        return $real;
    }

    /**
     * Create a formatted PHP file and store it to the cache folder.
     *
     * @param string $contents
     * @param array|null $params
     * @return string|bool
     */
    private function parseTemplate($contents, $params = [])
    {

        preg_match_all("#(\{[@?!])(.*){1,}([@?!]\})|(\@\w+)([\(]*(.+)+?[\)]*)|(\{{2}(.*){1,}\}{2})|(\{[@?!]+)|([@?!]+\})#imxU", $contents, $matches);

        if (count($matches) > 0)
        {

            $tags = $matches[0];

            foreach ($tags as $tag):

                $real = $this->parseTag($tag);

                $contents = str_replace($tag, $real, $contents);

            endforeach;

            $cachedFile = $this->cache_path . DIRECTORY_SEPARATOR . uniqid("poison.") . '.php';

            file_put_contents($cachedFile, $contents);

            ob_start();

            extract($this->globals);
            extract($params);
            require($cachedFile);

            return ob_get_clean();

        } else {
            return $contents;
        }

    }

    /**
     * Render a view.
     *
     * @param string $view
     * @param array|null $params
     * @return void
     *
     * @throws PoisonException
     */
    public function render($view, $params = [])
    {
        $this->clearCache();

        $view = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $path = $this->views_path . DIRECTORY_SEPARATOR . $view . $this->view_extension;

        if (file_exists($path)) {
            $output = $this->parseTemplate(file_get_contents($path), $params);
            echo $output;
        } else {
            throw new PoisonException("View [{$view}] not found.");
        }
    }

    /**
     * Add global variable.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addGlobal($key, $value)
    {
        $this->globals[$key] = $value;

        return $this;
    }

    /**
     * Include a file to the view to render.
     *
     * @param string $file
     * @return void
     *
     * @throws PoisonException
     */
    public function include($file)
    {
        $file = str_replace('.', DIRECTORY_SEPARATOR, $file);
        $path = $this->views_path . DIRECTORY_SEPARATOR . $file . $this->view_extension;

        if (file_exists($path)) {
            $output = $this->parseTemplate(file_get_contents($path));
            echo $output;
        } else {
            throw new PoisonException("File [{$file}] not found.");
        }
    }

}