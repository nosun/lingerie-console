<?php
class Common_Model extends MD_Model
{
	function getAdsCodes(){
		$ads_codes = array();
		//if setted, use the settings.
		require_once LIBPATH . '/ads.php';
		foreach($ADS_CODES_TABLE as $ads_name=>$ads_code){
			$ads_codes[$ads_name] = $ads_code;
		}
		return $ads_codes;
	}
}