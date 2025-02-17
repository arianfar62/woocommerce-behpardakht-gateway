<?php
/*
Plugin Name: پرداخت امن به پرداخت ملت * سونار وب
Plugin URI: https://sonarweb.ir/
Description: افزونه درگاه به‌پرداخت (بانک ملت) برای ووکامرس.
Version: 1.2.0
Author: اللهیار آریان فر
Author URI: https://sonarweb.ir/
Text Domain: behpardakht
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // جلوگیری از دسترسی مستقیم
}

// اضافه کردن کلاس اصلی درگاه
add_action( 'plugins_loaded', 'behpardakht_wc_init', 11 );

function behpardakht_wc_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

    include_once( dirname( __FILE__ ) . '/includes/class-wc-behpardakht-gateway.php' );

    add_filter( 'woocommerce_payment_gateways', 'behpardakht_add_gateway_class' );
    function behpardakht_add_gateway_class( $gateways ) {
        $gateways[] = 'WC_Behpardakht_Gateway';
        return $gateways;
    }
}
/*
// بارگذاری فایل‌های ترجمه
add_action( 'plugins_loaded', 'behpardakht_load_textdomain' );
function behpardakht_load_textdomain() {
    load_plugin_textdomain( 'behpardakht', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
*/
