<?php
require_once dirname(__FILE__) . '/../../domainmodel/repository/CalendarRepository.php';
require_once dirname(__FILE__) . '/../../domainmodel/repository/CalendarDayRepository.php';
require_once 'Booking.php';
require_once 'Bookings.php';
class Booki_Cart{
	private $bookings;
	public function __construct(){
		if(!isset($_SESSION['Booki_Bookings'])){
			$_SESSION['Booki_Bookings'] = new Booki_Bookings();
		}
		$this->bookings = $_SESSION['Booki_Bookings'];
	}
	
	public function setCoupon($coupon){
		$this->bookings->coupon = $coupon;
	}
	
	public function getCoupon(){
		return $this->bookings->coupon;
	}
	
	public function removeCoupon(){
		return $this->setCoupon(null);
	}
	
	public function addBooking(Booki_Booking $booking){
		$this->bookings->add($booking);
	}
	
	public function getBookings(){
		return $this->bookings;
	}
	
	public function getBooking($id){
		foreach($this->bookings as $booking){
			if($booking->id === $id){
				return $booking;
			}
		}
	}
	
	public function remove($id){
		foreach($this->bookings as $booking){
			if($booking->id === $id){
				$this->bookings->remove($booking);
				return;
			}
		}
	}
	
	public function clear(){
		$this->removeCoupon();
		$this->bookings->clear();
		unset($_SESSION['Booki_Bookings']);
	}
	
	public function getTotalAmount(){
		$additionalCosts = 0;
		$totalCost = 0;
		$flag = false;
		
		$calendarRepository =  new Booki_CalendarRepository();
		$calendarDayRepository = new Booki_CalendarDayRepository();
		
		$bookings = $this->getBookings();
		$projectId = null;
		$calendar = null;
		$calendarDays = null;
		foreach($bookings as $booking){
			if($booking->projectId !== $projectId){ 
				$projectId = $booking->projectId;
				$calendar = $calendarRepository->readByProject($projectId);
				$calendarDays = $calendarDayRepository->readAll($calendar->id);
			}

			foreach($booking->dates as $date){
				foreach($calendarDays as $calendarDay){
					$d = Booki_DateHelper::parseFormattedDateString($date);
					if($calendarDay->day === $d->format('Y-m-d')){
						$totalCost += $calendarDay->cost;
						$flag = true;
						break;
					}
				}
				if(!$flag){
					$totalCost += $calendar->cost;
				}
				$flag = false;
			}
			
			foreach($booking->optionals as $optional){
				$additionalCosts += $optional->cost;
			}
			
			foreach($booking->cascadingItems as $cascadingItem){
				$additionalCosts += $cascadingItem->cost;
			}
		}
		
		$totalAmount = $totalCost + $additionalCosts;
		
		if($this->bookings->coupon){
			return $this->bookings->coupon->deduct($totalAmount);
		}
		return $totalAmount;
	}
}
?>