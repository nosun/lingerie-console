<?php
class Attribute_Model extends Common_Model
{
   /**
   * @return Attribute_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	public function insertAttribute($tid, $name, $data){
		global $db;
		$set = array(
			'tid' => $tid,
			'name' => $name,
			'data' => serialize($data)
		);
		
		$db->insert('attribute', $set, true);
		return $db->affected();
	}
	
	public function getAttributes($type){
		global $db;
		
		$db->select('name, data');
		$db->from('attribute');
		//$db->join('types', 'attribute.tid = types.tid');
		$db->where('type', $type);
		$result = $db->get();
		$data = $result->allWithKey('name');
		return $data;
	}
	
	public function getAttributeValue($type, $name){
		global $db;
		$db->select('name, data');
		$db->from('attribute');
		//$db->join('types', 'attribute.tid = types.tid');
		$db->where('type', $type);
		$db->where('name', $name);
		
		$result = $db->get();
		$data = $result->row();
		if($data){
			return $data->data;
		}
		return false;
	}
	
}