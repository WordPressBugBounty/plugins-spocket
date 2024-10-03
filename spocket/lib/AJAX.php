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
 * Responsible for setting up AJAX functionality
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class AJAX
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
        $AJAXRequirements = new \Spocket\AJAX\Requirements($this->_plugin);
        $AJAXRequirements->run();
        $AJAXSpocket = new \Spocket\AJAX\Spocket($this->_plugin);
        $AJAXSpocket->run();
    }
}
