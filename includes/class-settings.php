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

if (!class_exists('SEON_Settings')) {

    class SEON_Settings {

        private $seon_settings_fields = array(
            'seon_licence_key' => '',
            'seon_activate_plugin' => '',
            'seon_activate_agent' => ''
        );

        /**
         * Construct
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function __construct() {
            if (current_user_can('manage_options')) {
                add_action('network_admin_menu', array(&$this, 'seon_add_network_settings'));
                add_action('admin_menu', array(&$this, 'seon_add_site_settings'));
            }
        }

        /**
         * Add site settings menu
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function seon_add_site_settings() {
            if (is_admin() || is_super_admin())
                add_submenu_page('options-general.php', __('SEON API Settings', 'seon'), __('SEON', 'seon'), 'administrator', 'seon-site-settings', array(&$this, 'seon_site_settings'));
        }

        /**
         * Add network settings menu
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function seon_add_network_settings() {
            if (is_super_admin())
                add_submenu_page('settings.php', __('SEON API Settings', 'seon'), __('SEON', 'seon'), 'administrator', 'seon-network-settings', array(&$this, 'seon_admin_settings'));
        }

        /**
         * Add site settings
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function seon_site_settings() {
            $this->seon_settings();
        }

        /**
         * Add network settings
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        public function seon_admin_settings() {
            $this->seon_settings(true);
        }

        /**
         * Seon settings
         * 
         * @since 1.0.0
         * @access public
         * @params $site
         * 
         */
        public function seon_settings($site = false) {

            $seon_settings_fields = $this->seon_settings_fields;
            $checked = '';


            /* Verify the nonce before proceeding. */
            if (isset($_POST['seon_nonce']) && wp_verify_nonce($_POST['seon_nonce'], basename(__FILE__))) {

                foreach ($this->seon_settings_fields as $field => $val) {
                    if (isset($_POST[$field])) {
                        $seon_settings_fields[$field] = sanitize_html_class($_POST[$field]);
                    }
                }

                foreach ($seon_settings_fields as $key => $val) {
                    if (( $key && ( '' != $val || !empty($val) ))) {
                        /* Add/Update Options */
                        if ($site) {
                            update_site_option($key, $val);
                        } else {
                            update_option($key, $val);
                        }
                    } elseif (( $key && ( '' == $val || empty($val) ))) {
                        /* Delete Option If Empty */
                        if ($site) {
                            delete_site_option($key);
                        } else {
                            delete_option($key);
                        }
                    }
                }
            }
            echo $this->seon_settings_html($site);
        }

        /**
         * Seon settings content
         * 
         * @since 1.0.0
         * @access public
         * @params $site
         * 
         */
        public function seon_settings_html($site = false) {


            $seon_settings_fields = $this->seon_settings_fields;

            /* Setting Options */
            foreach ($seon_settings_fields as $field => $val) {
                if ($site) {
                    if (get_site_option($field, '')) {
                        $seon_settings_fields[$field] = get_site_option($field, '');
                    }
                } else {
                    if (get_option($field, '')) {
                        $seon_settings_fields[$field] = get_option($field, '');
                    }
                }
            }

            $html = '<div class="wrap">';
            $html = '<div class="seon-settings-container">';
            $html .= '<section class="seon-settings">';
            $html .= '<div class="seon-settings-content">';
            $html .= '<form method="POST">';
            $html .= wp_nonce_field(basename(__FILE__), 'seon_nonce', true, false);
            $seon_nonce = wp_create_nonce("seon_nonce");


            $html .= '<table class="form-table" border="0">';
            $html .= '<tr valign="top">';
            $html .= '<th scope="row" class="seon-title">';
            $html .= '<label for="num_elements">';
            $html .= __('SEON Fraud API', 'seon');
            $html .= '</label>';
            $html .= '</th>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<p class="seon-settings-heading">' . __('API Licence Key', 'seon') . '</p>';
            $html .= '<input type="text" class="regular-text" name="seon_licence_key" value="'.$seon_settings_fields['seon_licence_key'].'">';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<p class="seon-settings-heading">' . __('Javascript Agent', 'seon') . '</p>';
            $html .= '<input type="radio" '.( $seon_settings_fields['seon_activate_agent'] == 1 ? 'checked ' : '').'class="regular-radio first" name="seon_activate_agent" value="1"> <label for="">' . __('Enabled', 'seon') . '</label>';
            $html .= '<input type="radio" '.( $seon_settings_fields['seon_activate_agent'] == 0 ? 'checked ' : '').'class="regular-radio" name="seon_activate_agent" value="0"> <label for="">' . __('Disabled', 'seon') . '</label>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<p class="seon-settings-heading">' . __('Activated', 'seon') . '</p>';
            $html .= '<input type="radio" '.( $seon_settings_fields['seon_activate_plugin'] == 1 ? 'checked ' : '').'class="regular-radio first" name="seon_activate_plugin" value="1"> <label for="">' . __('Enabled', 'seon') . '</label>';
            $html .= '<input type="radio" '.( $seon_settings_fields['seon_activate_plugin'] == 0 ? 'checked ' : '').'class="regular-radio" name="seon_activate_plugin" value="0"> <label for="">' . __('Disabled', 'seon') . '</label>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '<span class="seon-settings-submit">';
            $html .= '<input type="submit" value="' . __('Save settings', 'seon') . '" class="button-primary"/>';
            $html .= '</span>';


            $html .= '</form>';
            $html .= '</div>';
            $html .= '</section>';
            $html .= '</div>';

            //$order = $this->get_order_details(351);
            //echo $order;
            return $html;
        }

        public function get_order_details($order_id) {

            // 1) Get the Order object
            $order = wc_get_order($order_id);

            // OUTPUT
            echo '<h3>RAW OUTPUT OF THE ORDER OBJECT: </h3>';
            echo '<pre>';
            print_r($order);
            echo '<br><br>';
            echo '<h3>THE ORDER OBJECT (Using the object syntax notation):</h3>';
            echo '$order->order_type: ' . $order->order_type . '<br>';
            echo '$order->id: ' . $order->id . '<br>';
            echo '<h4>THE POST OBJECT:</h4>';
            echo '$order->post->ID: ' . $order->post->ID . '<br>';
            echo '$order->post->post_author: ' . $order->post->post_author . '<br>';
            echo '$order->post->post_date: ' . $order->post->post_date . '<br>';
            echo '$order->post->post_date_gmt: ' . $order->post->post_date_gmt . '<br>';
            echo '$order->post->post_content: ' . $order->post->post_content . '<br>';
            echo '$order->post->post_title: ' . $order->post->post_title . '<br>';
            echo '$order->post->post_excerpt: ' . $order->post->post_excerpt . '<br>';
            echo '$order->post->post_status: ' . $order->post->post_status . '<br>';
            echo '$order->post->comment_status: ' . $order->post->comment_status . '<br>';
            echo '$order->post->ping_status: ' . $order->post->ping_status . '<br>';
            echo '$order->post->post_password: ' . $order->post->post_password . '<br>';
            echo '$order->post->post_name: ' . $order->post->post_name . '<br>';
            echo '$order->post->to_ping: ' . $order->post->to_ping . '<br>';
            echo '$order->post->pinged: ' . $order->post->pinged . '<br>';
            echo '$order->post->post_modified: ' . $order->post->post_modified . '<br>';
            echo '$order->post->post_modified_gtm: ' . $order->post->post_modified_gtm . '<br>';
            echo '$order->post->post_content_filtered: ' . $order->post->post_content_filtered . '<br>';
            echo '$order->post->post_parent: ' . $order->post->post_parent . '<br>';
            echo '$order->post->guid: ' . $order->post->guid . '<br>';
            echo '$order->post->menu_order: ' . $order->post->menu_order . '<br>';
            echo '$order->post->post_type: ' . $order->post->post_type . '<br>';
            echo '$order->post->post_mime_type: ' . $order->post->post_mime_type . '<br>';
            echo '$order->post->comment_count: ' . $order->post->comment_count . '<br>';
            echo '$order->post->filter: ' . $order->post->filter . '<br>';
            echo '<h4>THE ORDER OBJECT (again):</h4>';
            echo '$order->order_date: ' . $order->order_date . '<br>';
            echo '$order->modified_date: ' . $order->modified_date . '<br>';
            echo '$order->customer_message: ' . $order->customer_message . '<br>';
            echo '$order->customer_note: ' . $order->customer_note . '<br>';
            echo '$order->post_status: ' . $order->post_status . '<br>';
            echo '$order->prices_include_tax: ' . $order->prices_include_tax . '<br>';
            echo '$order->tax_display_cart: ' . $order->tax_display_cart . '<br>';
            echo '$order->display_totals_ex_tax: ' . $order->display_totals_ex_tax . '<br>';
            echo '$order->display_cart_ex_tax: ' . $order->display_cart_ex_tax . '<br>';
            echo '$order->formatted_billing_address->protected: ' . $order->formatted_billing_address->protected . '<br>';
            echo '$order->formatted_shipping_address->protected: ' . $order->formatted_shipping_address->protected . '<br><br>';
            echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - <br><br>';

            // 2) Get the Order meta data
            $order_meta = get_post_meta($order_id);

            echo '<h3>RAW OUTPUT OF THE ORDER META DATA (ARRAY): </h3>';
            echo '<pre>';
            print_r($order_meta);
            echo '<br><br>';
            echo '<h3>THE ORDER META DATA (Using the array syntax notation):</h3>';
            echo '$order_meta[_order_key][0]: ' . $order_meta['_order_key'][0] . '<br>';
            echo '$order_meta[_order_currency][0]: ' . $order_meta['_order_currency'][0] . '<br>';
            echo '$order_meta[_prices_include_tax][0]: ' . $order_meta['_prices_include_tax'][0] . '<br>';
            echo '$order_meta[_customer_user][0]: ' . $order_meta['_customer_user'][0] . '<br>';
            echo '$order_meta[_billing_first_name][0]: ' . $order_meta['_billing_first_name'][0] . '<br><br>';
            echo 'And so on ……… <br><br>';
            echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - <br><br>';

            // 3) Get the order items
            $items = $order->get_items();

            echo '<h3>RAW OUTPUT OF THE ORDER ITEMS DATA (ARRAY): </h3>';

            foreach ($items as $item_id => $item_data) {

                echo '<h4>RAW OUTPUT OF THE ORDER ITEM NUMBER: ' . $item_id . '): </h4>';
                echo '<pre>';
                print_r($item_data);
                echo '<br><br>';
                echo 'Item ID: ' . $item_id . '<br>';
                echo '$item["product_id"] <i>(product ID)</i>: ' . $item_data['product_id'] . '<br>';
                echo '$item["name"] <i>(Product Name)</i>: ' . $item_data['name'] . '<br>';
                echo '$item["url"] <i>(Product Url)</i>: ' . get_the_permalink($item_data['product_id']) . '<br>';
                // Using get_item_meta() method
                echo 'Item quantity <i>(product quantity)</i>: ' . $order->get_item_meta($item_id, '_qty', true) . '<br><br>';
                echo 'Item line total <i>(product quantity)</i>: ' . $order->get_item_meta($item_id, '_line_total', true) . '<br><br>';
                echo 'And so on ……… <br><br>';
                echo '- - - - - - - - - - - - - <br><br>';

                $product_id = $item_data['product_id'];

                $product_category = wp_get_post_terms($product_id, 'product_cat');
                $cats = '';
                $c = 0;
                foreach ($product_category as $cat) {
                    $sep = ( $c == 0 ? ',' : '');
                    $cats .= $cat->name . $sep;
                    $c++;
                }

                echo $cats;
            }

            echo $string = WC_Geolocation::get_ip_address();
            echo '- - - - - - E N D - - - - - <br><br>';
        }

    }

}
$seon_settings = new SEON_Settings();
