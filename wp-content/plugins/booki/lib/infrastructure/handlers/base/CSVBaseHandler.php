<?php 
	class Booki_CSVBaseHandler
	{
		function encode($value) {
			if(strpos($value, '"') !== false || 
				strpos($value, "\n") !== false) 
			{
				$value = str_replace('"', '""', $value);
				$value = str_replace("\n", '', $value);
			}
			return '"' . $value . '"';
		}
	}
?>
