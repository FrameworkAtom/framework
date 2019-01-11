<?php

/**
 * Return global moment helper.
 *
 * @param string|int|null $time
 * @return \Atom\Moment\Moment|mixed
 */
function moment($time = null)
{
    return new \Atom\Moment\Moment($time);
}

/**
 * Return now moment.
 *
 * @return \Atom\Moment\Moment
 */
function now()
{
    return new \Atom\Moment\Moment();
}

/**
 * Return today string.
 *
 * @return false|string
 */
function today()
{
    return now()->format('Y-m-d');
}