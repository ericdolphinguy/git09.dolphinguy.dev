<?php
require_once 'CalendarMode.php';
require_once 'BookingMode.php';
require_once 'BookingWizardMode.php';
require_once 'ProjectStep.php';
require_once 'ProjectStatus.php';
require_once 'OptionalsBookingMode.php';
require_once 'OptionalsListingMode.php';
require_once dirname(__FILE__) . '/../base/EntityBase.php';
require_once dirname(__FILE__) . '/../../infrastructure/utils/WPMLHelper.php';

class Booki_Project extends Booki_EntityBase{
	public $id;
	public $status = Booki_ProjectStatus::RUNNING;
	public $name;
	public $bookingDaysLimit = 1;
	public $calendarMode = Booki_CalendarMode::POPUP;
	public $bookingMode = Booki_BookingMode::RESERVATION;
	public $description;
	public $previewUrl;
	public $tag;
	public $defaultStep = Booki_ProjectStep::BOOKING_FORM;
	public $bookingTabLabel; 
	public $customFormTabLabel;
	public $availableDaysLabel;
	public $selectedDaysLabel;
	public $bookingTimeLabel;
	public $optionalItemsLabel;
	public $nextLabel;
	public $prevLabel;
	public $addToCartLabel;
	public $fromLabel;
	public $toLabel;
	public $proceedToLoginLabel;
	public $makeBookingLabel;
	public $bookingLimitLabel;
	public $notifyUserEmailList;
	public $optionalsBookingMode;
	public $optionalsListingMode;
	public $optionalsMinimumSelection;
	public $contentTop;
	public $contentBottom;
	public $bookingWizardMode;
	public $hideSelectedDays;
	public $bookingDaysMinimum;
	//localized
	public $name_loc;
	public $description_loc;
	public $bookingTabLabel_loc;
	public $customFormTabLabel_loc;
	public $selectedDaysLabel_loc;
	public $availableDaysLabel_loc;
	public $descrption_loc;
	public $prevLabel_loc;
	public $nextLabel_loc;
	public $addToCartLabel_loc;
	public $bookingTimeLabel_loc;
	public $optionalItemsLabel_loc;
	public $fromLabel_loc;
	public $toLabel_loc;
	public $proceedToLoginLabel_loc;
	public $makeBookingLabel_loc;
	public $bookingLimitLabel_loc;
	public function  __construct($args){
		if($this->keyExists('status', $args)){
			$this->status = (int)$args['status'];
		}
		if($this->keyExists('name', $args)){
			$this->name = $this->decode((string)$args['name']);
		}
		if($this->keyExists('bookingDaysMinimum', $args)){
			$this->bookingDaysMinimum = (int)$args['bookingDaysMinimum'];
		}
		if($this->keyExists('bookingDaysLimit', $args)){
			$this->bookingDaysLimit = (int)$args['bookingDaysLimit'];
		}
		if($this->keyExists('calendarMode', $args)){
			$this->calendarMode = (int)$args['calendarMode'];
		}
		if($this->keyExists('bookingMode', $args)){
			$this->bookingMode = (int)$args['bookingMode'];
		}
		if($this->keyExists('description', $args)){
			$this->description = $this->decode((string)$args['description']);
		}
		if($this->keyExists('previewUrl', $args)){
			$this->previewUrl = (string)$args['previewUrl'];
		}
		if($this->keyExists('tag', $args)){
			$this->tag = $this->decode((string)$args['tag']);
		}
		if($this->keyExists('defaultStep', $args)){
			$this->defaultStep = (int)$args['defaultStep'];
		}
		if($this->keyExists('bookingTabLabel', $args)){
			$this->bookingTabLabel = $this->decode((string)$args['bookingTabLabel']);
		}
		if($this->keyExists('customFormTabLabel', $args)){
			$this->customFormTabLabel = $this->decode((string)$args['customFormTabLabel']);
		}
		if($this->keyExists('availableDaysLabel', $args)){
			$this->availableDaysLabel = $this->decode((string)$args['availableDaysLabel']);
		}
		if($this->keyExists('selectedDaysLabel', $args)){
			$this->selectedDaysLabel = $this->decode((string)$args['selectedDaysLabel']);
		}
		if($this->keyExists('bookingTimeLabel', $args)){
			$this->bookingTimeLabel = $this->decode((string)$args['bookingTimeLabel']);
		}
		if($this->keyExists('optionalItemsLabel', $args)){
			$this->optionalItemsLabel = $this->decode((string)$args['optionalItemsLabel']);
		}
		if($this->keyExists('nextLabel', $args)){
			$this->nextLabel = $this->decode((string)$args['nextLabel']);
		}
		if($this->keyExists('prevLabel', $args)){
			$this->prevLabel = $this->decode((string)$args['prevLabel']);
		}
		if($this->keyExists('addToCartLabel', $args)){
			$this->addToCartLabel = $this->decode((string)$args['addToCartLabel']);
		}
		if($this->keyExists('fromLabel', $args)){
			$this->fromLabel = $this->decode((string)$args['fromLabel']);
		}
		if($this->keyExists('toLabel', $args)){
			$this->toLabel = $this->decode((string)$args['toLabel']);
		}
		if($this->keyExists('proceedToLoginLabel', $args)){
			$this->proceedToLoginLabel = $this->decode((string)$args['proceedToLoginLabel']);
		}
		if($this->keyExists('makeBookingLabel', $args)){
			$this->makeBookingLabel = $this->decode((string)$args['makeBookingLabel']);
		}
		if($this->keyExists('bookingLimitLabel', $args)){
			$this->bookingLimitLabel = $this->decode((string)$args['bookingLimitLabel']);
		}
		if($this->keyExists('notifyUserEmailList', $args)){
			$this->notifyUserEmailList = trim($this->decode((string)$args['notifyUserEmailList']));
		}
		if($this->keyExists('optionalsBookingMode', $args)){
			$this->optionalsBookingMode = (int)$args['optionalsBookingMode'];
		}
		if($this->keyExists('optionalsListingMode', $args)){
			$this->optionalsListingMode = (int)$args['optionalsListingMode'];
		}
		if($this->keyExists('optionalsMinimumSelection', $args)){
			$this->optionalsMinimumSelection = (int)$args['optionalsMinimumSelection'];
		}
		if($this->keyExists('contentTop', $args)){
			$this->contentTop = $this->decode((string)$args['contentTop']);
		}
		if($this->keyExists('contentBottom', $args)){
			$this->contentBottom = $this->decode((string)$args['contentBottom']);
		}
		if($this->keyExists('bookingWizardMode', $args)){
			$this->bookingWizardMode = (int)$args['bookingWizardMode'];
		}
		if($this->keyExists('hideSelectedDays', $args)){
			$this->hideSelectedDays = (bool)$args['hideSelectedDays'];
		}
		if($this->keyExists('id', $args)){
			$this->id = (int)$args['id'];
		}
		
		//defaults
		if(!$this->bookingTabLabel){
			$this->bookingTabLabel = __('Booking', 'booki');
		}
		if(!$this->customFormTabLabel){
			$this->customFormTabLabel = __('Details', 'booki');
		}
		if(!$this->selectedDaysLabel){
			$this->selectedDaysLabel = __('Selected days', 'booki');
		}
		if(!$this->availableDaysLabel){
			$this->availableDaysLabel = __('Available days', 'booki');
		}
		if(!$this->prevLabel){
			$this->prevLabel =  __('Back', 'booki');
		}
		if(!$this->nextLabel){
			$this->nextLabel =  __('Next', 'booki');
		}
		if(!$this->addToCartLabel){
			$this->addToCartLabel =  __('Add to cart', 'booki');
		}
		if(!$this->bookingTimeLabel){
			$this->bookingTimeLabel = __('Booking time', 'booki');
		}
		if(!$this->optionalItemsLabel){
			$this->optionalItemsLabel = __('Optional extras', 'booki');
		}
		if(!$this->fromLabel){
			$this->fromLabel = __('From', 'booki');
		}
		if(!$this->toLabel){
			$this->toLabel = __('To', 'booki');
		}
		if(!$this->proceedToLoginLabel){
			$this->proceedToLoginLabel = __('Proceed', 'booki');
		}
		if(!$this->makeBookingLabel){
			$this->makeBookingLabel = __('Make booking', 'booki');
		}
		if(!$this->bookingLimitLabel){
			$this->bookingLimitLabel = __('%d seats left. Hurry!', 'booki');
		}
		$this->updateResources();
		$this->init();
	}
	
