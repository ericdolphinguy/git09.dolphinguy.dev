<?php
	require_once 'ExpiredEventsLogJob.php';
	require_once 'ExpiredBookingsJob.php';
	require_once 'Render.php';
	require_once dirname(__FILE__) . '/../session/Cart.php';
	require_once dirname(__FILE__) . '/../utils/ThemeHelper.php';
	require_once dirname(__FILE__) . '/../utils/ScriptHelper.php';
	require_once dirname(__FILE__) . '/../utils/PageNames.php';
	require_once dirname(__FILE__) . '/../ajax/TimeBuilderBridge.php';
	require_once dirname(__FILE__) . '/../ui/builders/MiniCartBuilder.php';
	require_once dirname(__FILE__) . '/../ui/builders/SettingsGlobalBuilder.php';
	require_once dirname(__FILE__) . '/../widgets/BasketWidget.php';
	require_once dirname(__FILE__) . '/../widgets/BookingsListWidget.php';
	
class Booki_Register{
    
    const scriptFolder = 'assets/scripts/';
    const cssFolder = 'assets/css/';
	private $pages;
	private $globalSettings;
	public function __construct(){
		$this->globalSettings = Booki_Helper::globalSettings();
		$args = array( 
			'meta_key'=>'booki_page_type'
			, 'hierarchical' => 0
		);
		$this->pages = get_pages($args);

		add_action('init', array($this, 'init'));
		add_action('wp_head', array($this, 'metaData'));
		add_action('wp_enqueue_scripts', array($this, 'cssIncludes'), 99);
		add_action('wp_enqueue_scripts', array($this, 'jsIncludes'));
		//[booki id="2"]
		add_shortcode( 'booki-booking', array($this, 'processShortCodeBooking'));
		add_shortcode( 'booki-list', array($this, 'processShortCodeList'));
		add_shortcode( 'booki-basket', array($this, 'processShortCodeBasket'));

		add_shortcode( 'booki-cart', array($this, 'processShortCodeCart'));
		add_shortcode( 'booki-bill', array($this, 'processShortCodeBillSettlement'));
		add_shortcode( 'booki-ppconfirmation', array($this, 'processShortCodePayPalConfirmation'));
		add_shortcode( 'booki-ppcancel', array($this, 'processShortCodePayPalCancel'));
		add_shortcode( 'booki-itemdetails', array($this, 'processShortCodeItemDetails'));
		add_shortcode( 'booki-history', array($this, 'processShortCodeHistory'));
		add_shortcode( 'booki-stats', array($this, 'processShortCodeStats'));

		add_filter('wp_get_nav_menu_items', array($this, 'excludeFromRegisteredMenu'), 10, 3);
		add_filter('wp_page_menu_args', array($this, 'excludeFromDefaultMenu'), 10, 1);
		//add_action('user_register', array($this, 'registrationComplete'), 10, 1);
		//add_action('wp_login', array($this, 'loginComplete'), 10, 2);
		
		add_filter('register', array($this, 'customRegisterUrl'));
		add_filter('registration_redirect', array($this, 'customRegistrationRedirect'));
	
		if (!(defined('DOING_AJAX') && DOING_AJAX)) {
			new Booki_ExpiredBookingsJob();
			new Booki_ExpiredEventsLogJob();
		}
		new Booki_TimeBuilderBridge();
		new Booki_TimezoneControlBridge();
	}
	
	public function customRegisterUrl( $registrationUrl ) {
		$redirectTo = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '';
		$flag = isset($_GET['booki']) ? $_GET['booki'] : '';
		if($redirectTo && $flag) {
			// change query name values to prevent default behaviour (redirect_to to uct_redirect)
			$registrationUrl = sprintf( '<a href="%s&%s">%s</a>', esc_url( wp_registration_url() ), 'uct_redirect=' . urlencode($redirectTo),  __('Register'));
		}
		
		return $registrationUrl;
	}

	public function customRegistrationRedirect($registrationRedirect) {
		$redirectTo = isset($_GET['uct_redirect']) ? $_GET['uct_redirect'] : '';
		$flag = isset($_GET['booki']) ? $_GET['booki'] : '';
		if($redirectTo && $flag) {
			$registrationRedirect = wp_login_url( $redirectTo );
		}

		return $registrationRedirect;
	}
	

	function loginComplete($userLogin, $user){}

