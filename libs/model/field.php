<?php
class Field_Model extends Common_Model
{
  /**
   * @return Field_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	function getFields($fieldName){
		global $db;
		$db->select('value');
		$result = $db->get($fieldName);
		$data = $result->all();
		return $data;
	}
}