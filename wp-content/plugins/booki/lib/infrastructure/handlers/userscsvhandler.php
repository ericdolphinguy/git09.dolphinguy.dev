<?php 
	require_once  dirname(__FILE__) . '/base/CSVBaseHandler.php';
	require_once  dirname(__FILE__) . '/../../domainmodel/repository/UserRepository.php';
	class Booki_BookingsCSVHandler extends Booki_CSVBaseHandler
	{
		public function __construct(){
			$pageIndex = isset($_GET['pageindex']) ? $_GET['pageindex'] : -1;
			$perPage = isset($_GET['perpage']) ? $_GET['perpage'] : null;
			$orderBy = isset($_GET['orderby']) ? $_GET['orderby'] : 'id';
			$order = isset($_GET['order']) ? $_GET['order'] : null;
			
			$userRepository = new Booki_UserRepository();
			$result = $userRepository->readAll($pageIndex, $perPage, $orderBy, $order);
			
			$columnNames = array(
				'id'
				, 'username'
				, 'email'
				, 'firstname'
				, 'lastname'
				, 'bookingsCount'
			);
			
			$records = array();
			foreach($result as $user){
				array_push($records, implode(",", array(
					$this->encode($user->id)
					, $this->encode($user->username)
					, $this->encode($user->email)
					, $this->encode($user->firstname)
					, $this->encode($user->lastname)
					, $this->encode($user->bookingsCount)
				)));
			}
			
			echo implode(",", $columnNames);
			echo "\n";
			echo implode("\n", $records);
		}
	}
?>
