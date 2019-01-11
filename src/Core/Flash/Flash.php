<?php

namespace Atom\Flash;


use mysql_xdevapi\Exception;

class Flash
{

    /**
     * Flash manager instance.
     *
     * @var Flash
     */
    protected static $_instance = null;

    /**
     * Get flash manager instance.
     *
     * @return Flash
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Flash();

        return static::$_instance;
    }

    /**
     * Add flash message to the application.
     *
     * @param $string message
     * @param string|null $type
     * @return void
     *
     * @throws \Atom\Exceptions\SessionException
     */
    public function add($message, $type = null)
    {
        $type = $type != null ? $type : 'message';
        $flash = json_encode([$type => $message, 'life' => 2], JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);

        session()->push('flash', $flash);
    }

    /**
     * Determine if flash message with the given type exists.
     *
     * @param string $type
     * @return bool
     *
     * @throws \Exception
     */
    public function has($type)
    {
        $has = false;

        foreach (session('flash') as $flash) {
            $flash = json_decode($flash);

            if (isset($flash->$type)) $has = true;
        }

        return $has;
    }

    /**
     * Determine if there is at least one flash message.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function exists()
    {
        return count(session('flash')) > 0;
    }

    /**
     * Get flash message.
     *
     * @param string|null $type
     * @return array
     *
     * @throws \Exception
     */
    public function get($type = null)
    {
        if (is_null($type)) {
            $items = [];
            foreach (session('flash') as $item) {
                $item = json_decode($item);
                $items[] = array_values(get_object_vars($item))[0];
            }
            
            if (count($items) == 1)
                return $items[0];
            else
                return $items;
        } else {
            
            if ($this->has($type)) {
                $items = [];
                foreach (session('flash') as $item) {
                    $item = json_decode($item);
                    if (isset($item->$type)) $items[] = $item->$type;
                }
                if (count($items) == 1)
                    return $items[0];
                else
                    return $items;
            }

        }

        return [];
    }

    /**
     * Update flash message lifetime.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function progess()
    {
        $items = [];

        if (session()->has('flash')) {
            foreach (session('flash') as $flash) {
                $real = json_decode($flash);
                $real->life--;
                $items[] = json_encode($real, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
            }

            session(['flash' => $items]);
            $items = [];

            foreach (session('flash') as $flash) {
                $real = json_decode($flash);
                if ($real->life > 0) $items[] = json_encode($real, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
            }

            session(['flash' => $items]);
        }
    }

}