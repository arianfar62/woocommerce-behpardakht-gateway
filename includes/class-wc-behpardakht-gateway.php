<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Behpardakht_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'behpardakht';
        $this->method_title       = __( 'به‌پرداخت ملت', 'behpardakht' );
        $this->method_description = __( 'پرداخت امن از طریق درگاه به‌پرداخت (بانک ملت).', 'behpardakht' );

        $this->has_fields = false;

        // بارگذاری تنظیمات
        $this->init_form_fields();
        $this->init_settings();

        // تعریف تنظیمات کاربر
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->enabled      = $this->get_option( 'enabled' );
        $this->terminal_id  = $this->get_option( 'terminal_id' );
        $this->username     = $this->get_option( 'username' );
        $this->password     = $this->get_option( 'password' );

        // تنظیم آیکون
        $this->icon = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/behpardakht.png';
    
        // افزودن اکشن‌ها
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

        add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'callback_handler' ) );
    }

public function return_from_bank() {
    if ( isset( $_GET['wc_order'] ) ) {
        $order_id = absint( $_GET['wc_order'] );
    } else {
        $order_id = absint( WC()->session->get( 'behpardakht_order_id' ) );
    }
    if ( isset( $order_id ) && ! empty( $order_id ) ) {
        $order = wc_get_order( $order_id );
        if ( $order->get_status() !== 'completed' ) {
            // دریافت اطلاعات از بانک
            // ...

            if ( $response_code == 0 ) {
                // پرداخت موفق
                // اجرای کدهای مربوط به تایید و تسویه تراکنش
                // ...

                // خالی کردن سبد خرید تنها در صورت پرداخت موفق
                WC()->cart->empty_cart();
                WC()->session->__unset( 'behpardakht_order_id' );

                // تنظیم وضعیت سفارش به تکمیل شده
                $order->payment_complete();
                $order->add_order_note( $message );

                // هدایت به صفحه تشکر
                wp_redirect( $this->get_return_url( $order ) );
                exit();
            } else {
                // پرداخت ناموفق یا انصراف کاربر

                // اضافه کردن پیام خطا یا اطلاع‌رسانی به کاربر
                wc_add_notice( __( 'پرداخت ناموفق بود یا توسط کاربر لغو شد.', 'behpardakht' ), 'error' );

                // هدایت کاربر به صفحه پرداخت بدون خالی کردن سبد خرید
                wp_safe_redirect( wc_get_checkout_url() );
                exit();
            }
        } else {
            // سفارش قبلاً تکمیل شده است
            // ...
        }
    } else {
        // شماره سفارش موجود نیست
        // ...
    }
}


    public function init_form_fields() {
        $this->form_fields = apply_filters( 'behpardakht_wc_form_fields', array(
            'enabled' => array(
                'title'       => __( 'فعالسازی/ غیرفعالسازی', 'behpardakht' ),
                'type'        => 'checkbox',
                'label'       => __( 'فعالسازی درگاه به‌پرداخت', 'behpardakht' ),
                'default'     => 'no'
            ),
            'title' => array(
                'title'       => __( 'عنوان', 'behpardakht' ),
                'type'        => 'text',
                'description' => __( 'عنوانی که در هنگام پرداخت به کاربر نمایش داده می‌شود.', 'behpardakht' ),
                'default'     => __( 'به‌پرداخت (بانک ملت)', 'behpardakht' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'توضیحات', 'behpardakht' ),
                'type'        => 'textarea',
                'description' => __( 'توضیحاتی که در هنگام پرداخت به کاربر نمایش داده می‌شود.', 'behpardakht' ),
                'default'     => __( 'پرداخت امن از طریق به‌پرداخت (بانک ملت).', 'behpardakht' ),
                'desc_tip'    => true,
            ),
            'terminal_id' => array(
                'title'       => __( 'ترمینال آیدی', 'behpardakht' ),
                'type'        => 'text',
                'description' => __( 'ترمینال آیدی دریافت شده از به‌پرداخت.', 'behpardakht' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'username' => array(
                'title'       => __( 'نام کاربری', 'behpardakht' ),
                'type'        => 'text',
                'description' => __( 'نام کاربری دریافت شده از به‌پرداخت.', 'behpardakht' ),
                'default'     => '',
            ),
            'password' => array(
                'title'       => __( 'رمز عبور', 'behpardakht' ),
                'type'        => 'password',
                'description' => __( 'رمز عبور دریافت شده از به‌پرداخت.', 'behpardakht' ),
                'default'     => '',
            ),
        ) );
    }

    public function admin_options() {
        echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
        echo wp_kses_post( wpautop( $this->get_method_description() ) );
        echo '<table class="form-table">';
        // تولید فیلدهای تنظیمات
        $this->generate_settings_html();
        echo '</table>';
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // ارسال اطلاعات به درگاه و دریافت آدرس بازگشت
        $redirect_url = $this->send_to_behpardakht( $order );

        if ( $redirect_url ) {
            // بازگرداندن نتیجه برای هدایت به درگاه
            return array(
                'result'   => 'success',
                'redirect' => $redirect_url,
            );
        } else {
            wc_add_notice( __( 'خطا در اتصال به درگاه، لطفا دوباره تلاش کنید.', 'behpardakht' ), 'error' );
            return;
        }
    }

    public function receipt_page( $order ) {
        echo '<p>' . __( 'در حال انتقال به درگاه پرداخت...', 'behpardakht' ) . '</p>';
    }

    private function send_to_behpardakht( $order ) {
        // پیاده‌سازی ارسال درخواست به به‌پرداخت و دریافت URL بازگشت

        $terminalId = $this->terminal_id;
        $userName = $this->username;
        $userPassword = $this->password;
        $orderId = $order->get_id();
        $amount = $order->get_total() * 10; // تبدیل تومان به ریال
        $additionalData = '';
        $callbackUrl = WC()->api_request_url( strtolower( get_class( $this ) ) );

        try {
            $client = new \SoapClient( 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl' );

            $params = array(
                'terminalId'      => intval( $terminalId ),
                'userName'        => $userName,
                'userPassword'    => $userPassword,
                'orderId'         => intval( $orderId ),
                'amount'          => intval( $amount ),
                'localDate'       => date( "Ymd" ),
                'localTime'       => date( "His" ),
                'additionalData'  => $additionalData,
                'callBackUrl'     => $callbackUrl,
                'payerId'         => 0,
            );

            $result = $client->bpPayRequest( $params );

            $res = explode( ',', $result->return );

            if ( $res[0] == '0' ) {
                // موفقیت‌آمیز، $res[1] حاوی RefId است
                $refId = $res[1];

                // ذخیره RefId در متادیتای سفارش
                $order->update_meta_data( '_behpardakht_ref_id', $refId );
                $order->save();

                // ساخت URL هدایت به درگاه
                $behpardakhtUrl = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';
                $redirectUrl = $behpardakhtUrl . '?RefId=' . $refId;

                return $redirectUrl;

            } else {
                // خطا
                $error_message = $this->get_error_message( $res[0] );
                wc_add_notice( $error_message, 'error' );
                return false;
            }

        } catch ( \Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            return false;
        }
    }

    public function callback_handler() {
        // مدیریت بازگشت از درگاه به‌پرداخت

        // دریافت پارامترها از POST
        $ResCode = isset( $_POST['ResCode'] ) ? $_POST['ResCode'] : null;
        $SaleOrderId = isset( $_POST['SaleOrderId'] ) ? $_POST['SaleOrderId'] : null;
        $SaleReferenceId = isset( $_POST['SaleReferenceId'] ) ? $_POST['SaleReferenceId'] : null;
        $RefId = isset( $_POST['RefId'] ) ? $_POST['RefId'] : null;

        // دریافت سفارش بر اساس ID
        $order = wc_get_order( $SaleOrderId );

        if ( $ResCode == '0' ) {
            // پرداخت موفق

            // تایید تراکنش
            $verified = $this->verify_transaction( $order, $SaleOrderId, $SaleReferenceId );

            if ( $verified ) {
                // پرداخت کامل شد
                $order->payment_complete( $SaleReferenceId );
                $order->add_order_note( sprintf( __( 'پرداخت به‌پرداخت با موفقیت انجام شد. کد پیگیری: %s', 'behpardakht' ), $SaleReferenceId ) );
                wc_add_notice( __( 'پرداخت با موفقیت انجام شد.', 'behpardakht' ), 'success' );
            } else {
                // تایید تراکنش ناموفق
                $order->update_status( 'failed', __( 'تایید تراکنش به‌پرداخت ناموفق بود.', 'behpardakht' ) );
                wc_add_notice( __( 'تایید تراکنش ناموفق بود.', 'behpardakht' ), 'error' );
            }

        } else {
            // پرداخت ناموفق
            $order->update_status( 'failed', __( 'پرداخت به‌پرداخت ناموفق بود.', 'behpardakht' ) );
            wc_add_notice( __( 'پرداخت ناموفق بود یا توسط کاربر لغو شد.', 'behpardakht' ), 'error' );
        }

        // هدایت به صفحه سفارش
        wp_redirect( $this->get_return_url( $order ) );
        exit;
    }

    private function verify_transaction( $order, $SaleOrderId, $SaleReferenceId ) {
        // پیاده‌سازی تایید تراکنش با به‌پرداخت

        $terminalId = $this->terminal_id;
        $userName = $this->username;
        $userPassword = $this->password;

        try {
            $client = new \SoapClient( 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl' );
            $params = array(
                'terminalId'      => intval( $terminalId ),
                'userName'        => $userName,
                'userPassword'    => $userPassword,
                'orderId'         => intval( $SaleOrderId ),
                'saleOrderId'     => intval( $SaleOrderId ),
                'saleReferenceId' => intval( $SaleReferenceId ),
            );

            $result = $client->bpVerifyRequest( $params );

            if ( $result->return == '0' ) {
                // تایید موفقیت‌آمیز
                $settle_result = $client->bpSettleRequest( $params );

                if ( $settle_result->return == '0' ) {
                    // واریز موفقیت‌آمیز
                    return true;
                } else {
                    // خطا در واریز
                    $order->add_order_note( sprintf( __( 'خطا در واریز وجه. کد خطا: %s', 'behpardakht' ), $settle_result->return ) );
                    return false;
                }
            } else {
                // خطا در تایید
                $order->add_order_note( sprintf( __( 'خطا در تایید تراکنش. کد خطا: %s', 'behpardakht' ), $result->return ) );
                return false;
            }

        } catch ( \Exception $e ) {
            $order->add_order_note( $e->getMessage() );
            return false;
        }
    }

    private function get_error_message( $code ) {
        // ترجمه کدهای خطای به‌پرداخت
        $messages = array(
            '11' => __( 'شماره کارت نامعتبر است.', 'behpardakht' ),
            '12' => __( 'موجودی کافی نیست.', 'behpardakht' ),
            '13' => __( 'رمز نادرست است.', 'behpardakht' ),
            '14' => __( 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است.', 'behpardakht' ),
            '15' => __( 'کارت نامعتبر است.', 'behpardakht' ),
            '16' => __( 'دفعات برداشت وجه بیش از حد مجاز است.', 'behpardakht' ),
            '17' => __( 'کاربر از انجام تراکنش منصرف شده است.', 'behpardakht' ),
            '18' => __( 'تاریخ انقضای کارت گذشته است.', 'behpardakht' ),
            '19' => __( 'مبلغ برداشت وجه بیش از حد مجاز است.', 'behpardakht' ),
            // سایر کدها و پیام‌ها
        );

        if ( array_key_exists( $code, $messages ) ) {
            return $messages[ $code ];
        } else {
            return __( 'خطای نامشخصی رخ داده است.', 'behpardakht' );
        }
    }
}

