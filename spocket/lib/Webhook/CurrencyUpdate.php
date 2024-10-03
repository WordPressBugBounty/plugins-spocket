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
 * CurrencyUpdate
 *
 * @category Category
 * @package  Spocket\Webhook
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class CurrencyUpdate
{

    private $_webhook;

    private static $_object = null;

    /**
     * Sets up initial instance properties
     *
     * @param Data $webhook A Webhook object.
     *
     * @return void
     */
    public function __construct( Data $webhook)
    {
        self::$_object  = $this;
        $this->_webhook = new \WC_Webhook(
            $webhook->findWebhookByTopic('action.woocommerce_settings_saved')
        );
    }

    /**
     * AddCurrencyUpdateTopicHook will add a new webhook topic hook.
     *
     * @param array $topic_hooks Esxisting topic hooks.
     *
     * @return array
     */
    public static function addCurrencyUpdateTopicHook( $topic_hooks)
    {
        $new_hooks = array(
        'action.woocommerce_settings_saved' => array(
        'woocommerce_currency_updated',
        ),
        );

        return array_merge($topic_hooks, $new_hooks);
    }

    /**
     * CurrencyUpdateEvent
     *
     * @param array $topic_events topic events.
     *
     * @return array
     */
    public static function currencyUpdateEvent( $topic_events)
    {
        $new_events = array(
        'woocommerce_settings_saved',
        );

        return array_merge($topic_events, $new_events);
    }


    /**
     * AddCurrencyUpdateTopicToMenu
     *
     * @param array $topics topics.
     *
     * @return array
     */
    public static function addCurrencyUpdateTopicToMenu( $topics)
    {
        $new_topics = array(
        'action.woocommerce_settings_saved' => __('Currency Update', 'woocommerce'),
        );

        return array_merge($topics, $new_topics);
    }

    /**
     * AddCurrencyToWebhookPayload
     *
     * @param array $payload payload.
     *
     * @return object
     */
    public static function addCurrencyToWebhookPayload( $payload)
    {
        if (array_key_exists('action', $payload)
            && 'woocommerce_settings_saved' == $payload['action']
        ) {
            $payload['id']       = self::$_object->webhook->get_new_delivery_id();
            $payload['currency'] = get_woocommerce_currency();
        }

        return $payload;
    }

    /**
     * Run
     *
     * @return void
     */
    public function run()
    {
        add_filter(
            'woocommerce_webhook_topic_hooks',
            array($this, 'addCurrencyUpdateTopicHook'),
            10,
            1
        );
        add_filter(
            'woocommerce_valid_webhook_events',
            array($this, 'currencyUpdateEvent'),
            10,
            1
        );
        add_filter(
            'woocommerce_webhook_topics',
            array($this, 'addCurrencyUpdateTopicToMenu'),
            10,
            1
        );
        add_filter(
            'woocommerce_webhook_payload',
            array($this, 'addCurrencyToWebhookPayload'),
            10,
            2
        );
    }
}
