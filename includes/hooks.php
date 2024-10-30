<?php

class carrrot_hooks
{

    /**
     *
     *woocommerce_after_single_product hook callback
     *User viewed product
     *
     */
    public static function product_viewed()
    {
        global $product;
        if (isset($_COOKIE["carrrot_uid"])) {
            $carrrotUID = $_COOKIE["carrrot_uid"];
            if ($product && $product->id) {
                /*
                Getting product info:
                    - Product name
                    - Product page URL
                    - Product image
                    - Product price
                */
                $_product = wc_get_product($product->id);
                $image_id = $_product->get_image_id();
                if ($image_id) {
                    $image = wp_get_attachment_image_src($image_id, 'full');
                }

                $params = array(
                    '$url' => $_product->get_permalink(),
                    '$amount' => round($_product->get_price()),
                    '$name' => $_product->get_title()
                );

                if (isset($image[0])) {
                    $params['$img'] = $image[0];
                }
                self::carrrot_send_operations($carrrotUID, array( //Adding item to users list of viewed products
                        array("op" => "union", "key" => '$viewed_products', "value" => $params['$name'])
                    )
                );
                self::carrrot_send_event($carrrotUID, '$product_viewed', $params);//Adding "Product viewed" event to users chronology

            }
        }
    }

    /**
     *
     *woocommerce_add_to_cart_redirect, woocommerce_ajax_added_to_cart hook callback
     *Added product to cart
     *
     * @arg - contains product id, if callback called via ajax
     *
     */
    public static function product_added($arg)
    {
        global $product;
        $is_ajax = (isset($_REQUEST["wc-ajax"]) && $_REQUEST["wc-ajax"] == 'add_to_cart'); //Checking if product was added via ajax
        $product_id = 0;
        if ($is_ajax) {
            $product_id = $arg;
        } else {
            if (isset($_REQUEST['add-to-cart'])){
                $product_id = (int)apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['add-to-cart'])); //Getting added product id, by applying appropriate filter
            }
        }

        if (isset($_COOKIE["carrrot_uid"]) && isset($product_id) && $product_id > 0 && ($is_ajax && isset($arg) || !$is_ajax && isset($_REQUEST['add-to-cart']))) {
            /*Getting product info:
                - Product name
                - Product page URL
                - Product image
                - Product price
            */
            $carrrotUID = $_COOKIE["carrrot_uid"];
            if ($product_id) {
                $_product = wc_get_product($product_id);
                $image_id = $_product->get_image_id();
                if ($image_id) {
                    $image = wp_get_attachment_image_src($image_id, 'full');
                }

                $params = array(
                    '$url' => $_product->get_permalink(),
                    '$amount' => round($_product->get_price()),
                    '$name' => $_product->get_title()
                );
                if (isset($image[0])) {
                    $params['$img'] = $image[0];
                }
                self::carrrot_send_event($carrrotUID, '$cart_added', $params); //	Adding "Product added to cart" event to users chronology. Product info sended as events properties
            }

            $cart_info = self::cart_info(); //Getting current cart condition
            self::carrrot_send_operations($carrrotUID, $cart_info['stand_alone_properties']); //Setting users properties - content of the cart, total of the cart
        }
        if (!$is_ajax) {
            return $arg;
        }
    }

    /**
     *
     *woocommerce_after_cart hook callback
     *User viewed cart
     *
     */
    public static function cart_viewed()
    {
        if (isset($_COOKIE["carrrot_uid"])) {
            $carrrotUID = $_COOKIE["carrrot_uid"];
            $cart_info = self::cart_info();
            self::carrrot_send_event($carrrotUID, '$cart_viewed', $cart_info['event_properties']); //	Adding "Viewed cart" event to users chronology. List of products is sended as events properties
            self::carrrot_send_operations($carrrotUID, $cart_info['stand_alone_properties']);//Setting users properties - content of the cart, total of the cart
        }
    }


    /**
     *
     *woocommerce_before_checkout_form hook callback
     *User started the checkout process
     *
     */
    public static function order_started()
    {
        if (isset($_COOKIE["carrrot_uid"])) {
            $carrrotUID = $_COOKIE["carrrot_uid"];
            self::carrrot_send_event($carrrotUID, '$order_started'); //	Adding "Started order" event to users chronology
        }
    }

    /**
     *
     *woocommerce_checkout_order_processed hook callback
     *User completed the checkout process
     *
     * @$order_id - number of resulting order
     */
    public static function order_completed($order_id)
    {
        echo $_COOKIE["carrrot_uid"];
        if (isset($_COOKIE["carrrot_uid"])) {
            $carrrotUID = $_COOKIE["carrrot_uid"];

            $order = wc_get_order($order_id);

            $total = $order->get_subtotal();
            $name = trim(trim($order->get_billing_first_name()) . " " . trim($order->get_billing_last_name()));
            if (!isset($name) || isset($name) && strlen($name) == 0) {
                $name = trim(trim($order->get_shipping_first_name()) . " " . trim($order->get_shipping_last_name()));
            }
            $phone = $order->get_billing_phone();
            $email = $order->get_billing_email();

            $operations = array();
            $operations[] = array('op' => 'update_or_create', 'key' => '$last_payment', 'value' => round($total)); //User property "Last payment" - total of the current order
            $operations[] = array('op' => 'add', 'key' => '$revenue', 'value' => round($total)); //User property "Revenue" - total of the current order added to previous revenue value
            $operations[] = array('op' => 'delete', 'key' => '$cart_items', 'value' => 0); //Clearing content of the cart
            $operations[] = array('op' => 'delete', 'key' => '$cart_amount', 'value' => 0); //Clearing total of the cart

            $order_items = $order->get_items();
            $items = array();
            foreach ($order_items as $product) {
                $items[] = $product['name'];
                $operations[] = array( //Adding item to the content of the cart
                    'op' => 'union',
                    'key' => '$ordered_items',
                    'value' => $product['name']
                );
            }
            if (isset($name) && strlen($name) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$name', 'value' => $name); //User name from the order
            }

            if (isset($email) && strlen($email) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$email', 'value' => $email); //User email from the order
            }

            if (isset($phone) && strlen($phone) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$phone', 'value' => $phone); //User phone number from the order
            }

            self::carrrot_send_event($carrrotUID, '$order_completed', array('$order_id' => $order_id, '$order_amount' => $total, '$items' => $items)); //	Adding "Completed order" event to users chronology. Order number and total of the order are sended as events properties
            self::carrrot_send_operations($carrrotUID, $operations); //Sending user properties collected earlier
        }
        return $order_id;
    }

    public static function order_authentication($order_id)
    {
        $settings = self::getSettings();
        if (isset($settings["auth"]) && $settings["auth"] && isset($settings["auth_key"]) && strlen($settings["auth_key"]) > 0) {
            $order = wc_get_order( $order_id );
            $userID = $order->get_user_id();
            if (!$userID)
                return;
            ?>
            <script>
                carrrot.auth('<?php echo($userID); ?>', '<?php echo(hash_hmac('sha256', $userID, $settings["auth_key"]))?>');
            </script>
            <?php
        }
    }

    public static function order_status_changed($order_id, $old_status, $new_status)
    {
        $settings = self::getSettings();
        if (isset($settings["auth"])) {
            $order = wc_get_order($order_id);
            $userID = $order->get_user_id();
            if (isset($userID) && $userID) {
                $new_status_name = wc_get_order_status_name($new_status);

                self::carrrot_send_operations($userID, array(
                    array('op' => 'update_or_create', 'key' => '$last_order_status', 'value' => $new_status_name)), true);

                if ($new_status == 'completed') {
                    $order_items = $order->get_items();
                    $items = array();
                    foreach ($order_items as $product) {
                        $items[] = $product['name'];
                    }

                    self::carrrot_send_event($userID, '$order_paid', array('$order_id' => $order_id, '$items' => $items, '$order_amount' => $order->total), true);
                }

                if ($new_status == 'refunded') {
                    $order_items = $order->get_items();
                    $items = array();
                    foreach ($order_items as $product) {
                        $items[] = $product['name'];
                    }

                    self::carrrot_send_event($userID, '$order_refunded', array('$order_id' => $order_id, '$items' => $items), true);
                }

                if ($new_status == 'cancelled') {
                    $order_items = $order->get_items();
                    $items = array();
                    foreach ($order_items as $product) {
                        $items[] = $product['name'];
                    }

                    self::carrrot_send_event($userID, '$order_cancelled', array('$order_id' => $order_id, '$items' => $items), true);
                }
            }
        }
    }

    public static function user_login($user_login)
    {
        set_transient('carrrot_'.$user_login, '1', 0);
    }

    public static function login_authentication()
    {
        global $current_user;
        wp_get_current_user();

        if (!is_user_logged_in())
            return;

        if (!get_transient('carrrot_'.$current_user->user_login))
            return;

        if (isset($_COOKIE["carrrot_uid"])) {
            $carrrotUID = $_COOKIE["carrrot_uid"];
            $settings = self::getSettings();
            $name = trim(get_user_meta($current_user->ID, 'billing_first_name', true). " " .get_user_meta($current_user->ID, 'billing_last_name', true));
            if (!isset($name) || isset($name) && strlen($name) == 0) {
                $name = trim(get_user_meta($current_user->ID, 'shipping_first_name', true) . " " . get_user_meta($current_user->ID, 'shipping_last_name', true));
            }
            $phone = get_user_meta($current_user->ID, 'billing_phone', true);
            $email = get_user_meta($current_user->ID, 'billing_email', true);
            $operations = array();
            if (isset($name) && strlen($name) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$name', 'value' => $name); //User name from the order
            }

            if (isset($email) && strlen($email) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$email', 'value' => $email); //User email from the order
            }

            if (isset($phone) && strlen($phone) > 0) {
                $operations[] = array('op' => 'update_or_create', 'key' => '$phone', 'value' => $phone); //User phone number from the order
            }

            self::carrrot_send_operations($carrrotUID, $operations); //Sending user properties collected earlier

            if (isset($settings["auth"]) && $settings["auth"] && isset($settings["auth_key"]) && strlen($settings["auth_key"]) > 0) {
                ?>
                <script>
                    carrrot.auth('<?php echo($current_user->ID); ?>', '<?php echo(hash_hmac('sha256', $current_user->ID, $settings["auth_key"]))?>');
                </script>
                <?php
            }
        }
        delete_transient('carrrot_'.$current_user->user_login);
    }

    /**
     *
     *Current cart condition
     *
     */
    private
    static function cart_info()
    {
        if (function_exists('WC')) $wc = WC();
        else {
            global $woocommerce;
            $wc = $woocommerce;
        }
        $cart = $wc->cart->get_cart(); //Getting current cart
        $e_cart_items = array();
        $cart_items = array();
        $properties = array();

        $cart_amount = 0;
        if (count($cart)) //If cart isn't empty, then collecting cart info and preparing it for sending
        {
            foreach ($cart as $key => $value) /*
					Collecting products info in 4 lists - products names, URLs, images and costs
					*/ {
                $pid = (!empty($value['variation_id'])) ? $value['variation_id'] : $value['product_id'];

                $_product = wc_get_product($pid);
                $image_id = $_product->get_image_id();
                if ($image_id) {
                    $image = wp_get_attachment_image_src($image_id, 'full');
                }
                $price = $value['data']->price;
                $url = $_product->get_permalink();
                $name = $_product->get_title();
                $quantity = $value['quantity'];

                $e_cart_items['$name'][] = $name;
                $e_cart_items['$url'][] = $url;
                $e_cart_items['$amount'][] = round($price * $quantity);
                if (isset($image[0])) {
                    $e_cart_items['$img'][] = $image[0];
                } else {
                    $e_cart_items['$img'][] = '<No image>';
                }

                $cart_items[] = $name;
                $cart_amount += $price * $quantity;
            }

            $properties[] = array( //Adding item to the content of the cart
                "op" => "update_or_create",
                "key" => '$cart_items',
                "value" => $cart_items
            );

            $properties[] = array( //Total of the cart
                "op" => "update_or_create",
                "key" => '$cart_amount',
                "value" => round($cart_amount)
            );
        } else //Clearing properties with content and total of the cart, if cart is empty
        {
            $properties[] = array(
                "op" => "delete",
                "key" => '$cart_amount',
                "value" => 0
            );

            $properties[] = array(
                "op" => "delete",
                "key" => '$cart_items',
                "value" => 0
            );

        }

        return array('stand_alone_properties' => $properties, 'event_properties' => $e_cart_items);
    }

    /**
     *
     *Getting API keys from settings
     *
     */
    public
    static function getSettings()
    {
        static $settings;
        if (empty($settings)) {
            $settings['api_key'] = get_option('carrrot_api_key');
            $settings['api_secret'] = get_option('carrrot_api_secret');
            $settings['auth_key'] = get_option('carrrot_auth_key');
            $settings['auth'] = get_option('carrrot_auth');
        }
        return $settings;
    }

    /**
     *
     *Sends event to Carrrot
     *
     * @userId - contains either carrrot uid, or inner WP user id
     * @event - name of user event. Standard events start with $, list of standard events can be found in service API documentation
     * @params - propertis that can be sended alongside with event, e.g. product name, with event "Product viewed"
     * @by_user_id - if true, parameter $carrrotUID contains inner WP user id. For usage requires Carrrot authentication to be added.
     *
     */
    public
    static function carrrot_send_event($carrrotUID, $event, $params = array(), $by_user_id = false)
    {
        $settings = self::getSettings();
        $send_uid = $carrrotUID;
        if ($send_uid && $settings && isset($settings["api_key"]) && isset($settings["api_secret"])) {
            $url = "https://api.carrrot.io/v1/users/" . $send_uid . "/events";
            $data = array(
                'auth_token' => "app." . $settings["api_key"] . "." . $settings["api_secret"],
                'event' => $event,
                'by_user_id' => $by_user_id ? 'true' : 'false'
            );

            if (count($params) > 0) {
                $data['params'] = json_encode($params);
            }
            $options = array(
                'headers' => array('Content-type' => 'application/x-www-form-urlencoded'),
                'method' => 'POST',
                'body' => $data
            );

            $result = wp_remote_request($url, $options);
        }
    }

    /**
     *
     *Sends properties to Carrrot
     *
     * @userId - contains either carrrot uid, or inner WP user id
     * @operations - array of arrays. Each first level item contains description of operation (how, where and what you sending). Used to set properties of the user.
     *Standard properties start with $, list of standard properties can be found in service API documentation
     * @by_user_id - if true, parameter $carrrotUID contains inner WP user id. For usage requires Carrrot authentication to be added.
     *
     */
    public
    static function carrrot_send_operations($carrrotUID, $operations, $by_user_id = false, $log_data = false)
    {
        $settings = self::getSettings();
        $send_uid = $carrrotUID;
        if ($send_uid && $settings &&  isset($settings["api_key"]) && isset($settings["api_secret"])) {
            $url = "https://api.carrrot.io/v1/users/" . $send_uid . "/props";
            $data = array(
                'auth_token' => "app." . $settings["api_key"] . "." . $settings["api_secret"],
                'operations' => json_encode($operations),
                'by_user_id' => $by_user_id ? 'true' : 'false'
            );
            if ($log_data) {
                ?>
                console.log('carrrot_send_operations', <?php echo json_encode($data)?>);
            <?php }
            $options = array(
                'headers' => array('Content-type' => 'application/x-www-form-urlencoded'),
                'method' => 'POST',
                'body' => $data
            );
            $result = wp_remote_request($url, $options);
        }
    }

    public
    static function write_to_log($Title, $Text = null)
    {
        $message = $Title;
        if ($Text != null && strlen($Text) > 0) {
            $message .= ": \r\n" . (string)$Text;
        }
        $fileResult = "[" . date("Y-m-d H:i:s") . "] " . $message . "\r\n";
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/carrot_integr_log.txt';
        $ret = file_put_contents($filePath, $fileResult, FILE_APPEND | LOCK_EX);
    }
}