<?php

/**
 * File for Requirements class
 * php version 7.4
 *
 * @category Category
 * @package  Spocket\Webhook
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket\Webhook;

/**
 * AddCustomTopic
 *
 * @category Category
 * @package  Spocket\Webhook
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class AddCustomTopic
{

    private $_topic;
    private $_name;
    private $_deliveryEndpoint;
    private $_apiUrl;

    /**
     * Get Secret
     *
     * @return array
     */
    private function _getSecret()
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'select secret from `%1$s` where status = "active" limit 1',
                $this->table
            )
        );
    }

    /**
     * Query Topic
     *
     * @return array
     */
    private function _queryTopic()
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                'select secret from `%1$s` '.
                'where status = "active" and topic = "%2$s"',
                $this->table,
                $this->_topic
            )
        );
    }

    /**
     * Sets up initial instance properties
     *
     * @param $name             Name.
     * @param $topic            Topic.
     * @param $deliveryEndpoint EndPoint.
     *
     * @return void
     */
    public function __construct( $name, $topic, $deliveryEndpoint)
    {
        global $wpdb;
        $lApiUrl = !empty(getenv('API_URL')) ?
        getenv('API_URL') :
        'https://newapi.spocket.co/';

        $this->_name             = $name;
        $this->_topic            = $topic;
        $this->_deliveryEndpoint = $deliveryEndpoint;
        $this->_apiUrl           = $lApiUrl;
        $this->table            = "{$wpdb->prefix}wc_webhooks";
    }

    /**
     * Add
     *
     * @return void
     */
    public function add()
    {
        if (!empty($this->_getSecret())) {
            $secret      = $this->_getSecret()->secret;
            $query_topic = $this->_queryTopic();

            if (!empty($secret) && empty($query_topic)) {
                $webhook = new \WC_Webhook();
                $webhook->set_props(
                    array(
                    'secret'           => $secret,
                    'status'           => 'active',
                    'api_version'      => 2,
                    'name'             => $this->_name,
                    'delivery_url'     => $this->_apiUrl .
                    '/' . $this->_deliveryEndpoint,
                    'topic'            => $this->_topic,
                    'user_id'          => get_current_user_id()
                    )
                );

                $webhook->save();
            }
        }
    }

    /**
     * Run
     *
     * @return void
     */
    public function run()
    {
        add_action('admin_init', array($this, 'add'));
    }
}
