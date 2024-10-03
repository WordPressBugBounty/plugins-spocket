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
 * Data
 *
 * @category Category
 * @package  Spocket\Webhook
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Data
{

    private $_table = 'wc_webhooks';

    /**
     * Return Webook
     *
     * @param $topic The Webhook Topic
     *
     * @return void
     */
    public function findWebhookByTopic( $topic)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . $this->_table;

        return $wpdb->get_row(
            $wpdb->prepare(
                'select * from `%1$s` where topic = "%2$s"',
                $tableName,
                $topic
            )
        );
    }
}
