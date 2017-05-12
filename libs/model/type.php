<?php
class Type_Model extends Common_Model
{
  /**
   * @return Type_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	function getAllTypes(){
		global $db;
		$result = $db->get('types');
		$data = $result->all();
		return $data;
	}
}