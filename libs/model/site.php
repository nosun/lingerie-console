<?php
class Site_Model extends Common_Model
{
  /**
   * @return Site_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	function insertSite($url, $name){
		global $db;
	  	$site = array(
	  	'url' => $url,
	  	'name' => $name,
	  	);
	  	$db->insert('sites', $site);
	  	return $db->lastInsertId();
	}
	
	function getAllSites(){
		global $db;
        $result = $db->where('status','1')->get('sites');
        $data = $result->all();
		return $data;
	}
	
	function getSite($sid){
		global $db;
		$db->where('id', $sid);
		$result = $db->get('sites');
		$data = $result->row();
		return $data;
	}
}
