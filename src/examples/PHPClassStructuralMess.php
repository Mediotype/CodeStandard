<?php

/**
 * @author    Mediotype Development
 * @copyright 2018 Mediotype
 */

namespace Mediotype\Module\Framework;

/**
 * Provide a demonstration of a class which does not adhere to the Mediotype standards.
 */
class Example
    extends AbstractExample
{
    private $enabled = false;

    public $config = [];

    /**
     * Set the enablement status.
     *
     * @param boolean $state
     * @return boolean
     */
    public function setState(bool $state = true)
    {
        $this->enabled = $state;

        return $state;
    }

    /**
     * Sanitize the given input.
     *
     * @param string $input
     * @return string
     */
    private function sanitize($input)
    {
        return strip_tags($input);
    }

    /**
     * Get a config value.
     *
     * @param string $key
     * @return mixed|null
     */
    protected function _getConfig($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * Public accessor for config retrieval.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getConfig($key)
    {
        return $this->_getConfig($key);
    }

    /**
     * Set a config value.
     *
     * @param string $key
     * @param string|null $value
     * @return void
     */
    public function setConfig($key, $value = null)
    {
        $this->config[$key] = $this->sanitize($value);
    }
}
