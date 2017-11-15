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

if (!class_exists('SEON_Orders')) {

    class SEON_Orders {

        /**
         * Construct
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function __construct() {
            add_filter('manage_edit-shop_order_columns', array(&$this, 'seon_shop_order_column'));
            add_action('manage_shop_order_posts_custom_column', array(&$this, 'seon_shop_order_values'), 2);
            add_action('admin_print_styles', array(&$this, 'seon_add_order_column_style'), 10, 2);
            add_action('manage_edit-shop_order_sortable_columns', array(&$this, 'seon_shop_order_sorting'));
            add_action('add_meta_boxes', array(&$this, 'seon_shop_order_metabox'));
            add_action('woocommerce_admin_order_data_after_order_details', array(&$this, 'custom_shop_order_detailes'), 10, 1);
        }

        /**
         * Order style
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function seon_add_order_column_style() {
            $css = '.widefat .column-order_date, .widefat .column-order_profit { width: 9%; }';
            wp_add_inline_style('woocommerce_admin_styles', $css);
        }

        /**
         * Order column
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function seon_shop_order_column($columns) {
            $seon_columns = (is_array($columns)) ? $columns : array();
            unset($seon_columns['order_actions']);

            //$seon_columns['order_seon_score'] = __('Seon Score', 'seon');
            $seon_columns['order_seon_status'] = __('Seon State', 'seon');

            $seon_columns['order_actions'] = $columns['order_actions'];
            return $seon_columns;
        }

        /**
         * Order column
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function seon_shop_order_values($column) {
            global $post;
            $data = get_post_meta($post->ID);

            //if ($column == 'order_seon_score') {
                //echo (isset($data['_order_seon_score']) ? $data['_order_seon_score'][0] : '');
            //}
            if ($column == 'order_seon_status') {             
                echo (isset($data['_order_seon_status']) ? '<span class="' . strtolower($data['_order_seon_status'][0]) . '"></span> ' . $data['_order_seon_status'][0] : '');
            }
        }

        /**
         * Order column sorting
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function seon_shop_order_sorting($columns) {
            $custom = array(
                //'order_seon_score' => '_order_seon_score',
                'order_seon_status' => '_order_seon_status'
            );
            return wp_parse_args($custom, $columns);
        }

        /**
         * Order meta boxes
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function seon_meta_box_markup($post) {
            $html = '';
            if ($post && $post->ID && $seon_id = get_post_meta($post->ID, '_order_seon_id', true)) {
                $html .= '<iframe id="seon-transaction" src="https://admin.seondev.space/transactions/' . $seon_id . '/?embed=1" width="100%" height="100%" style="width:100%"></iframe>';                        
            }
            echo $html;
        }

        /**
         * Order meta box box content
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function seon_shop_order_metabox() {
            add_meta_box("seon-frame-meta-box", __('SEON Transaction Details', 'seon'), array($this, 'seon_meta_box_markup'), "shop_order", "advanced", "", null);
        }

        /**
         * Order extra informations
         * 
         * @since 1.0.0
         * @access public
         * @params $columns
         * 
         */
        public function custom_shop_order_detailes($order) {
            $html = '<div class="order_data_column">';
            $html .= '<h4>' . __('SEON', 'seon') . '</h4>';
            //$html .= '<p>' . __('Score', 'seon') . ':<br><strong>' . get_post_meta($order->id, '_order_seon_score', true) . '</strong></p>';
            $html .= '<p>' . __('State', 'seon') . ':<br><strong>' . get_post_meta($order->id, '_order_seon_status', true) . '</strong></p>';
            $html .= '</div>';

            echo $html;
        }

    }

}
$seon_orders = new SEON_Orders();
