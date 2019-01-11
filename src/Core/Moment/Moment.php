<?php

namespace Atom\Moment;


class Moment
{

    /**
     * Current date.
     *
     * @var string
     */
    protected $current;

    /**
     * Current timestamp;
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Create new instance of Moment.
     *
     * @param string|int|null $time
     * @return void
     */
    public function __construct($time = null)
    {
        if (isset($time)) {
            if (is_string($time)) {
                $time = strtolower($time);
                $this->timestamp = strtotime($time);
                $this->current = $time;
            } elseif (is_int($time)) {
                $this->timestamp = $time;
                $this->current = date('Y-m-d h:i:s', $this->timestamp);
            }
        } else {
            $this->timestamp = strtotime('now');
            $this->current = 'now';
        }
    }

    /**
     * Format the current moment.
     *
     * @param string|null $format
     * @return false|string
     */
    public function format($format = null)
    {
        if (isset($format) && is_string($format)) {
            return date($format, $this->timestamp);
        } else {
            return date('Y-m-d h:i:s', $this->timestamp);
        }
    }

    /**
     * Add given value to the current moment.
     * 
     * @param string $value
     * @return Moment
     *
     * @throws \Exception
     */
    public function add($value)
    {
        if (is_string($value)) {
            $date = new \DateTime('@' . strval($this->timestamp));
            $date->add(date_interval_create_from_date_string($value));
        } elseif (is_int($value)) {
            $date = new \DateTime('@' . strval($this->timestamp + $value));
        } else {
            $date = new \DateTime('now');
        }

        $result = new Moment(date_timestamp_get($date));

        return $result;
    }

    /**
     * Remove given value to the current moment.
     *
     * @param string $value
     * @return Moment
     *
     * @throws \Exception
     */
    public function remove($value)
    {
        if (is_string($value)) {
            $date = new \DateTime('@' . strval($this->timestamp));
            $date->sub(date_interval_create_from_date_string($value));
        } elseif (is_int($value)) {
            $date = new \DateTime('@' . strval($this->timestamp - $value));
        } else {
            $date = new \DateTime('now');
        }

        $result = new Moment(date_timestamp_get($date));

        return $result;
    }

    /**
     * Get the moment timestamp.
     *
     * @return false|int
     */
    public function timestamp()
    {
        return $this->timestamp;
    }
    
}