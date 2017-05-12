<?php
class Category_Model extends Common_Model
{
  /**
   * @return Category_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function insertCategory($sid, $site_cid, $name, $site_pcid){
		global $db;
	  	$catgory = array(
	  	'sid' => $sid,
	  	'site_cid' => $site_cid,
	  	'name' => $name,
	  	'site_pcid' => $site_pcid,
	  	);
	  	$db->insert('category', $catgory);
	  	return $db->lastInsertId();
	}
	
	function getAllCategories(){
		global $db;
		$result = $db->get('category');
		$data = $result->all();
		return $data;
	}
	
	function getCategoriesBySid($sid){
		global $db;
		$db->where('sid', $sid);
		$result = $db->get('category');
		$data = $result->all();
		return $data;
	}
	
	function getCategoryByCid($cid){
		global $db;
		$db->where('id', $cid);
		$result = $db->get('category');
		$data = $result->row();
		return $data;
	}
	
	function getCategoryBySiteCid($siteCid){
		global $db;
		$db->where('site_cid', $siteCid);
		$result = $db->get('category');
		$data = $result->row();
		return $data;
	}
	
	function getCategoriesStructureBySid($sid){
		global $db;
		$db->where('sid', $sid);
		$result = $db->get('category');
		$data = $result->allWithKey('site_cid');
		foreach($data as $site_cid=>$category){
			if($category->site_pcid > 0){
				$category->full_name = $data[$category->site_pcid]->name.'->'.$category->name;
			}else{
				$category->full_name = $category->name;
			}
		}
		$result = array();
		//need to change the data index to cid.
		foreach($data as $site_cid => $category){
			$result[$category->id] = $category;
		}
		unset($data);
		return $result;
	}
	
	function getDefaultCategoryId($sid){
		global $db;
		$db->select('id');
		$db->where('sid', $sid);
		$result = $db->get('category');
		$data = $result->row();
		return $data->id;
	}
}