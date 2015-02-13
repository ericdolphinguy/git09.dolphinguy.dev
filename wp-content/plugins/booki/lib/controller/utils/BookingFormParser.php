<?php
	require_once dirname(__FILE__) . '/../../domainmodel/entities/ElementType.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/FormElementRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/OptionalRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/CascadingListRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/ProjectRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
	require_once dirname(__FILE__) . '/../../domainmodel/service/BookingProvider.php';
	require_once dirname(__FILE__) . '/../../infrastructure/session/OrderLogger.php';
	require_once dirname(__FILE__) . '/../../infrastructure/session/Booking.php';
	require_once dirname(__FILE__) . '/../../infrastructure/session/Bookings.php';
	require_once dirname(__FILE__) . '/../../infrastructure/utils/Validator.php';
	require_once dirname(__FILE__) . '/../../infrastructure/utils/Helper.php';
	require_once dirname(__FILE__) . '/../../infrastructure/utils/DateHelper.php';
	
class Booki_BookingFormParser{
	protected function __construct() {
	}
	public static function populateBookingFromPostData($projectId, Booki_Bookings $bookings){
		if($projectId === -1 || $projectId === null){
			return $bookings;
		}
		$errors = array();
		
		$calendarFieldId = 'selected_date';
		$timeFieldId = 'time';
		$timezoneField = 'timezone';
		$depositField = 'deposit_field';
		
		$selectedDate = isset($_POST[$calendarFieldId]) ? $_POST[$calendarFieldId] : '';
		$selectedTime = isset($_POST[$timeFieldId]) ? $_POST[$timeFieldId] : null;
		$timezone = isset($_POST[$timezoneField]) ? $_POST[$timezoneField] : null;
		$deposit = isset($_POST[$depositField]) ? (double)$_POST[$depositField] : null;
		$dates = explode(',', $selectedDate);
		
		foreach($dates as $d){
			if(!Booki_DateHelper::dateIsValidFormat($d)){
				$errors['Date'] = __('Booking date is required or is in an invalid format.', 'booki');
				return array('bookings'=>$bookings, 'errors'=>$errors);
			}
		}
		
		$projectRepository = new Booki_ProjectRepository();
		$project = $projectRepository->read($projectId);
		$calendarRepository = new Booki_CalendarRepository();
		$calendar = $calendarRepository->readByProject($projectId);
		$formElementRepository = new Booki_FormElementRepository();	
		$optionalRepository =  new Booki_OptionalRepository();
		$formElements = $formElementRepository->readAll($projectId);
		$optionals = $optionalRepository->readAll($projectId);
		$cascadingListRepository = new Booki_CascadingListRepository();
		$cascadingLists = $cascadingListRepository->readAllTopLevel($projectId);
		$cascadingLists = $cascadingListRepository->readItemsByLists($cascadingLists);
		
		if($timezone){
			$bookings->setTimezone($timezone);
		}
		
		$booking = new Booki_Booking($bookings->count() - 1, $projectId, $project->name, $selectedDate, is_array($selectedTime) ? $selectedTime[0] : $selectedTime, $deposit);
		
		foreach($formElements as $elem){
			$name = 'booki_form_element_' . $elem->id;
			if($elem->elementType === Booki_ElementType::RADIOBUTTON){
				$name = 'booki_form_element_' . (strlen($elem->value) > 0 ? $elem->value : $elem->id);
			}
			if( isset($_POST[$name])){
				$value = $_POST[$name];
				if($elem->elementType === Booki_ElementType::RADIOBUTTON && $elem->label !== $value)
				{
					continue;
				}
				
				$validator = new Booki_Validator($elem->validation, $elem->label, $value);
				if($validator->isValid()){
					if($value && count($errors) === 0){
						$elem->value = $value;
						$booking->formElements->add($elem);
					}
				} else{
					$errors[$name] = join(',', $validator->errors);
				}
			}
		}
		
		$optionalName = 'booki_optional_group_' . $projectId;
		if($project->optionalsMinimumSelection > 0){
			if(isset($_POST[$optionalName]) && count($_POST[$optionalName]) < $project->optionalsMinimumSelection){
				$errors[$optionalName] = sprintf(__('You must make atleast %d selections', 'booki'), $project->optionalsMinimumSelection);
			}
		}
		
		if($calendar->period === Booki_CalendarPeriod::BY_DAY){
			if($project->bookingDaysMinimum && count($booking->dates) < $project->bookingDaysMinimum){
				$errors['Dates'] = sprintf('A minimum of %d days required.', $project->bookingDaysMinimum);
			}
			if($project->bookingDaysLimit > 1 && count($booking->dates) > $project->bookingDaysLimit){
				$errors['Dates'] = sprintf('You cannot select more than %d days.', $project->bookingDaysLimit);
			}
		}else{
			if(!$selectedTime){
				$errors['Timeslots'] = sprintf(__('A minimum of 1 time slots required. If there are no time slots available, try a different date.', 'booki'), 1);
			}else{
				if($project->bookingDaysMinimum && (is_array($selectedTime) && count($selectedTime) < $project->bookingDaysMinimum)){
					$errors['Timeslots'] = sprintf(__('A minimum of %d time slots required.', 'booki'), $project->bookingDaysMinimum);
				}
				if($project->bookingDaysLimit > 1 && (is_array($selectedTime) && count($selectedTime) > $project->bookingDaysLimit)){
					$errors['Timeslots'] = sprintf(__('You cannot select more than %d time slots.', 'booki'), $project->bookingDaysLimit);
				}
			}
		}
		$bookingsTotalCount = 0;
		if($project->optionalsBookingMode === Booki_OptionalsBookingMode::EACH_DAY){
			if(count($booking->dates) > 1){
				$bookingsTotalCount = count($booking->dates);
			}else if(is_array($selectedTime) && count($selectedTime) > 1){
				$bookingsTotalCount = count($selectedTime);
			}
		}
		
		if(count($errors) === 0){
			foreach($cascadingLists as $cl){
				$errors = self::fillCascadingListFromHttpPost($booking, $cascadingListRepository, $bookingsTotalCount, $cl->cascadingItems, $cl->id, $cl->isRequired);
				if(count($errors) > 0){
					break;
				}
			}
		}
		
		if(count($errors) === 0){
			foreach($optionals as $optional){
				if(isset($_POST[$optionalName])){
					$optional->count = $bookingsTotalCount;
					$value = $_POST[$optionalName];
					if(is_array($value)){
						foreach($value as $id){
							if(intval($id) === $optional->id){
								$booking->optionals->add($optional);
								break;
							}
						}
					}else if($value){
						$booking->optionals->add($optional);
					}
				}
			}
		
			$bookings->add($booking);
			
			if(is_array($selectedTime) && count($selectedTime) > 1){
				for($i = 1; $i < count($selectedTime); $i++){
					$time = $selectedTime[$i];
					$booking = new Booki_Booking($bookings->count() - 1, $projectId, $project->name, $selectedDate, $time, $deposit);
					$bookings->add($booking);
				}
			}
		}
		return array('bookings'=>$bookings, 'errors'=>$errors);
	}
	
	protected static function fillCascadingListFromHttpPost($booking, $cascadingListRepository, $bookingsTotalCount, $cascadingItems, $id, $isRequired, $hasSelection = false){
		$errors = array();
		$cascadingItemName = 'booki_cascadingdropdown_' . $id;
		foreach($cascadingItems as $ci){
			if(isset($_POST[$cascadingItemName])){
				$hasSelection = true;
				$selectedCascadingItemId = (int)$_POST[$cascadingItemName];
				if($selectedCascadingItemId === $ci->id){
					$ci->count = $bookingsTotalCount;
					if($ci->parentId === -1){
						$booking->cascadingItems->add($ci);
					} else {
						$cascadingItems = $cascadingListRepository->readItemsByListId($ci->parentId);
						self::fillCascadingListFromHttpPost($booking, $cascadingListRepository, $bookingsTotalCount, $cascadingItems, $ci->parentId, $isRequired, $hasSelection);
					}
				}
			}
		}
		if(!$hasSelection && $isRequired){
			$errors[$cascadingItemsName] = __('You must select atleast one item from the dropdown list', 'booki');
			break;
		}
		return $errors;
	}
}
?>