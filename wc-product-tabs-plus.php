<?php
/**
 * Plugin Name: WC Product Tabs Plus
 * Plugin URI: https://wooninjas.com
 * Description: Advance tab management for WooCommerce tabs on single product page
 * Version: 1.0.3
 * Author: WooNinjas
 * Author URI: https://wooninjas.com
 * Text Domain: wptp
 * License: GPLv2 or later
 */

namespace WPTP;

if (!defined("ABSPATH")) exit;

// Directory
define('WPTP\DIR', plugin_dir_path(__FILE__));
define('WPTP\DIR_FILE', DIR . basename(__FILE__));
define('WPTP\INCLUDES_DIR', trailingslashit(DIR . 'includes'));
define('WPTP\TEMPLATES', trailingslashit(DIR . 'templates'));

// URLS
define('WPTP\URL', trailingslashit(plugins_url('', __FILE__)));
define('WPTP\ASSETS_URL', trailingslashit(URL . 'assets'));

// Load WC dependency class
if (!class_exists('WC_Dependencies')) {
    require_once DIR . 'woo-includes/class-wc-dependencies.php';
}

// Check if WooCommerce active
if (!\WC_Dependencies::woocommerce_active_check()) {
    return;
}

//Loading files
require_once INCLUDES_DIR . 'class-global-tabs.php';
require_once INCLUDES_DIR . 'class-product-data.php';
require_once INCLUDES_DIR . 'class-tabs.php';
require_once INCLUDES_DIR . 'functions.php';

/**
 * Class Main for plugin initiation
 *
 * @since 1.0
 */
final class Main
{
    public static $version = '1.0.2';

    // Main instance
    protected static $_instance = null;

    protected function __construct() {
        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));
        // Upgrade
        add_action('plugins_loaded', array($this, 'upgrade'));

        GlobalTabs::init();
        ProductData::init();

        // Adding settings tab
        add_filter('plugin_action_links_' . plugin_basename(DIR_FILE), function($links) {
            return array_merge($links, array(
                sprintf(
                    '<a href="%s">Global tabs</a>',
                    admin_url('edit.php?post_type='.GlobalTabs::get_posttype())
                ),
            ));
        });

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        // Render product tabs on Single product
        add_filter('woocommerce_product_tabs', array($this, 'render_frontend'));
    }

    /**
     * @return $this
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Activation function hook
     *
     * @return void
     */
    public static function activation() {
        if (!current_user_can('activate_plugins'))
            return;

        update_option('wptp_version', self::$version);
    }

    /**
     * Deactivation function hook
     * No used in this plugin
     *
     * @return void
     */
    public static function deactivation() {}

    public static function upgrade() {
        if (get_option('wptp_version') != self::$version) {
            wptp_upgrade();
        }
    }

    /**
     * Enqueue scripts on admin
     */
    public static function admin_enqueue_scripts() {
        global $post_type;
        $screens = array('product');

        if (!in_array($post_type, $screens)) return;

        wp_enqueue_style('wptp-css', ASSETS_URL . 'css/wptp.css', array(), self::$version);

        $deps = array(
            'jquery',
            'jquery-ui-core',
            'backbone',
            'editor'
        );

        wp_enqueue_script('wptp-js', ASSETS_URL . 'js/wptp.js', $deps, self::$version, true);
    }

    /**
     * Render tabs on front
     * @param $tabs array
     * @return mixed
     */
    public static function render_frontend($tabs) {
        $product_tabs = wptp_get_all_tabs();
        $i = 1;
        foreach ($product_tabs as $product_tab) {
            if ($product_tab->hide || empty($product_tab->title)) continue;

            $tab_properties = array(
                'title' 	=> $product_tab->title,
                'priority' 	=> 50 + $i,
                'callback' 	=> function() use ($product_tab) {
	                /**
	                 * Tab specific
	                 * @since 1.0.1
	                 */
	                do_action( "wptp_tab_{$product_tab->fieldID}", $product_tab );

	                /**
	                 * Global tab
	                 * @since 1.0.1
	                 */
	                do_action( "wptp_tab", $product_tab );

	                /**
	                 * Global tab object
	                 * @since 1.0.3
	                 */

	                $product_tab = apply_filters( 'wptp_tab_object', $product_tab );

	                if ( isset( $product_tab->title ) ) {
	                	echo "<h2>{$product_tab->title}</h2>";
	                }

	                if ( isset( $product_tab->content ) ) {
		                echo $product_tab->content;
	                }
                }
            );
            $tabs[$product_tab->fieldID] = $tab_properties;
            $i++;
        }

        return $tabs;
    }
}

/**
 * Main instance
 *
 * @return Main
 */
function WPTP() {
    return Main::instance();
}

// Bootstrap
WPTP();
