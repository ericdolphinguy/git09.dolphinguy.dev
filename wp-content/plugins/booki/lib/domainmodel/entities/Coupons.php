<?php
require_once  dirname(__FILE__) . '/../base/CollectionBase.php';
require_once 'Coupon.php';
class Booki_Coupons extends Booki_CollectionBase{
	public $total = 0;//contains total records count --use when paging
	public function add($value) {
		if (! ($value instanceOf Booki_Coupon) ){
			throw new Exception('Invalid value. Expected an instance of the Booki_Coupon class.');
		}
        parent::add($value);
    }
}
?>