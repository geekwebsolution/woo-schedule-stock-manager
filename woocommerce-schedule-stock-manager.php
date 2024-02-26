<?php
/*
Plugin Name: WooCommerce Schedule Stock Manager
Description: This Plugin provide you options to manage the stock quantity automatic increase throughout daily, weekly, monthly, hourly and yearly schedule type options of all your woocommerce products
Author: Geek Code Lab
Version: 2.7
WC tested up to: 8.6.1
Author URI: https://geekcodelab.com/
Text Domain: woocommerce-schedule-stock-manager
*/
//do not allow direct access
if (strpos(strtolower($_SERVER['SCRIPT_NAME']), strtolower(basename(__FILE__)))) {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

register_activation_hook( __FILE__, 'wssmgk_script_activation' );
function wssmgk_script_activation() {
	if (is_plugin_active( 'woo-schedule-stock-manager-pro/woocommerce-schedule-stock-manager-pro.php' ) ) {
		deactivate_plugins('woo-schedule-stock-manager-pro/woocommerce-schedule-stock-manager-pro.php');
   	}
}

/** Trigger an admin notice if WooCommerce is not installed.*/
if ( ! function_exists( 'wssmgk_install_woocommerce_admin_notice' ) ) {
	function wssmgk_install_woocommerce_admin_notice() { ?>
		<div class="error">
			<p>
				<?php
				// translators: %s is the plugin name.
				echo esc_html( sprintf( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'WooCommerce Schedule Stock Manager' ), 'woocommerce-schedule-stock-manager' );
				?>
			</p>
		</div>
		<?php
	}
}
function wssmgk_woocommerce_constructor() {
    // Check WooCommerce installation
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wssmgk_install_woocommerce_admin_notice' );
		return;
	}

}
add_action( 'plugins_loaded', 'wssmgk_woocommerce_constructor' );

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'wssmgk_add_plugin_settings_link');
function wssmgk_add_plugin_settings_link( $links ) {
	$support_link = '<a href="https://geekcodelab.com/contact/" target="_blank" >' . __( 'Support', 'woocommerce-schedule-stock-manager' ) . '</a>'; 
	array_unshift( $links, $support_link );

    $pro_link = '<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-schedule-stock-manager-pro/" target="_blank" style="color:#46b450;font-weight: 600;">' . __( 'Premium Upgrade', 'woocommerce-schedule-stock-manager' ) . '</a>'; 
	array_unshift( $links, $pro_link );

	return $links;
}   

/* * ******************
 * Global constants
 * ****************** */

// ********** Be sure to use "Match case," and do UPPER and lower case seperately ****************

define('WSSMGK_BUILD', '2.7');  // Used to force load of latest .js files
define('WSSMGK_FILE', __FILE__); // For use in other files
define('WSSMGK_PATH', plugin_dir_path(__FILE__));
define('WSSMGK_URL', plugin_dir_url(__FILE__));

/**
 * Added HPOS support for woocommerce
 */
add_action( 'before_woocommerce_init', 'wssmgk_before_woocommerce_init' );
function wssmgk_before_woocommerce_init() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

/* * ******************
 * Includes
 * ****************** */
//function to run on activation  
require_once WSSMGK_PATH . 'library/class-admin.php';
require_once WSSMGK_PATH . 'library/class-schedule.php';

//  Initialize plugin settings and hooks ... 
wssmgk_auto_stock_manager::wssmgk_setup();
wssmgk_schedule_stock_manager::wssmgk_shedule_setup();