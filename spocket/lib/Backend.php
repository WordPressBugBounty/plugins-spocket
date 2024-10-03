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
 * Responsible for setting up backend functionality
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Backend
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
        $BackendAssets = new \Spocket\Backend\Assets($this->_plugin);
        $BackendAssets->run();
        $BackendScreen = new \Spocket\Backend\Screen($this->_plugin);
        $BackendScreen->run();
    }
}
