<?php
/**
* Plugin Name:     WooCommerce Chile RUT
* Plugin URI:      https://github.com/asterion/woocommerce-chile-rut
* Description:     Agrega el campo RUT al checkout
* Author:          Marcos Matamala
* Author URI:      https://github.com/asterion/woocommerce-chile-rut
* Text Domain:     woocommerce-chile-rut
* Domain Path:     /languages
* Version:         0.1.0
*
* @package         Woocommerce_Chile_Rut
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ChileRut.php';

/**
* Check if WooCommerce is active
**/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_filter( 'woocommerce_checkout_fields' , 'woocommerce_chile_rut_checkout' );

    function woocommerce_chile_rut_checkout( $fields ) {
        $fields['billing']['billing_rut'] = [
            'type' => 'text',
            'label' => __('RUT', 'woocommerce'),
            'placeholder' => _x('RUT', 'placeholder', 'woocommerce'),
            'priority' => 120,
            'class' => array('form-row-wide'),
            'required' => true
        ];

        return $fields;
    }

    /**
    * Process the checkout
    */
    add_action('woocommerce_checkout_process', 'woocommerce_chile_rut_checkout_process');

    function woocommerce_chile_rut_checkout_process() {
        if ( ! $_POST['billing_rut'] ) {
            wc_add_notice( __( 'Por favor ingrese el campo RUT.' ), 'error' );
        } else {
            $validador = new ChileRut();
            $rut = preg_replace('/[^0-9kK]/', '', $_POST['billing_rut']);

            if (!$validador->check($rut)) {
                wc_add_notice( __( 'El RUT ingresado no es correcto.' ), 'error' );
            }
        }
    }

    add_action( 'woocommerce_checkout_update_order_meta', 'woocommerce_chile_rut_checkout_update_order_meta' );

    function woocommerce_chile_rut_checkout_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['billing_rut'] ) ) {
            update_post_meta( $order_id, __('RUT', 'woocommerce'), sanitize_text_field( $_POST['billing_rut'] ) );
        }
    }

    /**
    * Display field value on the order edit page
    */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocommerce_chile_rut_checkout_display_admin_order_meta', 10, 1 );

    function woocommerce_chile_rut_checkout_display_admin_order_meta($order){
        echo '<p><strong>'.__('RUT', 'woocommerce').':</strong> ' . get_post_meta( $order->id, __('RUT', 'woocommerce'), true ) . '</p>';
    }
}
