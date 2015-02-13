<?php

class Booki_BridgeBase{
	public function __construct(){
		if(WP_DEBUG){
			remove_action( 'shutdown', 'wp_ob_end_flush_all', 1);
		}
	}
}
?>
