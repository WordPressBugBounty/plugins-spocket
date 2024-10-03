<?php

/**
 * File for Requirements class
 * php version 7.4
 *
 * @category Category
 * @package  Spocket\Backend
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket\Backend;

/**
 * Displays an admin interface for the plugin
 *
 * @category Category
 * @package  Spocket\Backend
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Screen
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
     * Registers interface, to be displayed as a sub-menu of the Settings menu
     * item.
     *
     * @return void
     */
    public function addMenuPage()
    {
        add_menu_page(
            esc_html__('Spocket', 'spocket'),
            esc_html__('Spocket', 'spocket'),
            'manage_woocommerce',
            'spocket',
            array($this, 'displayInterface'),
            "{$this->_plugin->getAssetsURL()}images/white-logo.png",
            57
        );
    }

    /**
     * Displays an interface.
     *
     * @return void
     */
    public function displayInterface()
    {
        do_action('spocket_before_interface', $this);
        echo '<div class="wrap js-spocket-admin-interface">';
        esc_html_e('Loading, please wait...', 'spocket');
        echo '</div>';
        do_action('spocket_after_interface', $this);
    }

    /**
     * Registers filters and actions.
     *
     * @return void
     */
    public function run()
    {
        add_action('admin_menu', array($this, 'addMenuPage'));
    }
}
