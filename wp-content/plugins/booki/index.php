<?php
/**
 * @package Booki
 * @version 2.6d
 */
/*
Plugin Name: Booki
Plugin URI: http://www.booki.io
Description: A modern booking plugin for WordPress. This plugin allows you to setup appointments or reservations with time that adapts to users timezone. You can make payment via PayPal or simply book and pay later. Make sure you read the documentation, available in PDF format within the plugin.
Version: 2.6d
Author: Alessandro Zifiglio
Author URI: http://www.typps.com
License: Copyright @Alessandro Zifiglio. All rights reserved. Codecanyon licensing applies.
*/
class Booki{
	public function __construct(){
		if(!defined('BOOKI_VERSION')){
			define('BOOKI_VERSION', 2.6);
		}

		if(!defined('BOOKI_ROOT')){
			define('BOOKI_ROOT', dirname( __FILE__ ));
		}

		if(!defined('BOOKI_PLUGINDIR')){
			define('BOOKI_PLUGINDIR', trailingslashit( get_bloginfo('wpurl') ) . PLUGINDIR . '/' . basename(dirname( __FILE__ )) . '/'    ) ;
		}

		if(!defined('BOOKI_PAYPAL_MERCHANT_SDK')){
			define('BOOKI_PAYPAL_MERCHANT_SDK', dirname( __FILE__ ) . '/lib/ext/paypal/merchant-sdk-php-2.1.96/');
		}
		
		if(!defined('BOOKI_TCPDF')){
			define('BOOKI_TCPDF', dirname( __FILE__ ) . '/lib/ext/tcpdf/');
		}

		if(!defined('BOOKI_MAILCHIMP')){
			define('BOOKI_MAILCHIMP', dirname( __FILE__ ) . '/lib/ext/mailchimp/src/');
		}

		if(!defined('BOOKI_PLUGIN_NAME')){
			define('BOOKI_PLUGIN_NAME', basename(dirname( __FILE__ )) . '/index.php');
		}
		
		if(!defined('BOOKI_DATEFORMAT')){
			define('BOOKI_DATEFORMAT', 'Y-m-d');
		}
		
		add_action('after_setup_theme', array($this, 'init'));
		require_once  dirname(__FILE__) . '/lib/base/DateTime.php';
		require_once  dirname(__FILE__) . '/lib/infrastructure/actions/Register.php';
		require_once  dirname(__FILE__) . '/lib/infrastructure/actions/Admin.php';
		require_once  dirname(__FILE__) . '/lib/infrastructure/session/Bookings.php';
		
		if(!class_exists('MailChimp')){
			require_once  BOOKI_MAILCHIMP . 'Mailchimp.php';
		}
		
		if (!session_id()) {
			session_start();
		}

		register_activation_hook( __FILE__, array('Booki_Admin', 'install'));
		register_deactivation_hook( __FILE__, array('Booki_Admin', 'deactivate'));
	}
	
	public function init(){
		load_plugin_textdomain( 'booki', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		//WARNING: DO NOT CHANGE RESTRICTED MODE
		if(!defined('BOOKI_RESTRICTED_MODE')){
			define('BOOKI_RESTRICTED_MODE',  false);
		}
		$this->sessionTest();
		
		if (is_admin() ) {
			new Booki_Admin();
		} else{
			new Booki_Register();
		}
		
		if(BOOKI_RESTRICTED_MODE){
			if (!(defined('DOING_AJAX') && DOING_AJAX)) {
				require_once dirname(__FILE__) . '/lib/infrastructure/actions/ResetAppJob.php';
				new Booki_ResetAppJob();
			}
		}
		
		$globalSettings = Booki_Helper::globalSettings();
		if($globalSettings->noCache){
			Booki_Helper::noCache();
		}
		
		$payPalSettings = Booki_Helper::paypalSettings();
		if($payPalSettings->useSandBox){
			if(!defined('BOOKI_PP_CONFIG_PATH')){
				define('BOOKI_PP_CONFIG_PATH',  BOOKI_PAYPAL_MERCHANT_SDK . 'config/debug/');
			}
		}
	}
	
	public function sessionTest(){
		if (isset($_SESSION['Booki_Last_Activity']) && (time() - $_SESSION['Booki_Last_Activity'] > 1800)) {
			if(isset($_SESSION['Booki_Bookings'])){
				unset($_SESSION['Booki_Bookings']);
			}
			if(isset($_SESSION['Booki_MailChimpList'])){
				unset($_SESSION['Booki_MailChimpList']);
			}
		}
		$_SESSION['Booki_Last_Activity'] = time();
	}
}

new Booki();


?>