	protected function init(){
		$this->name_loc = Booki_WPMLHelper::t('name_project' . $this->id, $this->name);
		$this->bookingTabLabel_loc = Booki_WPMLHelper::t('bookingTabLabel_project' . $this->id, $this->bookingTabLabel);
		$this->customFormTabLabel_loc = Booki_WPMLHelper::t('customFormTabLabel_project' . $this->id, $this->customFormTabLabel);
		$this->selectedDaysLabel_loc = Booki_WPMLHelper::t('selectedDaysLabel_project' . $this->id, $this->selectedDaysLabel);
		$this->availableDaysLabel_loc = Booki_WPMLHelper::t('availableDaysLabel_project' . $this->id, $this->availableDaysLabel);
		$this->description_loc = Booki_WPMLHelper::t('description_project' . $this->id, $this->description);
		$this->prevLabel_loc = Booki_WPMLHelper::t('prev_label_project' . $this->id, $this->prevLabel);
		$this->nextLabel_loc = Booki_WPMLHelper::t('next_label_project' . $this->id, $this->nextLabel);
		$this->addToCartLabel_loc = Booki_WPMLHelper::t('add_to_cart_label_project' . $this->id, $this->addToCartLabel);
		$this->bookingTimeLabel_loc = Booki_WPMLHelper::t('booking_time_label_project' . $this->id, $this->bookingTimeLabel);
		$this->optionalItemsLabel_loc = Booki_WPMLHelper::t('optional_items_label_project' . $this->id, $this->optionalItemsLabel);
		$this->fromLabel_loc = Booki_WPMLHelper::t('from_label_project' . $this->id, $this->fromLabel);
		$this->toLabel_loc = Booki_WPMLHelper::t('to_label_project' . $this->id, $this->toLabel);
		$this->proceedToLoginLabel_loc = Booki_WPMLHelper::t('proceed_to_login_label_project' . $this->id, $this->proceedToLoginLabel);
		$this->makeBookingLabel_loc = Booki_WPMLHelper::t('make_booking_label_project' . $this->id, $this->makeBookingLabel);
		$this->bookingLimitLabel_loc = Booki_WPMLHelper::t('booking_limit_label_project' . $this->id, $this->bookingLimitLabel);
	}
	public function updateResources(){
		$this->registerWPML();
	}
	public function deleteResources(){
		$this->unregisterWPML();
	}
	
