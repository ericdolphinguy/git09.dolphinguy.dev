<?php
require_once  dirname(__FILE__) . '/../utils/Helper.php';
require_once  dirname(__FILE__) . '/base/BridgeBase.php';

class Booki_UserInfoBridge extends Booki_BridgeBase{
	public function __construct(){
		parent::__construct();
		add_action('wp_ajax_booki_getUserByEmail', array($this, 'getUserByEmailCallback')); 
		//toDO: check user role to enable nopriv mode for this bridge
		//add_action('wp_ajax_nopriv_booki_getUserByEmail', array($this, 'getUserByEmailCallback')); 
	}
	
	public  function getUserByEmailCallback(){

		$model = $_POST['model'];
		$result = get_user_by( 'email', $model['email'] );

		$result = array(
			'id'=>$result ? $result->ID : null
			, 'userName'=>$result ? $result->user_login : null
			, 'firstName'=>$result ? $result->first_name : null
			, 'lastName'=>$result ? $result->last_name : null
			, 'profilePageUrl'=>admin_url('profile.php')
		);
		echo Booki_Helper::json_encode_response($result, 'result');

		die();
	}
}
?>
