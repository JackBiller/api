<?php

class RouteConfig { 
	var $isGet 		= true;
	var $isGet_Id 	= true;
	var $isPost 	= true;
	var $isPut 		= true;
	var $isDelete 	= true;

	function __construct($option=array()) { 
		$this->setOptions($option);
	}

	public function setOptions($option=array()) { 
		foreach ($option as $key => $value) { 
			$this->$key = $value;
		}
	}
}

?>