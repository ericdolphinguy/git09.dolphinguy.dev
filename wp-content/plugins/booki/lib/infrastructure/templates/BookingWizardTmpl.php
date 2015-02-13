<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/../session/Cart.php';
require_once dirname(__FILE__) . '/../../controller/WizardController.php';
require_once dirname(__FILE__) . '/../ui/builders/OrderSummaryBuilder.php';
require_once  dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
class Booki_BookingWizardTmpl{
	public $projectId;
	public $data;
	public $project;
	public $isBackEnd;
	public $steps = array();
	public $goToCartUrl;
	public $orderHistoryUrl;
	public $cartEmpty;
	public $globalSettings;
	public $checkoutSuccessMessage;
	public $orderId = null;
	public $errors = array();
	public $resx;
	public function __construct($hasCustomFormFields){
		$this->projectId = apply_filters( 'booki_shortcode_id', null);
		if($this->projectId === null || $this->projectId === -1){
			$this->projectId = apply_filters( 'booki_project_id', null);
		}
		$this->isBackEnd = apply_filters( 'booki_is_backend', null);
		$repo = new Booki_ProjectRepository();
		$this->project = $repo->read($this->projectId);
		
		
		$defaultStep = $this->project->defaultStep;
		
		if($hasCustomFormFields){
			array_push($this->steps, 
			array(
					'id'=>'bookingtab' . $this->projectId
					, 'step'=>0
					, 'defaultStep'=>$defaultStep
					, 'name'=>$this->project->bookingTabLabel_loc
			));
		
			array_push($this->steps, 
			array(
					'id'=>'detailstab' . $this->projectId
					, 'step'=>1
					, 'defaultStep'=>$defaultStep
					, 'name'=>$this->project->customFormTabLabel_loc
			));
			
			usort($this->steps, array($this, 'sortByDefaultStep'));
		}

		$this->globalSettings = Booki_Helper::globalSettings();
		$this->resx = Booki_Helper::resx();
		
		$postProjectId = isset($_POST['projectid']) ? (int)$_POST['projectid'] : -1;
		if($this->projectId === $postProjectId){
			new Booki_WizardController(array($this, 'addToCartCallback'), array($this, 'checkoutCallback'));
		}
		$orderBuilder = new Booki_OrderSummaryBuilder();
		$this->data = $orderBuilder->result;
		
		$cart = new Booki_Cart();
		$bookings = $cart->getBookings();
		$this->cartEmpty = $bookings->count() === 0;
		
		if($this->globalSettings->useDashboardHistoryPage){
			$this->orderHistoryUrl = admin_url() . 'admin.php?page=booki/userhistory.php';
		}else{
			$this->orderHistoryUrl = Booki_Helper::getUrl(Booki_PageNames::HISTORY_PAGE);
		}
		$this->goToCartUrl = Booki_Helper::appendReferrer(Booki_Helper::getUrl(Booki_PageNames::CART));
		$this->errors = apply_filters( 'booki_custom_form_errors', null);
		//Booki_Helper::noCache();
	}
	
	public function sortByDefaultStep($a, $b){
		if($a['step'] == $a['defaultStep']){
			return 0;
		}
		return ($a['step'] < $b['step']) ? -1 : 1;
	}
	
	public function addToCartCallback($cart, $projectId, $errors){}
	
	public function checkoutCallback($errorMessage, $orderId = null){
		$this->checkoutSuccessMessage = $errorMessage;
		$this->orderId = $orderId;
	}
}

?>