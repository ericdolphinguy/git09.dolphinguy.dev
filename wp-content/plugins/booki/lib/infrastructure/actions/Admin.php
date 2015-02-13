<?php
	require_once dirname(__FILE__) . '/../ui/lists/ImageList.php';
	require_once dirname(__FILE__) . '/../utils/ThemeHelper.php';
	require_once dirname(__FILE__) . '/../utils/PageNames.php';
	require_once dirname(__FILE__) . '/../utils/WPMLHelper.php';
	require_once dirname(__FILE__) . '/../widgets/BasketWidget.php';
	require_once dirname(__FILE__) . '/../widgets/BookingsListWidget.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/OrderRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/CouponRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/UserRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/EventsLogRepository.php';
	require_once dirname(__FILE__) . '/../ajax/CalendarBridge.php';
	require_once dirname(__FILE__) . '/../ajax/CalendarDayBridge.php';
	require_once dirname(__FILE__) . '/../ajax/CalendarDaysBridge.php';
	require_once dirname(__FILE__) . '/../ajax/FormElementBridge.php';
	require_once dirname(__FILE__) . '/../ajax/OptionalBridge.php';
	require_once dirname(__FILE__) . '/../ajax/ProjectBridge.php';
	require_once dirname(__FILE__) . '/../ajax/TimeBuilderBridge.php';
	require_once dirname(__FILE__) . '/../ajax/UserInfoBridge.php';
	require_once dirname(__FILE__) . '/../ajax/TimezoneControlBridge.php';
	require_once dirname(__FILE__) . '/../ajax/CreateTimeSlotsBridge.php';
	require_once dirname(__FILE__) . '/../ajax/CascadingListBridge.php';
	require_once dirname(__FILE__) . '/../ajax/CascadingItemBridge.php';
	class Booki_Admin{
		const scriptFolder = 'assets/admin/scripts/';
		const frontEndScriptFolder = 'assets/scripts/';
		const cssFolder = 'assets/admin/css/';
		const frontEndCssFolder = 'assets/css/';
		private $registerScripts;
		private $globalSettings;
		public function __construct(){
			$this->registerScripts = isset($_GET['page']) ? strpos($_GET['page'], 'booki/') === 0 : false;
			$this->globalSettings = Booki_Helper::globalSettings();
			
			add_action('admin_init', array($this, 'adminInit'));
			add_action( 'init', array($this, 'init'));
			add_action('wp_head', array($this, 'metaData'));
			
			add_action('admin_menu', array($this, 'menu'));
			if($this->registerScripts){
				add_action('admin_enqueue_scripts', array($this, 'cssIncludes'));
				add_action('admin_enqueue_scripts', array($this, 'jsIncludes'));
			}
			add_action('wp_ajax_mediaLibraryPaging', array($this, 'mediaLibraryPagingCallback'));
			add_action('load-profile.php', array($this, 'disableUserProfile'));
		}
		
		
		public function adminInit() {
			global $wp_version;

			// all admin functions are disabled in old versions
			if ( version_compare( $wp_version, '3.0', '<' ) ) {
				add_action('admin_notices', array($this, 'wpVersionWarning' ) );
			}
			
			$handlers = array('bookingscsvgen', 'userscsvgen', 'couponscsvgen');
			$pageIdentifier = isset($_GET['booki_handler']) ? $_GET['booki_handler'] : '';
			if(in_array($pageIdentifier, $handlers)){
				require_once dirname(__FILE__) . '/../../gen/' . $pageIdentifier . '.php';
				exit();
			}
			
			new Booki_CalendarBridge();
			new Booki_CalendarDayBridge();
			new Booki_CalendarDaysBridge();
			new Booki_FormElementBridge();
			new Booki_OptionalBridge();
			new Booki_ProjectBridge();
			new Booki_TimeBuilderBridge();
			new Booki_UserInfoBridge();
			new Booki_TimezoneControlBridge();
			new Booki_CreateTimeSlotsBridge();
			new Booki_CascadingListBridge();
			new Booki_CascadingItemBridge();
			require_once dirname(__FILE__) . '/../templates/BookingFormTmpl.php';
			require_once dirname(__FILE__) . '/../templates/CustomFormTmpl.php';
			require_once dirname(__FILE__) . '/../templates/OptionalsFormTmpl.php';
			require_once dirname(__FILE__) . '/../templates/CascadingDropdownListTmpl.php';
			require_once dirname(__FILE__) . '/../templates/BookingWizardTmpl.php';
			require_once dirname(__FILE__) . '/../templates/TimezoneControlTmpl.php';
			require_once dirname(__FILE__) . '/../templates/CheckoutGridTmpl.php';
			require_once dirname(__FILE__) . '/../templates/CartTmpl.php';
			require_once dirname(__FILE__) . '/../templates/StatsTmpl.php';
			require_once dirname(__FILE__) . '/../templates/HistoryTmpl.php';
			
			$postId = null;
			if (isset($_GET['post'] )){
				$postId = $_GET['post'];
			}else if ( isset ( $_POST['post_ID'])){
				$postId = $_POST['post_ID'];
			}
			if($postId === null){
				return;
			}
		}
		
		public function wpVersionWarning() {
			echo '
			<div class="update-nag"><p><strong> ' .__('Booki has been tested to work with WordPress 3.0 or higher. We recommend you upgrade.') . '</strong> ' . sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress') . '</p></div>
			';
		}
		function disableUserProfile() {
			if(BOOKI_RESTRICTED_MODE){
				$url = admin_url() . 'admin.php?page=booki/index.php';
				wp_redirect($url);
			}
		}
		
		public function menu() {
			if ( function_exists('add_menu_page') ){
				$suffixList = array();
				
				$isAdmin = Booki_Helper::userHasRole('administrator');
				
				$projectRepo = new Booki_ProjectRepository();
				$orderRepo = new Booki_OrderRepository();
				$couponRepo = new Booki_CouponRepository();
				$userRepo = new Booki_UserRepository();
				$eventsLogRepo = new Booki_EventsLogRepository();
				
				$countLabel = '&nbsp;&nbsp;<span class="update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>';
				
				$projectsCount = sprintf($countLabel, $isAdmin ? $projectRepo->count() : 0);
				$bookingsCount = sprintf($countLabel, $isAdmin ? $orderRepo->count() : 0);
				$couponsCount = sprintf($countLabel, $isAdmin ? $couponRepo->count() : 0);
				$usersCount = sprintf($countLabel, $isAdmin ? $userRepo->count() : 0);
				$eventsLogCount = sprintf($countLabel, $isAdmin ? $eventsLogRepo->count() : 0);
				
				$user = wp_get_current_user();
				$adminCapability ='administrator';
				$manageBookingsCapability = 'administrator';
				$userHistoryCapability =  'administrator';
				
				if(BOOKI_RESTRICTED_MODE){
					$adminCapability = 'subscriber';
					$manageBookingsCapability = 'subscriber';
					$userHistoryCapability = 'subscriber';
					
					remove_menu_page('profile.php');        
				}else{
					if (Booki_Helper::userHasRole('editor')){
						$manageBookingsCapability = 'editor';
					}
					$userHistoryCapability = $user->roles[0];
				}
				
				array_push($suffixList,
					add_menu_page(
						__('Booki.io - A Booking Application from Mars', 'booki'), 
						'Booki'. $bookingsCount, 
						$adminCapability, 
						'booki/index.php', 
						array($this, 'registerCreateProjectsPage'),   
						BOOKI_PLUGINDIR . 'assets/admin/images/icon16x16.png'
				));
				
				
				//duplicate the main menu
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Create/Edit new or existing booking projects', 'booki'), 
						__('Projects', 'booki') . $projectsCount, 
						$adminCapability, 
						'booki/index.php', 
						array($this, 'registerCreateProjectsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage bookings made', 'booki'), 
						__('Bookings', 'booki') . $bookingsCount, 
						$manageBookingsCapability, 
						'booki/managebookings.php', 
						array($this,  'registerManageBookingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/managebookings.php', 
						__('Create new booking manually', 'booki'), 
						__('New bookings', 'booki'), 
						$manageBookingsCapability, 
						'booki/createbookings.php', 
						array($this, 'registerCreateBookingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage discount coupons', 'booki'), 
						__('Coupons', 'booki') . $couponsCount, 
						$adminCapability, 
						'booki/coupons.php', 
						array($this,  'registerCouponsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('Manage users', 'booki'), 
						__('Users', 'booki') . $usersCount, 
						$adminCapability, 
						'booki/users.php', 
						array($this,  'registerUsersPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						'Paypal', 
						'Paypal', 
						$adminCapability, 
						'booki/paypal.php', 
						array($this, 'registerPaypalGatewayPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Email Settings', 'booki'), 
						__('Email Settings', 'booki'), 
						$adminCapability, 
						'booki/emailsettings.php', 
						array($this, 'registerEmailSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Invoice Settings', 'booki'), 
						__('Invoice Settings', 'booki'), 
						$adminCapability, 
						'booki/invoicesettings.php', 
						array($this, 'registerInvoiceSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Edit string resources found in the booking form', 'booki'), 
						__('String Resources', 'booki'), 
						$adminCapability, 
						'booki/resources.php', 
						array($this, 'registerResourcesPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('General Settings', 'booki'), 
						__('General Settings', 'booki'), 
						$adminCapability, 
						'booki/generalsettings.php', 
						array($this, 'registerGeneralSettingsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('User history --past bookings etc', 'booki'), 
						__('History', 'booki'), 
						$userHistoryCapability, 
						'booki/userhistory.php', 
						array($this, 'registerUserHistoryPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Logs errors returned by Paypal, Mailchimp and Email failures', 'booki'), 
						__('Event log', 'booki') . $eventsLogCount, 
						$adminCapability, 
						'booki/eventslog.php', 
						array($this, 'registerEventsLogPage')
				));
				
				//duplicate the main menu
				array_push($suffixList, 
					add_submenu_page(
						'booki/index.php', 
						__('General statistics', 'booki'), 
						__('Stats', 'booki'), 
						$manageBookingsCapability, 
						'booki/stats.php', 
						array($this, 'registerCreateStatsPage')
				));
				
				array_push($suffixList, 
					add_submenu_page( 
						'booki/index.php', 
						__('Uninstall', 'booki'), 
						__('Uninstall', 'booki'), 
						$adminCapability, 
						'booki/uninstall.php', 
						array($this, 'registerUninstallPage')
				));
			}
			global $submenu;
			unset($submenu['edit.php?post_type=booki'][10]);
		}
		
		public function registerCreateProjectsPage(){
			require_once  dirname(__FILE__) . '/../../views/createprojects.php';
		}
		
		public function registerManageBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/managebookings.php';
		}
		
		public function registerCreateStatsPage(){
			require_once  dirname(__FILE__) . '/../../views/stats.php';
		}
		
		public function registerCreateBookingsPage(){
			require_once  dirname(__FILE__) . '/../../views/createbookings.php';
		}
		
		public function registerCouponsPage(){
			require_once  dirname(__FILE__) . '/../../views/managecoupons.php';
		}
		
		public function registerUsersPage(){
			require_once  dirname(__FILE__) . '/../../views/manageusers.php';
		}
		
		public function registerPayPalGatewayPage(){
			require_once  dirname(__FILE__) . '/../../views/paypal.php';
		}
		public function registerResourcesPage(){
			require_once  dirname(__FILE__) . '/../../views/resources.php';
		}
		public function registerGeneralSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/generalsettings.php';
		}
		
		public function registerInvoiceSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/invoicesettings.php';
		}
		
		public function registerEmailSettingsPage(){
			require_once  dirname(__FILE__) . '/../../views/emailsettings.php';
		}
		
		public function registerUninstallPage(){
			require_once  dirname(__FILE__) . '/../../views/uninstall.php';
		}
		
		public function registerUserHistoryPage(){
			require_once  dirname(__FILE__) . '/../../views/userhistory.php';
		}
		
		public function registerEventsLogPage(){
			require_once  dirname(__FILE__) . '/../../views/eventslog.php';
		}
		
		public function init() {
			self::registerResources();
			$this->registerCustomPages();
		}
		
		protected function registerCustomPages(){
			$args = array( 
				'meta_key'=>'booki_page_type'
				, 'hierarchical' => 0
			);
			$pages = get_pages($args);
			
			$pageAttributes = array(
				array('pageType'=>Booki_PageNames::CART, 'title'=>'Booki - Cart', 'content'=>'[booki-cart]')
				, array('pageType'=>Booki_PageNames::PAYPAL_HANDLER, 'title'=>'Booki - Billing', 'content'=>'[booki-bill]')
				, array('pageType'=>Booki_PageNames::PAYPAL_CONFIRMATION_HANDLER, 'title'=>'Booki - Paypal payment confirmation', 'content'=>'[booki-ppconfirmation]')
				, array('pageType'=>Booki_PageNames::PAYPAL_CANCEL_HANDLER, 'title'=>'Booki - Paypal payment cancel', 'content'=>'[booki-ppcancel]')
				, array('pageType'=>Booki_PageNames::BOOKING_VIEW, 'title'=>'Booki - List item', 'content'=>'[booki-itemdetails]')
				, array('pageType'=>Booki_PageNames::HISTORY_PAGE, 'title'=>'Booki - History', 'content'=>'[booki-history]')
				, array('pageType'=>Booki_PageNames::STATS_PAGE, 'title'=>'Booki - Stats', 'content'=>'[booki-stats]')
			);
			
			$args = array(
				'post_title'=>''
				, 'post_content'=>''
				, 'post_type'=>'page'
				, 'post_status'=>'publish'
				, 'show_ui'=>false
				, 'show_in_menu' =>false
				, 'show_in_admin_bar'=>false
				, 'comment_status'=> 'closed'
				, 'ping_status'=>'closed'
			);

			foreach($pageAttributes as $attrs){
				if(!$this->hasPage($pages, $attrs['pageType'])){
					$args['post_title'] = $attrs['title'];
					$args['post_content'] = $attrs['content'];
					$id = wp_insert_post( $args);
					add_post_meta($id, 'booki_page_type', $attrs['pageType']);
				}
			}
		}
		
		protected function hasPage($pages, $pageId){
			foreach($pages as $page){
				if((int)$page->booki_page_type === $pageId){
					return true;
				}
			}
			return false;
		}
		
		public function cssIncludes(){
			$pathFrontEnd = BOOKI_PLUGINDIR . self::frontEndCssFolder;
			$pathBackEnd = BOOKI_PLUGINDIR . self::cssFolder;
			
			wp_enqueue_style('bootstrap', $pathBackEnd . 'bootstrap.debug.css');
			wp_enqueue_style('jquery-ui-smoothness', $pathBackEnd . 'jquery-ui/smoothness/jquery-ui-1.10.3.custom.css');
			wp_enqueue_style('jquery.minicolors', $pathBackEnd . 'minicolors/jquery.minicolors.css');
			
			if($this->globalSettings->debugMode){
				wp_enqueue_style('booki-frontend', $pathFrontEnd . 'booki.debug.css');
				wp_enqueue_style('booki-backend', $pathBackEnd . 'booki.admin.debug.css');
			}else{
				wp_enqueue_style('booki-frontend', $pathFrontEnd . 'booki.min.css');
				wp_enqueue_style('booki-backend', $pathBackEnd . 'booki.admin.min.css');
			}
		}
		
		public function jsIncludes(){
			$pathFrontEnd = BOOKI_PLUGINDIR . self::frontEndScriptFolder;
			$pathBackEnd = BOOKI_PLUGINDIR . self::scriptFolder;
			wp_enqueue_script('jquery', array(), '', true);
			wp_enqueue_script('json2', array(), '', true);
			wp_enqueue_script('jquery-ui-datepicker', array(), '', true);
			wp_enqueue_script('underscore', array(), '', true);
			wp_enqueue_script('backbone', array(), true);
			wp_enqueue_script('moment', $pathBackEnd . 'moment.min.js', array(), '', true);
			wp_enqueue_script('bootstrap', $pathBackEnd . 'bootstrap/bootstrap.min.js', array(), '', true);
			wp_enqueue_script('jquery.minicolors', $pathBackEnd . 'jquery.minicolors.js', array(), '', true);
			wp_enqueue_script('parsely', $pathBackEnd . 'parsley.min.js', array(), '', true);
			wp_enqueue_script('accounting', $pathBackEnd . 'accounting.min.js', array(), '', true);
			wp_enqueue_script('jsTimezoneDetect', $pathBackEnd . 'jstz.min.js', array(), '', true);
			if($this->globalSettings->debugMode){
				wp_enqueue_script('booki-frontend', $pathFrontEnd . 'booki.debug.js', array(), '', true);
				wp_enqueue_script('booki-backend', $pathBackEnd . 'booki.admin.debug.js', array(), '', true);
			}else{
				wp_enqueue_script('booki-frontend', $pathFrontEnd . 'booki.1.0.min.js', array(), '', true);
				wp_enqueue_script('booki-backend', $pathBackEnd . 'booki.admin.1.0.min.js', array(), '', true);
			}
			Booki_ScriptHelper::enqueueDatePickerLocale();
			Booki_ScriptHelper::enqueueParsleyLocale();
		}
		
		private static function readText($path){
			$content = '';
			if ($handle = fopen($path, 'rb')) {
				$len = filesize($path);
				if ($len > 0){
					$content = fread($handle, $len);
				}
				fclose($handle);
			}
			return trim($content);
		}
		
		private static function getSqlScript($file_name){
			$path = BOOKI_ROOT  . '/assets/sql/' . $file_name;
			return Booki_Helper::readText($path);
		}
		
		public static function install(){
			global $wpdb;
			$dbVersion = floatval(get_option('booki_db_version'));
			$scriptFileName = 'create_booki.sql';
			$sql = null;
			$newVersion = floatval(self::getSqlScript('version.txt'));
			try{
				if($dbVersion !== $newVersion){
					//clear out pages, they will get freshly regenerated.
					self::deleteAllPages();
					self::clearSessions();
					$sql = self::getSqlScript($scriptFileName);
					if($sql){
						$sql = str_replace('prefix_', $wpdb->prefix, $sql);
						require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
						dbDelta($sql);
						update_option('booki_db_version', $newVersion);
					}
				}
				
			} catch (Exception $e) {
				update_option('booki_db_error', $e->getMessage());
			}
		}
		
		
		public static function uninstall(){
			global $wpdb;
			$sql = self::getSqlScript('drop_booki.sql');
			
			if(!$sql){
				return;
			}

			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);
			$length = count($statements) - 1;

			try{
				self::deleteResources();
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
				delete_option('booki_db_version');
				delete_option('booki_db_error');
				
				self::deleteAllPages();
				self::clearSessions();
			
				include_once( ABSPATH . 'wp-admin/includes/plugin.php');
				if(is_plugin_active(BOOKI_PLUGIN_NAME)) {
					deactivate_plugins(BOOKI_PLUGIN_NAME);
					wp_redirect(admin_url('plugins.php?deactivate=true&plugin_status=all&paged=1'));
					exit();
				}
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		
		public static function deleteAllPages(){
			$args = array( 
				'meta_key'=>'booki_page_type'
				, 'hierarchical' => 0
			);
			
			$pages = get_pages($args);
			if(count($pages) > 0){
				foreach( $pages as $page ) {
					wp_delete_post( $page->ID, true);
				}
			}
		}
		public static function deactivate(){
			wp_clear_scheduled_hook('Booki_ResetAppJobEventHook');
			wp_clear_scheduled_hook('Booki_ExpiredBookingsJobEventHook');
			wp_clear_scheduled_hook('Booki_ExpiredEventsLogJobEventHook');
			self::deleteAllPages();
			self::clearSessions();
		}
		
		public static function registerResources(){
			$resx = Booki_Helper::resx();
			$resx->updateResources();
			Booki_WPMLHelper::registerEmailResource();
		}
		
		public static function deleteResources(){
			//delete wpml resources
			$resx = Booki_Helper::resx();
			$resx->deleteResources();
			
			$projectRepo = new Booki_ProjectRepository();
			$projects = $projectRepo->readAll();
			foreach($projects as $project){
				$project->deleteResources();
			}
			
			$templateNames = Booki_Helper::systemEmails();
			foreach($templateNames as $templateName){
				Booki_WPMLHelper::unregister('email_template_' . str_replace(' ', '_', $templateName));
			}
		}
		
		public static function clearDatabase(){
			global $wpdb;
			$sql = self::getSqlScript('clear_booki.sql');
			self::clearSessions();
			
			if(!$sql){
				return;
			}
			
			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);

			$length = count($statements) - 1;
			try{
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		public static function myISAMReady(){
			global $wpdb;
			$sql = self::getSqlScript('myisam_booki.sql');
			
			if(!$sql){
				return;
			}
			
			$sql = str_replace('prefix_', $wpdb->prefix, $sql);
			$statements = explode(';', $sql);

			$length = count($statements) - 1;
			try{
				for($i = 0; $i < $length; $i++){
					$stmt = $statements[$i];
					$wpdb->query($stmt);
				}	
			} catch (Exception $e) {
				 add_option('booki_db_error', $e->getMessage());
			}
		}
		
		public static function clearSessions(){
			if(isset($_SESSION['Booki_Bookings'])){
				unset($_SESSION['Booki_Bookings']);
			}
			if(isset($_SESSION['Booki_MailChimpList'])){
				unset($_SESSION['Booki_MailChimpList']);
			}
		}
		
		public function mediaLibraryPagingCallback(){
			//required workaround for ajax
			$GLOBALS['hook_suffix'] = '';
			set_current_screen();
			//now get your image listing.
			$imageList = new Booki_ImageList();
			$imageList->prepare_items();
			$imageList->display();
			die();
		}
		
		public function metaData(){
			echo '<meta name="plugins" content="POWERED BY BOOKI ' . BOOKI_VERSION . '. A BOOKING PLUGIN FOR WORDPRESS." />';
		}
	}
?>
