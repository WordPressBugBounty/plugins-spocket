<?php

/**
 * File for Requirements class
 * php version 7.4
 *
 * @category Category
 * @package  Spocket\Common
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket\Common;

/**
 * Handles Spocket related functionality
 *
 * @category Category
 * @package  Spocket\Common
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Spocket {


	/**
	 * The plugin instance.
	 *
	 * @var Plugin
	 */
	private $_plugin;

	private static $_object = null;
	/**
	 * Get Spocket Consumer Secret
	 *
	 * @return void
	 */
	private function _getSpocketConsumerSecret() {
		global $wpdb;

		$wpUser    = wp_get_current_user();
		$tableName = $wpdb->prefix . 'woocommerce_api_keys';

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT key_id, consumer_secret
                FROM `%1$s`
                WHERE description LIKE %s
                AND user_id = "%3$s"',
				$tableName,
				// esc_like() escapes % as a literal. Escaping the whole
				// '%spocket%' produced a pattern that only matched a key whose
				// description was literally "%spocket%", so no key was ever
				// found and disconnect never reached Spocket.
				'%' . $wpdb->esc_like('spocket') . '%',
				$wpUser->id
			)
		);
	}

	/**
	 * Product Category
	 *
	 * @param $category_id Category Id
	 *
	 * @return array
	 */
	private function _getProductCategory( $category_id ) {
		global $wpdb;

		$tableName = $wpdb->prefix . 'terms';
		return $wpdb->get_row(
			$wpdb->prepare(
				'select * from `%1$s` where term_id = "%2$s"',
				$tableName,
				$category_id
			)
		);
	}

	/**
	 * Sets up initial instance properties
	 *
	 * @param \Spocket\Plugin $plugin This plugin's instance.
	 *
	 * @return void
	 */
	public function __construct( \Spocket\Plugin $plugin ) {
		self::$_object = $this;
		$this->_plugin = $plugin;
	}

	/**
	 * Checks whether Spocket is integrated with the store.
	 *
	 * @return array Spocket status results
	 */
	public static function getSpocketStatus() {
		return array(
		'connected' => get_option('spocket_connected', false),
		);
	}

	/**
	 * Domains, with their subdomains, that Spocket owns: production and staging.
	 *
	 * @var string[]
	 */
	private static $_spocketDomains = array( 'spocket.co', 'staging-spocket.co' );

	/**
	 * Whether a URL points at a Spocket host.
	 *
	 * Owner details and the store authorization key are only ever added to
	 * requests for these hosts, and auth tokens only ever accepted from them.
	 * There is deliberately no environment switch around this check: an
	 * IS_DEV bypass here used to forward that data to whatever host a request
	 * went to, and save any auth_token that host answered with. Staging is a
	 * fixed Spocket-owned domain in the list instead.
	 *
	 * @param string $url url
	 *
	 * @return boolean
	 */
	private static function _isSpocketUrl( $url ) {
		$urlHost = parse_url($url, PHP_URL_HOST);

		if (!is_string($urlHost) || '' === $urlHost) {
			return false;
		}

		$urlHost = strtolower($urlHost);

		foreach (self::$_spocketDomains as $domain) {
			$suffix = '.' . $domain;
			if ($domain === $urlHost
				|| substr($urlHost, -strlen($suffix)) === $suffix
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filters request ags and adds the data that Spocket expects.
	 *
	 * @param array  $args http args
	 * @param string $url  url
	 *
	 * @return array modified http args
	 */
	public static function fillInSpocketData( $args, $url ) {
		if (!is_user_logged_in()) {
			return $args;
		}

		if (!self::_isSpocketUrl($url)) {
			return $args;
		}

		$body = json_decode($args['body'], true);

		if (!is_array($body)) {
			return $args;
		}

		if (!isset($body['consumer_key'])) {
			return $args;
		}

		// We are sending API keys to spocket, set our option here
		$currentUser = wp_get_current_user();

		$body['user_email'] = $currentUser->get('user_email');
		$body['user_name']  = $currentUser->get('display_name');

		$body['store_authorization_key'] = get_option(
			'spocket_store_authorization_key'
		);

		$customer = new \WC_Customer($currentUser->ID);

		$body['user_address'] = sprintf(
			'%s, %s %s, %s',
			$customer->get_billing_address_1(),
			$customer->get_billing_postcode(),
			$customer->get_billing_city(),
			$customer->get_billing_country()
		);

		if (empty(trim(str_replace(',', '', $body['user_address'])))) {
			$body['user_address'] = '';
		}

		$body['store_name'] = get_option('blogname');
		$body['store_url']  = get_site_url();
		$args['body']       = wp_json_encode($body);

		return apply_filters('spocket_http_args', $args, $url);
	}

	/**
	 * Intercepts response and stores an auth token.
	 *
	 * @param array  $response http request response
	 * @param array  $r        http args
	 * @param string $url      url
	 *
	 * @return array untouched http request response
	 */
	public static function maybeSaveSpocketAuthToken( $response, $r, $url ) {
		if (!self::_isSpocketUrl($url)) {
			return $response;
		}

		if (is_wp_error($response)
			|| intval($response['response']['code']) !== 200
		) {
			return $response;
		}

		$body = json_decode($response['body'], true);

		if (!is_array($body)) {
			return $response;
		}

		if (!isset($body['auth_token'])) {
			return $response;
		}

		update_option('spocket_auth_token', $body['auth_token'], false);
		update_option('spocket_connected', true, false);
		return $response;
	}

	/**
	 * Disconnects Spocket from the store.
	 *
	 * Marks the store disconnected locally only once Spocket has confirmed the
	 * integration was removed on its side. Flipping spocket_connected before
	 * that confirmation let WordPress report "disconnected" even when the
	 * backend call failed or never ran, leaving the store connected on
	 * Spocket's side with nothing to tell the merchant why.
	 *
	 * @return boolean True once both sides agree the store is disconnected.
	 */
	public static function disconnectSpocket() {
		global $wpdb;

		$spocketKey     = self::$_object->_getSpocketConsumerSecret();
		$consumerSecret = empty($spocketKey) ? '' : $spocketKey->consumer_secret;
		$userId         = get_option('spocket_user_id', '');

		if (empty($consumerSecret) && empty($userId)) {
			// Nothing identifies this store to Spocket, so a connection was
			// never completed and there is nothing for the backend to remove.
			update_option('spocket_connected', false, false);
			return true;
		}

		// Send whichever identifiers exist. The backend matches on user_id
		// first (it never rotates) and falls back to consumer_secret, so a
		// missing local key must never short-circuit this call: skipping it
		// is exactly how WordPress showed "disconnected" while Spocket still
		// had the store active.
		$deleteIntegrationResponse = wp_remote_request(
			sprintf(
				self::$_object->_plugin->getApiURL() .
				'/woocommerce/integration?user_id=%s&consumer_secret=%s',
				rawurlencode($userId),
				rawurlencode($consumerSecret)
			),
			array(
			'method' => 'DELETE',
			)
		);

		if (is_wp_error($deleteIntegrationResponse)
			|| intval(wp_remote_retrieve_response_code($deleteIntegrationResponse)) !== 200
		) {
			// Spocket never confirmed the disconnect. Keep the local key and
			// the connected flag as-is so the merchant can retry, instead of
			// silently drifting out of sync with Spocket's records.
			return false;
		}

		if (!empty($spocketKey)) {
			// Remove only the key that was just disconnected, not every key on
			// the site whose description happens to mention Spocket.
			$wpdb->delete(
				$wpdb->prefix . 'woocommerce_api_keys',
				array( 'key_id' => (int) $spocketKey->key_id ),
				array( '%d' )
			);
		}
		update_option('spocket_connected', false, false);

		return true;
	}

	/**
	 * Saves the user id to the database upon successful Spocket connection.
	 *
	 * @return void
	 */
	public function maybeSaveUserId() {
		if (!isset($_GET['success']) || !isset($_GET['user_id'])) {
			return;
		}

		if (intval($_GET['success']) !== 1) {
			return;
		}

		$savedUserId = get_option('spocket_user_id', '');
		$newUserId   = sanitize_text_field($_GET['user_id']);

		if ($savedUserId === $newUserId) {
			return;
		}

		update_option('spocket_user_id', $newUserId, false);
	}

	/**
	 * Saves spocket_shop_url if the option is empty on wp_options table.
	 *
	 * @return void
	 */
	public static function saveShopUrl() {
		$shopUrl  = get_option('spocket_shop_url', '');
		$site_url = get_site_url();
		//if ('' === $shopUrl) {
		update_option('spocket_shop_url', $site_url, false);
		//}
	}


	/**
	 * Finishes the Direct Signup process
	 *
	 * @param $storeAuthorizationKey Store authorization Key
	 *
	 * @return void
	 */
	public static function finishDirectSignup( $storeAuthorizationKey ) {
		/**
		* Authorization key generated when the user signs up through
		* direct signup process.
		* PS. That is not our Spocket Auth Token key.
		*/
		if ('' !== $storeAuthorizationKey) {
			update_option(
				'spocket_store_authorization_key',
				$storeAuthorizationKey,
				false
			);
		}

		return true;
	}

	/**
	 * Add the spocket_shop_url to webhook payload.
	 *
	 * @param $payload Payload
	 *
	 * @return $payload
	 */
	public function addAdditionalParamsToWebhookPayload( $payload ) {
		$shopUrl                                = get_option('spocket_shop_url', '');
		$payload['spocket_shop_url']            = $shopUrl;
		$payload['spocket_plugin_version']      = $this->_plugin->getVersion();
		$payload['spocket_integrated_store_id'] = get_option('spocket_user_id', '');

		return $payload;
	}

	/**
	 * Just a over write for the Webhook disable rule
	 * to explain that, the webhooks have a limit of 5 failed requests,
	 * after that the webhooks will be disabled on its own.
	 *
	 * @return integer
	 */
	public function overruleWebhookDisableRule() {
		return 999999999999;
	}

	/**
	 * Action to create a collection on Spocket when it's created on WooCommerce
	 *
	 * @param $category_id Category Id
	 *
	 * @return void
	 */
	public function createCategoryOnSpocket( $category_id ) {
		$category         = $this->_getProductCategory($category_id);
		$spocketAuthToken = get_option('spocket_auth_token');

		$requestBody = array(
		'collection' => array(
		'uid'    => $category_id,
		'title'  => $category->name,
		'handle' => $category->slug,
		),
		);

		if (!empty($spocketAuthToken)) {
			wp_safe_remote_post(
				$this->_plugin->getApiURL() . '/dropshippers/woocommerces/collections',
				array(
				'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $spocketAuthToken,
				),
				'body' => json_encode($requestBody),
				)
			);
		}
	}

	/**
	 * DeleteCategoryOnSpocket
	 *
	 * @param $category_id Category Id
	 *
	 * @return void
	 */
	public function deleteCategoryOnSpocket( $category_id ) {
		$category         = $this->_getProductCategory($category_id);
		$spocketAuthToken = get_option('spocket_auth_token');

		if (!empty($spocketAuthToken)) {
			wp_remote_request(
				$this->_plugin->getApiURL() . '/dropshippers/woocommerces/collections',
				array(
				'method' => 'DELETE',
				'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $spocketAuthToken,
				),
				'body' => json_encode(array( 'uid' => $category_id )),
				)
			);
		}
	}

	/**
	 * Registers filters and actions.
	 *
	 * @return void
	 */
	public function run() {
		self::saveShopUrl(); // save spocket_shop_url

		add_filter('http_request_args', array( $this, 'fillInSpocketData' ), 10, 2);
		add_filter(
			'http_response',
			array( $this, 'maybeSaveSpocketAuthToken' ),
			10,
			3
		);
		add_action('spocket_before_interface', array( $this, 'maybeSaveUserId' ));

		// change default woocommerce webhook requests
		add_filter(
			'woocommerce_webhook_payload',
			array( $this, 'addAdditionalParamsToWebhookPayload' ),
			10,
			3
		);

		// overrule woocommerce disable webhooks rule
		add_filter(
			'woocommerce_max_webhook_delivery_failures',
			array( $this, 'overruleWebhookDisableRule' )
		);

		// create category on Spocket
		add_action('create_product_cat', array( $this, 'createCategoryOnSpocket' ));

		// delete category on Spocket
		add_action('delete_product_cat', array( $this, 'deleteCategoryOnSpocket' ));
	}
}
