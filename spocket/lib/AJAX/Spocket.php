<?php

/**
 * File for Spocket class
 * php version 7.4
 *
 * @category Category
 * @package  Spocket\AJAX
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket\AJAX;

use \Spocket\Common\Spocket as CommonSpocket;

/**
 * Handles AJAX requests related to Spocket
 *
 * @category Category
 * @package  Spocket\AJAX
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Spocket
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
    public function __construct(\Spocket\Plugin $plugin)
    {
        $this->_plugin = $plugin;

    }//end __construct()


    /**
     * Gets requirements status.
     *
     * @return void
     */
    public function getSpocketStatus()
    {
        check_ajax_referer('spocket-get-status-nonce', 'nonce');
        $spocketStatus = CommonSpocket::getSpocketStatus();
        wp_send_json_success(
            array(
            'spocketStatus' => $spocketStatus,
            )
        );
    }//end getSpocketStatus()


    /**
     * Disconnects the store from Spocket.
     *
     * @return void
     */
    public function disconnectSpocket()
    {
        check_ajax_referer('spocket-disconnect-nonce', 'nonce');
        $disconnected = CommonSpocket::disconnectSpocket();
        wp_send_json_success(
            array(
            'disconnected' => $disconnected,
            )
        );
    }//end disconnectSpocket()


    /**
     * Save shop url.
     *
     * @return void
     */
    public function saveShopUrl()
    {
        check_ajax_referer('spocket-save-shop-url-nonce', 'nonce');

        $spocketStatus = CommonSpocket::saveShopUrl();

    }//end saveShopUrl()


    /**
     * Finishes the direct signup process.
     *
     * @return void
     */
    public function finishDirectSignup()
    {
        check_ajax_referer('spocket-direct-signup-nonce', 'nonce');

        if (isset($_POST['storeAuthorizationKey'])) {
            $storeAuthorizationKey = sanitize_text_field(
                $_POST['storeAuthorizationKey']
            );
            $isStoreAuthorizationKeyCreated = CommonSpocket::finishDirectSignup(
                $storeAuthorizationKey
            );

            if ($isStoreAuthorizationKeyCreated) {
                return wp_send_json_success(['direct_signup_status' => 'created']);
            }
        }

        wp_send_json_error(
            [
            'direct_signup_status' => 'failed',
            'message' => 'Please provide your store authorization key'
            ]
        );

    }//end finishDirectSignup()


    /**
     * Some comment.
     *
     * @return void
     */
    public function removeStoreAuthorizationKeyFromRequest()
    {
        check_ajax_referer('spocket-remove-store-authorization-key-nonce', 'nonce');
        update_option('spocket_store_authorization_key', '', false);

        if (get_option('spocket_store_authorization_key') === '') {
            return wp_send_json_success(['status' => 'removed']);
        }

    }//end removeStoreAuthorizationKeyFromRequest()


    /**
     * Registeres and enqueues assets.
     *
     * @return void
     */
    public function run()
    {
        add_action('wp_ajax_spocket_status', [$this, 'getSpocketStatus']);
        add_action('wp_ajax_spocket_disconnect', [$this, 'disconnectSpocket']);
        add_action('wp_ajax_spocket_save_shop_url', [$this, 'saveShopUrl']);
        add_action('wp_ajax_spocket_direct_signup', [$this, 'finishDirectSignup']);
        add_action(
            'wp_ajax_spocket_remove_store_authorization_key_from_request',
            [$this, 'removeStoreAuthorizationKeyFromRequest']
        );

    }//end run()


}//end class
