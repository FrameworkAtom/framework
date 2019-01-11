<?php

/**
 * Set of helpers functions for the strings.
 */

if (!function_exists('snake_case')) {

    /**
     * Parse a string to snake case format.
     *
     * @param $string
     * @return string
     */
    function snake_case($string)
    {
        return strtolower(str_replace(' ', '_', $string));
    }

}