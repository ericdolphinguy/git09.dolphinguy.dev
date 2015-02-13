<?php 
class Booki_ProviderBase {
	public static function json_encode_response($data = NULL, $key = NULL){
		if (method_exists($data, 'toArray')){
			$data = $data->toArray();
		}
		if (! is_null($key)){
			return json_encode(array($key=>$data));
		}
		return json_encode($data);
	}
	
	public static function json_decode_request($data, $assoc = false){
		return json_decode(urldecode($data), $assoc);
	}
}
?>