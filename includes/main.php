<?php
//Require file with callbacks
require_once('hooks.php');

class carrrot
{
    private static $initiated = false;

    //Initializing required hooks if they not initiated yet
    public static function init()
    {
        if (!self::$initiated) {
            self::add_hooks();
            self::$initiated = true;
        }
    }


    private static function add_hooks()
    {
        add_filter('plugin_action_links_' . CARRROT_PLUGIN_BASE, array('carrrot', 'action_links')); //Adding action links for plugin in admin panel
        add_action('admin_menu', array('carrrot', 'add_pages')); //Adding plugins options page

        if (!is_admin()) {
            add_action('wp_head', array('carrrot', 'plugin_main')); //Adding Carrrot chat to every page if it isn't admin panel
        }

        add_action('wp_login', array('carrrot_hooks', 'user_login'));
        add_action('wp_footer', array('carrrot_hooks', 'login_authentication'));

        add_action('woocommerce_after_single_product', array('carrrot_hooks', 'product_viewed')); //User viewed product
        add_action('woocommerce_after_cart', array('carrrot_hooks', 'cart_viewed')); //User viewed cart
        add_filter('woocommerce_add_to_cart_redirect', array('carrrot_hooks', 'product_added')); //User added product to cart via postback
        add_filter('woocommerce_ajax_added_to_cart', array('carrrot_hooks', 'product_added')); //User added product to cart via ajax
        add_action('woocommerce_before_checkout_form', array('carrrot_hooks', 'order_started')); //User opened checkout page
        add_action('woocommerce_checkout_order_processed', array('carrrot_hooks', 'order_completed'));    //User completed checkout and order was created

        add_action('woocommerce_order_status_changed', array('carrrot_hooks', 'order_status_changed'), 10, 3);
        add_action('woocommerce_thankyou', array('carrrot_hooks', 'order_authentication'));

        register_uninstall_hook(__FILE__, array('carrrot', 'delete_options'));
    }

    //Plugins action links
    public static function action_links($actions)
    {
        $actions[] = '<a href="plugins.php?page=carrrot">' . __('Settings', 'carrrot') . '</a>';
        return $actions;
    }

    //Plugin options page in menu
    public static function add_pages()
    {
        add_submenu_page(
            'plugins.php',
            __('Carrrot', 'carrrot'),
            __('Carrrot', 'carrrot'),
            'manage_options',
            "carrrot",
            array('carrrot', 'settings_page')
        );
        add_action('admin_init', array('carrrot', 'add_options')); //Adding plugins options
    }

    //Creating plugins options in wp
    public static function add_options()
    {
        if (!get_option('carrrot_api_key')) {
            add_option('carrrot_api_key');
        }
        if (!get_option('carrrot_api_secret')) {
            add_option('carrrot_api_secret');
        }
        if (!get_option('carrrot_auth_key')) {
            add_option('carrrot_auth_key');
        }
        if (!get_option('carrrot_auth')) {
            add_option('carrrot_auth');
        }
    }

    //Deleting options with plugin deletion
    public static function delete_options()
    {
        delete_option('carrrot_api_key');
        delete_option('carrrot_api_secret');
        delete_option('carrrot_auth_key');
        delete_option('carrrot_auth');
    }

    //Form and initialize options page
    public static function settings_page()
    {
        if (isset($_REQUEST['carrrot_plugin_form_submit'])
            && check_admin_referer('carrrot_plugin_settings', 'carrrot_plugin_nonce') //Updating options if options form was submited
        ) {
            if (isset($_REQUEST['carrrot_api_key'])) {
                update_option('carrrot_api_key', $_REQUEST['carrrot_api_key']);
            } else {
                update_option('carrrot_api_key', '');
            }

            if (isset($_REQUEST['carrrot_api_secret'])) {
                update_option('carrrot_api_secret', $_REQUEST['carrrot_api_secret']);
            } else {
                update_option('carrrot_api_secret', '');
            }

            if (isset($_REQUEST['carrrot_auth_key'])) {
                update_option('carrrot_auth_key', $_REQUEST['carrrot_auth_key']);
            } else {
                update_option('carrrot_auth_key', '');
            }

            if (isset($_REQUEST['carrrot_auth'])) {
                update_option('carrrot_auth', $_REQUEST['carrrot_auth']);
            } else {
                update_option('carrrot_auth', '');
            }

            $message = __("Settings saved", 'carrrot'); //Everything's OK
        }
        if (!isset($message)) {
            $message = __('Failed', 'carrrot'); //Something went wrong
        }
        $page = CARRROT_PLUGIN_DIR . 'options.php';
        ob_start();
        include $page; //Including options page template
        echo ob_get_clean();
    }

    //Adding service code
    public static function plugin_main()
    {
        $settings = carrrot_hooks::getSettings();
        if (!empty($settings['api_key'])) //If api_key option is set add code to each non-admin page
        {
            ?>
            <!-- Carrrot BEGIN -->
            <script type="text/javascript">
                !function(){function t(t,e){return function(){window.carrrotasync.push(t,arguments)}}if("undefined"==typeof carrrot){var e=document.createElement("script");e.type="text/javascript",e.async=!0,e.src="//cdn.carrrot.io/api.min.js",document.getElementsByTagName("head")[0].appendChild(e),window.carrrot={},window.carrrotasync=[],carrrot.settings={};for(var n=["connect","track","identify","auth","oth","onReady","addCallback","removeCallback","trackMessageInteraction"],a=0;a<n.length;a++)carrrot[n[a]]=t(n[a])}}(),carrrot.connect('<?php echo $settings['api_key']; ?>');
            </script>
            <!-- Carrrot END -->
            <?php
        }
    }

}