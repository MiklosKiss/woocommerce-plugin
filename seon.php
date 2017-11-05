<?php

/**
 * Plugin Name: Seon Fraud
 * Plugin URI: http://seon.io/
 * Description: SEON API Fraud.
 * Version: 1.0.0
 * Author: SEON
 * Author URI: http://seon.io/
 * Text Domain: seon
 * Domain Path: /languages
 *
 * Copyright: © 2009-2017 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define('SEON_PLUGIN', plugins_url('', __FILE__));



if (!class_exists('SEON_FRAUD')) {

    class SEON_FRAUD {

        /** plugin version number */
        const VERSION = "1.0";

        /** plugin version name */
        const VERSION_OPTION_NAME = 'seon_version';

        /**
         * Construct
         * @since 1.0.0
         */
        public function __construct() {

            /** Load text domain */
            load_plugin_textdomain('seon', false, dirname(plugin_basename(__FILE__)) . '/i18n/languages/');
            $plugin = plugin_basename(__FILE__);

            /** Load init() */
            add_action('plugins_loaded', array(&$this, 'init'));
        }

        /**
         * Include classes
         * @since 1.0.0
         */
        public function init() {
            require_once( plugin_dir_path(__FILE__) . 'includes/class-enqueue.php' );
            require_once( plugin_dir_path(__FILE__) . 'includes/class-settings.php' );
            require_once( plugin_dir_path(__FILE__) . 'includes/class-data.php' );
            require_once( plugin_dir_path(__FILE__) . 'includes/class-orders.php' );
        }

    }

    $seon = new SEON_FRAUD();
}