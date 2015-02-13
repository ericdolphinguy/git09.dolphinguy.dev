<?php
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/ElementType.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/EmailType.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/entities/EmailSetting.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/repository/EmailSettingRepository.php';
	require_once dirname(__FILE__) . '/../../../domainmodel/service/EventsLogProvider.php';
	require_once dirname(__FILE__) . '/../../utils/Helper.php';
class Booki_Emailer{
	private $emailSettingRepository;
	protected $emailSettings;
	protected $emailType;
	protected $globalSettings;
	protected $displayTimezone;
	public function __construct($emailType){
		$this->emailType = $emailType;
		$this->emailSettingRepository = new Booki_EmailSettingRepository($emailType);
		$this->emailSettings = $this->emailSettingRepository->read();
		$this->globalSettings = Booki_Helper::globalSettings();
		if(!$this->emailSettings){
			$this->emailSettings = new Booki_EmailSetting();
			$this->emailSettings->content = Booki_Helper::readEmailTemplate($emailType);
		}
		$this->displayTimezone = $this->globalSettings->displayTimezone();
	}
	
	protected function logErrorIfAny($result){
		if (!$result) {
			global $ts_mail_errors;
			global $phpmailer;
			if (!isset($ts_mail_errors)){
				$ts_mail_errors = array();
			}
			if (isset($phpmailer)) {
				array_push($ts_mail_errors, $phpmailer->ErrorInfo);
			}
			
			Booki_EventsLogProvider::insert($ts_mail_errors);
		}
	}
}
?>