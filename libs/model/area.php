<?php 
class Area_Model extends Common_Model
{
  /**
   * @return Area_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	
	function getAreaCode($areaName){
		global $db;
		$sql = 'SELECT code FROM area WHERE LOWER(`name`) = ';
		$sql .= '"'.strtolower(trim($areaName)).'"';
		$result = $db->query($sql);
		$data = $result->one();
		if($data == false){
			$data = $areaName;
		}
		return $data;
	}
	
	function getAllCountries(){
		global $db;
		$db->where('parent_id', 0);
		$result = $db->get('area');
		$data = $result->all();
		return $data;
	}
	
	function getAllProvinces($country_code){
		global $db;
		$db->select('id');
		$db->where('code', $country_code);
		$result = $db->get('area');
		$data = $result->one();
		
		$db->where('parent_id', $data);
		$result = $db->get('area');
		$data = $result->all();
		return $data;
	}
	
	function getAllProvinceByCountryId($parent_id){
		global $db;
		$db->where('parent_id', $parent_id);
		$result = $db->get('area');
		$data = $result->all();
		return $data;
	}
	
}