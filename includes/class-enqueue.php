<?php

/**
 * Plugin Name: Seon Fraud
 * Plugin URI: http://seon.io/
 * Description: Exstension fraud checks.
 * Version: 1.0.0
 * Author: SEON
 * Author URI: http://seon.io/
 * Text Domain: seon
 * Domain Path: /languages
 *
 * Copyright: © 2009-2015 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('SEON_Enqueue')) {

    class SEON_Enqueue {

        public function __construct() {

            add_filter('admin_head', array(&$this, 'seon_include_css'));
            add_action('admin_enqueue_scripts', array(&$this, 'seon_include_js'));

            //add_action('wp_loaded', array(&$this, 'seon_session_start'), 1);
            //add_action('wp_logout', array(&$this, 'seon_session_distroy'), 1);
            //add_action('wp_login', array(&$this, 'seon_session_start'), 1);
        }

        public static function seon_include_css() {

            wp_enqueue_style('awesome_font', SEON_PLUGIN . '/assets/css/font-awesome.min.css');
            wp_enqueue_style('google-font', 'https://fonts.googleapis.com/css?family=Cabin:400,500,600,700');

            wp_enqueue_style('admin-settings-styles', SEON_PLUGIN . '/assets/css/settings.css');
        }

        public static function seon_include_js() {

            $seon_settings_agent = ( get_option('seon_activate_agent') ? get_option('seon_activate_agent') : get_site_option('seon_activate_agent') );
            if ($seon_settings_agent) {
                //wp_enqueue_script('jquery');
                //wp_enqueue_script('seon_agent', 'https://cdn.seon.io/v1.0/js/agent.js', array('jquery'), '1.0.0', true);
                //wp_add_inline_script('seon_agent_session', 'start("' . $_SESSION['seon_agent_session'] . '");');
            }
            wp_enqueue_script('seon_resizer', SEON_PLUGIN . '/assets/js/iframeResizer.min.js', array(), '1.0.0', true);
            wp_enqueue_script('seon_js', SEON_PLUGIN . '/assets/js/scripts.js', array('jquery'), '1.0.0', true);
            $cd_admin_js_vars = array('ajax_url' => admin_url('admin-ajax.php'));
            wp_localize_script('seon_js', 'CD_VARS', $cd_admin_js_vars);
        }

        public function seon_session_start() {
            if (!session_id()) {
                session_start();

                $random_number = $this->seon_create_session();
                $_SESSION['seon_agent_session'] = ( $_SESSION['seon_agent_session'] == $random_number ? $this->seon_create_session() : $random_number );
            }
        }

        public function seon_session_distroy() {
            session_destroy();
        }

        public function seon_create_session() {
            return mt_rand(100000, 999999);
        }

    }

}
$seon_enqueue = new SEON_Enqueue();
