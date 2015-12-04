<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;
use LogicException;

class Terminal
{
    const DEFAULT_DIMENSION_UNITS = SSH2_TERM_UNIT_CHARS;

    protected $width = 80;
    protected $height = 25;
    protected $env = [];
    protected $dimensionUnits = null;

    /**
     * Create new instance.
     *
     * @return Terminal
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Get the SSH2 terminal unit value.
     * 
     * @param string $dimensionUnits chars|pixels
     *
     * @return int
     *
     * @throws UnexpectedValueException if the unit name is incorrect
     */
    public function getDimensionUnitsId($dimensionUnits)
    {
        $map = [
            'chars' => SSH2_TERM_UNIT_CHARS,
            'pixels' => SSH2_TERM_UNIT_PIXELS,
        ];

        if (!isset($map[$dimensionUnits])) {
            throw new UnexpectedValueException(sprintf(
                'Incorrect dimension unit "%s". You must use one of [%s]',
                $dimensionUnits,
                implode(', ', array_keys($map))
            ));
        }

        return $map[$dimensionUnits];
    }

    /**
     * Set the unit that width and height are given in.
     *
     * @param string $dimensionUnits chars|pixels
     * @param bool   $force
     *
     * @return $this;
     *
     * @throws UnexpectedValueException if $dimensionUnits is not "chars" or "pixels".
     * @throws LogicException           if the dimensionUnits has been set to pixels, but is changed back to chars or vice versa.
     */
    public function dimensionUnits($dimensionUnits)
    {
        $candidate = $this->getDimensionUnitsId($dimensionUnits);

        // No change. Just return
        if ($candidate === $this->dimensionUnits) {
            return $this;
        }

        // Allow setting dimensionUnits once
        if (null === $this->dimensionUnits) {
            $this->dimensionUnits = $candidate;

            return $this;
        }

        // We cannot change the dimensionUnits once it has been set.
        throw new LogicException(sprintf(
            'You cannot change dimensionUnits. It has already been set to %s',
            $this->dimensionUnits
        ));
    }

    /**
     * Set the width of the terminal.
     *
     * @param int    $width
     * @param string $dimensionUnits chars|pixels
     *
     * @return $this;
     */
    public function width($width, $dimensionUnits = null)
    {
        $this->width = $width;
        if (null === $dimensionUnits) {
            return $this;
        }

        return $this->dimensionUnits($dimensionUnits, false);
    }

    /**
     * Set the height of the terminal.
     *
     * @param int    $height
     * @param string $dimensionUnits chars|pixels
     *
     * @return $this;
     */
    public function height($height, $dimensionUnits = null)
    {
        $this->height = $height;
        if (null === $dimensionUnits) {
            return $this;
        }

        return $this->dimensionUnits($dimensionUnits, false);
    }

    /**
     * Add environment variable(s) to the terminal.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this;
     */
    public function env($key, $value = null)
    {
        if (is_array($key)) {
            $this->env = array_merge($this->env, $key);

            return $this;
        }

        $this->env[$key] = $value;

        return $this;
    }

    /**
     * Get the width of the terminal.
     *
     * @return int;
     */
    public function getWidth()
    {
        return $this->width;
    }

    /** 
     * Get the height of the terminal.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get the environment variables.
     *
     * @return array
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Get the dimension units as an SSH terminal id.
     *
     * @return int
     */
    public function getDimensionUnits()
    {
        return $this->dimensionUnits ?: static::DEFAULT_DIMENSION_UNITS;
    }
}