	function registrationComplete($userId) {
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$cartEmpty = $bookings->count() === 0;
		
		if($this->globalSettings->autoLoginAfterRegistration){
			wp_set_auth_cookie( $userId, false, is_ssl() );
			if($cartEmpty){
				wp_safe_redirect(home_url('/'));
				exit;
			}
		}
		
		if($this->globalSettings->autoLoginAfterRegistration && ($this->globalSettings->useCartSystem && !$cartEmpty)){
			Booki_Helper::redirect(Booki_PageNames::CART);
			exit;
		}
	}
	
	protected function includeTemplates(){
		require_once dirname(__FILE__) . '/../templates/BookingFormTmpl.php';
		require_once dirname(__FILE__) . '/../templates/BillSettlementTmpl.php';
		require_once dirname(__FILE__) . '/../templates/CartTmpl.php';
		require_once dirname(__FILE__) . '/../templates/CheckoutGridTmpl.php';
		require_once dirname(__FILE__) . '/../templates/CustomFormTmpl.php';
		require_once dirname(__FILE__) . '/../templates/PaypalProcessPaymentTmpl.php';
		require_once dirname(__FILE__) . '/../templates/PaypalCancelPaymentTmpl.php';
		require_once dirname(__FILE__) . '/../templates/MasterTmpl.php';
		require_once dirname(__FILE__) . '/../templates/MiniCartTmpl.php';
		require_once dirname(__FILE__) . '/../templates/OptionalsFormTmpl.php';
		require_once dirname(__FILE__) . '/../templates/CascadingDropdownListTmpl.php';
		require_once dirname(__FILE__) . '/../templates/ListTmpl.php';
		require_once dirname(__FILE__) . '/../templates/BookingViewTmpl.php';
		require_once dirname(__FILE__) . '/../templates/BookingWizardTmpl.php';
		require_once dirname(__FILE__) . '/../templates/TimezoneControlTmpl.php';
		require_once dirname(__FILE__) . '/../templates/StatsTmpl.php';
		require_once dirname(__FILE__) . '/../templates/HistoryTmpl.php';
	}
	
	public function excludeFromDefaultMenu($args){
		$excludes = array();
		foreach($this->pages as $page){
			array_push($excludes, $page->ID);
		}
		$args['exclude'] = implode(',', $excludes);
		return $args;
	}

	public function excludeFromRegisteredMenu( $items, $menu, $args ) {
		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {
			foreach($this->pages as $page){
				if ( $item->object_id === $page->ID ) {
					unset( $items[$key] );
				}
			}
		}
		return $items;
	}

	public function init(){
		ob_start();
		$handlers = array('invoicegen');
		$pageIdentifier = isset($_GET['booki_handler']) ? $_GET['booki_handler'] : '';
		if(in_array($pageIdentifier, $handlers)){
			require_once dirname(__FILE__) . '/../../gen/' . $pageIdentifier . '.php';
			exit();
		}
		$this->includeTemplates();
	}
	
	function processShortCodeBooking( $atts, $content = null ) {
		extract( shortcode_atts( array( 'id' => '-1' ), $atts ) );
		$id = intval($id);
		
		$render = new Booki_Render();
		return $render->booking($id);
	}
	