	protected function registerWPML(){
		Booki_WPMLHelper::register('name_project' . $this->id, $this->name);
		Booki_WPMLHelper::register('bookingTabLabel_project' . $this->id, $this->bookingTabLabel);
		Booki_WPMLHelper::register('customFormTabLabel_project' . $this->id, $this->customFormTabLabel);
		Booki_WPMLHelper::register('selectedDaysLabel_project' . $this->id, $this->selectedDaysLabel);
		Booki_WPMLHelper::register('availableDaysLabel_project' . $this->id, $this->availableDaysLabel);
		Booki_WPMLHelper::register('description_project' . $this->id, $this->description);
		Booki_WPMLHelper::register('prev_label_project' . $this->id, $this->prevLabel);
		Booki_WPMLHelper::register('next_label_project' . $this->id, $this->nextLabel);
		Booki_WPMLHelper::register('add_to_cart_label_project' . $this->id, $this->addToCartLabel);
		Booki_WPMLHelper::register('booking_time_label_project' . $this->id, $this->bookingTimeLabel);
		Booki_WPMLHelper::register('optional_items_label_project' . $this->id, $this->optionalItemsLabel);
		Booki_WPMLHelper::register('from_label_project' . $this->id, $this->fromLabel);
		Booki_WPMLHelper::register('to_label_project' . $this->id, $this->toLabel);
		Booki_WPMLHelper::register('proceed_to_login_label_project' . $this->id, $this->proceedToLoginLabel);
		Booki_WPMLHelper::register('make_booking_label_project' . $this->id, $this->makeBookingLabel);
		Booki_WPMLHelper::register('booking_limit_label_project' . $this->id, $this->bookingLimitLabel);
	}
	
