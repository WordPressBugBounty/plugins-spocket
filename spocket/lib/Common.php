<?php

/**
 * File for Requirements class
 * php version 7.4
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket;

/**
 * Responsible for setting up functionality on both frontend and the backend
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Common
{

    /**
     * The plugin instance.
     *
     * @var Plugin
     */
    private $_plugin;

    /**
     * Sets up initial instance properties
     *
     * @param \Spocket\Plugin $plugin This plugin's instance.
     *
     * @return void
     */
    public function __construct( \Spocket\Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Runs the class.
     *
     * @return void
     */
    public function run()
    {
        $CommonSpocket = new \Spocket\Common\Spocket($this->_plugin);
        $CommonSpocket->run();
    }
}