	function processShortCodeList( $atts, $content = null ) {
		$listArgs = array(
			'tags'=>isset($atts['tags']) ? $atts['tags'] : ''
			, 'heading'=>isset($atts['heading']) ? $atts['heading'] : __('Find a booking', 'booki')
			, 'fromLabel'=>isset($atts['fromlabel']) ? $atts['fromlabel'] : __('Check-in', 'booki')
			, 'toLabel'=>isset($atts['tolabel']) ? $atts['tolabel'] : __('Check-out', 'booki')
			, 'perPage'=>isset($atts['perpage']) ? $atts['perpage'] : 5
			, 'fullPager'=>isset($atts['fullpager']) ? filter_var($atts['fullpager'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableSearch'=>isset($atts['enablesearch']) ? filter_var($atts['enablesearch'], FILTER_VALIDATE_BOOLEAN) : true
			, 'enableItemHeading'=>isset($atts['enableitemheading']) ? filter_var($atts['enableitemheading'], FILTER_VALIDATE_BOOLEAN) : false
		);
		
		$render = new Booki_Render();
		return $render->bookingList($listArgs);
	}
	
	function processShortCodeBasket($atts, $content = null){
		$render = new Booki_Render();
		return $render->basket();
	}
	
	public function processShortCodeCart(){
		$render = new Booki_Render();
		return $render->cart();
	}

	public function processShortCodeBillSettlement(){
		$render = new Booki_Render();
		return $render->payPalBillSettlement();
	}

	public function processShortCodePayPalConfirmation(){
		$render = new Booki_Render();
		return $render->payPalPaymentConfirmation();
	}

	public function processShortCodePayPalCancel(){
		$render = new Booki_Render();
		return $render->payPalPaymentCancel();
	}

	public function processShortCodeItemDetails(){
		$render = new Booki_Render();
		return $render->bookingItemDetails();
	}
	
	public function processShortCodeHistory(){
		$render = new Booki_Render();
		return $render->historyPage();
	}
	
	public function processShortCodeStats(){
		$render = new Booki_Render();
		return $render->statsPage();
	}
	
    public function cssIncludes(){
		$bootstrapRoot =  BOOKI_PLUGINDIR;
		$bookiFrontEndImport = null;
		$calendarThemeRoot = BOOKI_PLUGINDIR;
		$calendarStyleSheet = sprintf('jquery-ui/%s/jquery-ui-1.10.3.custom.css', $this->globalSettings->calendarTheme);
		
		if($this->globalSettings->theme !== '-1'){
			$dirUri = get_stylesheet_directory_uri() . '/booki/' . $this->globalSettings->theme . '/';
			$dir = get_stylesheet_directory() . '/booki/' . $this->globalSettings->theme . '/';

			if(file_exists($dir . self::cssFolder . 'bootstrap.min.css')){
				$bootstrapRoot = $dirUri;
			}
			
			if(file_exists($dir . self::cssFolder . 'booki.import.css')){
				$bookiFrontEndImport = $dirUri;
			}
		}
		
		//calendar is in the booki root directory i.e. /booki/assets/css and not /booki/themename/assets/css
		if(file_exists(get_stylesheet_directory() . '/booki/' . self::cssFolder . $calendarStyleSheet)){
			$calendarThemeRoot = get_stylesheet_directory_uri() . '/booki/';
		} else if (!file_exists(dirname(__FILE__) . '/../../../' . self::cssFolder . $calendarStyleSheet)){
			//calendar theme referenced has been deleted or moved ? Use this default theme then.
			$calendarStyleSheet = 'jquery-ui/smoothness/jquery-ui-1.10.3.custom.css';
		}
			
		if($this->globalSettings->refBootstrapStyleSheet){
			wp_enqueue_style( 'booki-bootstrap', $bootstrapRoot . self::cssFolder . 'bootstrap.min.css');
		}
		
		if($this->globalSettings->calendarTheme){
			wp_enqueue_style( 'jquery-ui-' . $this->globalSettings->calendarTheme, $calendarThemeRoot . self::cssFolder . $calendarStyleSheet);
		}
		if($this->globalSettings->debugMode){
			wp_enqueue_style( 'booki-frontend', BOOKI_PLUGINDIR . self::cssFolder . 'booki.debug.css' . '?booki=' . BOOKI_VERSION);
		} else{
			wp_enqueue_style( 'booki-frontend', BOOKI_PLUGINDIR . self::cssFolder . 'booki.min.css' . '?booki=' . BOOKI_VERSION);
		}
		if($bookiFrontEndImport !== null){
			wp_enqueue_style( 'booki-frontend-import', $bookiFrontEndImport . self::cssFolder . 'booki.import.css');
		}
    }
    
    public function jsIncludes(){
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		
		Booki_ScriptHelper::enqueueDatePickerLocale();
		
		wp_enqueue_script( 'moment', BOOKI_PLUGINDIR . self::scriptFolder . 'moment.min.js');
		if($this->globalSettings->refBootstrapJS){
			wp_enqueue_script( 'booki-bootstrap', BOOKI_PLUGINDIR . self::scriptFolder . 'bootstrap/bootstrap.min.js');
		}
		
		wp_enqueue_script( 'parsely', BOOKI_PLUGINDIR . self::scriptFolder . 'parsley.min.js');
		wp_enqueue_script( 'accounting', BOOKI_PLUGINDIR . self::scriptFolder . 'accounting.min.js');
		wp_enqueue_script( 'jsTimezoneDetect', BOOKI_PLUGINDIR . self::scriptFolder . 'jstz.min.js');

		if($this->globalSettings->debugMode){
			wp_enqueue_script( 'booki-frontend', BOOKI_PLUGINDIR . self::scriptFolder . 'booki.debug.js' . '?booki=' . BOOKI_VERSION);
		} else{
			wp_enqueue_script( 'booki-frontend', BOOKI_PLUGINDIR . self::scriptFolder . 'booki.1.0.min.js' . '?booki=' . BOOKI_VERSION);
		}
		
		Booki_ScriptHelper::enqueueParsleyLocale();
    }
	
	public function metaData(){
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
	}
}
?>