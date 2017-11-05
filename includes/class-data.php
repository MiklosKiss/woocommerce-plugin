<?php

if (!class_exists('SEON_Data')) {

    class SEON_Data {

        /**
         * Construct
         * 
         * @since 1.0.0
         * @access public
         * 
         */
        function __construct() {
            add_action('woocommerce_checkout_order_processed', array($this, 'request'), 1, 1);
        }

        /**
         * API Request
         * 
         * @since 1.0.0
         * @access public
         * @params $order_id
         * 
         */
        public function request($order_id) {

            $seon_settings_key = ( get_option('seon_licence_key') ? get_option('seon_licence_key') : get_site_option('seon_licence_key') );
            $url = 'https://api.seondev.space/SeonRestService/fraud-api/v1.0/';
            
            $response = wp_remote_post(
                    $url, array(
                'headers' => 'Content-Type: application/json; charset=UTF-8',
                'body' => $this->order_data($order_id, $seon_settings_key)
                    )
            );


            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log("Something went wrong: $error_message");
            } else {
                error_log(print_r($response['body'], TRUE));


                /* Add Custom Fields */
                $success = json_decode($response['body']);

                if ($success->success && $success->data) {

                    if (!$success->data->is_fraud) {
                        $order = new WC_Order($order_id);

                        if (!empty($order) && false === $success->data->is_fraud) {
                            $order->update_status('on-hold');
                        }
                    }

                    add_post_meta($order_id, '_order_seon_status', $success->data->state);
                    add_post_meta($order_id, '_order_seon_score', $success->data->fraud_score);
                    add_post_meta($order_id, '_order_seon_id', $success->data->seon_id);
                }
            }
        }

        /**
         * Order data
         * 
         * @since 1.0.0
         * @access public
         * @params $order_id
         * 
         */
        public function order_data($order_id, $api_key) {

            $order = new WC_Order($order_id);
            $order_meta = get_post_meta($order_id);
            $user_data = get_userdata($order_meta['_customer_user'][0]);


            $seon_settings_agent = ( get_option('seon_activate_agent') ? get_option('seon_activate_agent') : get_site_option('seon_activate_agent') );

            if ((!filter_var($order_meta['_customer_ip_address'][0], FILTER_VALIDATE_IP) === false ) && (!in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')) )) {
                $customer_ip = $order_meta['_customer_ip_address'][0];
            } else {
                $customer_ip = '';
            }

            /*
             * System data 
             * ------------------------------------------------------------------- */
            $purchase['ip'] = $customer_ip;
            $purchase['license_key'] = $api_key;
            $purchase['javascript'] = ($seon_settings_agent) ? 'true' : 'false';

            /*
             * User data 
             * ------------------------------------------------------------------- */
            $purchase['user_id'] = $order_meta['_customer_user'][0];
            $purchase['affiliate_id'] = '';
            $purchase['user_fullname'] = $order_meta['_billing_first_name'][0] . ' ' . $order_meta['_billing_last_name'][0];
            $purchase['user_name'] = $order_meta['_billing_first_name'][0];
            $purchase['affiliate_name'] = '';
            $purchase['user_order_memo'] = $order->customer_note;
            $purchase['email'] = $order_meta['_billing_email'][0];
            $purchase['run_email_api'] = 'true'; // should be setup in configuration
            $purchase['password_hash'] = $user_data->user_pass;
            $purchase['user_created'] = strtotime($user_data->user_registered);
            $purchase['user_country'] = $order_meta['_billing_country'][0];
            $purchase['user_city'] = $order_meta['_billing_city'][0];
            $purchase['user_region'] = $order_meta['_billing_state'][0];
            $purchase['user_zip'] = $order_meta['_billing_postcode'][0];
            $purchase['user_street'] = $order_meta['_billing_address_1'][0];
            $purchase['user_street2'] = $order_meta['_billing_address_2'][0];

            /*
             * Payment data 
             * ------------------------------------------------------------------- */
            $purchase['payment_mode'] = $order_meta['_payment_method'][0];
            $purchase['payment_id'] = $order_meta['_order_key'][0];
            $purchase['action_type'] = 'purchase';
            $purchase['phone_number'] = $order_meta['_billing_phone'][0];
            $purchase['transaction_type'] = 'purchase';
            $purchase['transaction_amount'] = $order_meta['_order_total'][0];
            $purchase['transaction_currency'] = $order_meta['_order_currency'][0];

            /*
             * Shipping data 
             * ------------------------------------------------------------------- */

            $purchase['shipping_country'] = $order_meta['_shipping_country'][0];
            $purchase['shipping_city'] = $order_meta['_shipping_city'][0];
            $purchase['shipping_region'] = $order_meta['_shipping_state'][0];
            $purchase['shipping_zip'] = $order_meta['_shipping_postcode'][0];
            $purchase['shipping_street'] = $order_meta['_shipping_address_1'][0];
            $purchase['shipping_street2'] = $order_meta['_shipping_address_2'][0];
            $purchase['shipping_fullname'] = $order_meta['_shipping_first_name'][0] . ' ' . $order_meta['_shipping_last_name'][0];
            $purchase['shipping_phone'] = $order_meta['_shipping_first_name'][0];
            $purchase['shipping_method'] = $order_meta['_billing_country'][0];

            /*
             * Billing data 
             * ------------------------------------------------------------------- */
            $purchase['billing_country'] = $order_meta['_billing_country'][0];
            $purchase['billing_city'] = $order_meta['_billing_city'][0];
            $purchase['billing_region'] = $order_meta['_billing_state'][0];
            $purchase['billing_zip'] = $order_meta['_billing_postcode'][0];
            $purchase['billing_street'] = $order_meta['_billing_address_1'][0];
            $purchase['billing_street2'] = $order_meta['_billing_address_2'][0];
            $purchase['billing_phone'] = $order_meta['_billing_phone'][0];

            /*
             * Misc data 
             * ------------------------------------------------------------------- */

            if ($order->get_used_coupons()) {
                $discount = $order->get_used_coupons();
                $purchase['discount_code'] = $discount[0];
            }

            /*
             * Product data 
             * ------------------------------------------------------------------- */

            $items = $order->get_items();

            $purchase['items'] = array();


            $i = 0;
            foreach ($items as $item_id => $item_data) {

                $product_category = wp_get_post_terms($item_data['product_id'], 'product_cat');
                $cats = '';
                $c = 0;
                foreach ($product_category as $cat) {
                    $sep = ( $c == 0 ? ',' : '');
                    $cats .= $cat->name . $sep;
                    $c++;
                }

                $purchase['items'][$i]['item_id'] = $item_data['product_id'];
                $purchase['items'][$i]['item_quantity'] = $item_data['qty'];
                $purchase['items'][$i]['item_name'] = $item_data['name'];
                $purchase['items'][$i]['item_price'] = get_post_meta($item_data['product_id'], '_regular_price', true);
                $purchase['items'][$i]['item_categories'] = $cats;
                $purchase['items'][$i]['item_url'] = get_the_permalink($item_data['product_id']);
                $i++;
            }

            $purchase = json_encode($purchase);

            return $purchase;
        }

    }

    $GLOBALS['SEON_Data'] = new SEON_Data();
}