	protected function unregisterWPML(){
		Booki_WPMLHelper::unregister('name_project' . $this->id);
		Booki_WPMLHelper::unregister('bookingTabLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('customFormTabLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('selectedDaysLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('availableDaysLabel_project' . $this->id);
		Booki_WPMLHelper::unregister('description_project' . $this->id);
		Booki_WPMLHelper::unregister('prev_label_project' . $this->id);
		Booki_WPMLHelper::unregister('next_label_project' . $this->id);
		Booki_WPMLHelper::unregister('add_to_cart_label_project' . $this->id);
		Booki_WPMLHelper::unregister('booking_time_label_project' . $this->id);
		Booki_WPMLHelper::unregister('optional_items_label_project' . $this->id);
		Booki_WPMLHelper::unregister('from_label_project' . $this->id);
		Booki_WPMLHelper::unregister('to_label_project' . $this->id);
		Booki_WPMLHelper::unregister('proceed_to_login_label_project' . $this->id);
		Booki_WPMLHelper::unregister('make_booking_label_project' . $this->id);
		Booki_WPMLHelper::unregister('booking_limit_label_project' . $this->id);
	}
	
	public function toArray(){
		return array(
			'id'=>$this->id
			, 'status'=>$this->status
			, 'name'=>$this->name
			, 'name_loc'=>$this->name_loc
			, 'bookingDaysMinimum'=>$this->bookingDaysMinimum
			, 'bookingDaysLimit'=>$this->bookingDaysLimit
			, 'calendarMode'=>$this->calendarMode
			, 'bookingMode'=>$this->bookingMode
			, 'description'=>$this->description
			, 'description_loc'=>$this->description_loc
			, 'previewUrl'=>$this->previewUrl
			, 'tag'=>$this->tag
			, 'defaultStep'=>$this->defaultStep
			, 'bookingTabLabel'=>$this->bookingTabLabel
			, 'customFormTabLabel'=>$this->customFormTabLabel
			, 'availableDaysLabel'=>$this->availableDaysLabel
			, 'selectedDaysLabel'=>$this->selectedDaysLabel
			, 'bookingTimeLabel'=>$this->bookingTimeLabel
			, 'optionalItemsLabel'=>$this->optionalItemsLabel
			, 'nextLabel'=>$this->nextLabel
			, 'prevLabel'=>$this->prevLabel
			, 'addToCartLabel'=>$this->addToCartLabel
			, 'fromLabel'=>$this->fromLabel
			, 'toLabel'=>$this->toLabel
			, 'proceedToLoginLabel'=>$this->proceedToLoginLabel
			, 'makeBookingLabel'=>$this->makeBookingLabel
			, 'bookingLimitLabel'=>$this->bookingLimitLabel
			, 'notifyUserEmailList'=>$this->notifyUserEmailList
			, 'optionalsBookingMode'=>$this->optionalsBookingMode
			, 'optionalsListingMode'=>$this->optionalsListingMode
			, 'optionalsMinimumSelection'=>$this->optionalsMinimumSelection
			, 'contentTop'=>$this->contentTop
			, 'contentBottom'=>$this->contentBottom
			, 'bookingWizardMode'=>$this->bookingWizardMode
			, 'hideSelectedDays'=>$this->hideSelectedDays
		);
	}
}
?>