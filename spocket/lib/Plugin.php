<?php

/**
 * File for Requirements class
 * php version 
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
namespace Spocket;

/**
 * The core plugin class
 *
 * @category Category
 * @package  Spocket
 * @author   Display name <jonathan@spocket.co>
 * @license  Copyright https://en.wikipedia.org/wiki/Copyright
 * @link     no link
 */
class Plugin
{

    /**
     * The absolute url to the plugin folder.
     *
     * @var string
     */
    protected $url;

    /**
     * The absolute path to the plugin folder.
     *
     * @var string
     */
    protected $path;

    /**
     * The relative path with the index.php to the plugin folder.
     *
     * @var string
     */
    protected $pluginBaseName;

    /**
     * Plugin version.
     *
     * @var string
     */
    protected $version = '';

    /**
     * 1000
     * Sets up initial instance properties
     *
     * @return void
     */
    public function __construct()
    {
        $this->url            = \plugin_dir_url(dirname(__FILE__));
        $this->path           = \plugin_dir_path(dirname(__FILE__));
        $this->pluginBaseName = \plugin_basename(SPOCKET_MAKE_PATHS_RELATIVE_FILE);
    }

    /**
     * Retrieve the url of the plugin
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Retrieve the path of the plugin
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the plugin_basename
     *
     * @return string
     */
    public function getPluginBaseName()
    {
        return $this->pluginBaseName;
    }

    /**
     * Retrieve the plugin version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Retrieve the absolute url for the plugin assets folder
     *
     * @return string
     */
    public function getAssetsURL()
    {
        if (\getenv('ASSETS_URL') === false) {
            return $this->getURL() . 'dist/';
        }

        return \getenv('ASSETS_URL');
    }

    /**
     * Retrieve the absolute url for the plugin assets src folder
     *
     * @return string
     */
    public function getAssetsSrcURL()
    {
        return $this->getURL() . 'src/';
    }

    /**
     * Retrieve the absolute url for the plugin assets src folder
     *
     * @return string
     */
    public function getApiURL()
    {
        $lUrl = ( \getenv('API_URL') === false ) ?
        'https://newapi.spocket.co' :
        \getenv('API_URL');

        return $lUrl;
    }


    /**
     * Runs the initial setup of the plugin, including assets and hooks.
     *
     * @return void
     */
    public function run()
    {
        if (wp_doing_ajax()) {
            $AJAX = new \Spocket\AJAX($this);
            $AJAX->run();
        } elseif (is_admin()) {
            $Backend = new \Spocket\Backend($this);
            $Backend->run();
        }

        $ActivationCheck = new \Spocket\ActivationCheck\Requirements($this);
        $ActivationCheck->run();

        $conditionResult = in_array(
            'woocommerce/woocommerce.php',
            apply_filters(
                'activate_plugins',
                get_option('active_plugins')
            )
        );

        if ($conditionResult) {
            $CurrencyUpdate = new \Spocket\Webhook\CurrencyUpdate(
                new Webhook\Data()
            );
            $CurrencyUpdate->run();
        }

        $this->_addCurrencyUpdateCustomTopic();

        $Common = new \Spocket\Common($this);
        $Common->run();
    }

    /**
     * AddProductCategoryUpdateCustomTopic
     *
     * @return void
     */
    private function _addProductCategoryUpdateCustomTopic()
    {
        $name             = 'Product Category Update';
        $topic            = 'action.created_product_cat';
        $deliveryEndpoint = 'webhooks/woocommerces/categories/update';

        $AddCategoryCustomTopic = new \Spocket\Webhook\AddCustomTopic(
            $name,
            $topic,
            $deliveryEndpoint
        );
        $AddCategoryCustomTopic->run();
    }

    /**
     * AddCurrencyUpdateCustomTopic
     *
     * @return void
     */
    private function _addCurrencyUpdateCustomTopic()
    {
        $name             = 'Currency Update';
        $topic            = 'action.woocommerce_settings_saved';
        $deliveryEndpoint = 'webhooks/woocommerces/shop/update';

        $AddCategoryCustomTopic = new \Spocket\Webhook\AddCustomTopic(
            $name,
            $topic,
            $deliveryEndpoint
        );
        $AddCategoryCustomTopic->run();
    }
}
