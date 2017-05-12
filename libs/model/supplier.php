<?php
class Supplier_Model extends Common_Model
{
  /**
   * @return Supplier_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	function getSuppliers(){
		global $db;
		$result = $db->get('suppliers');
		$data = $result->all();
		return $data;
	}
	
	function getSupplier($supplierName){
		global $db;
		$db->where('name', $supplierName);
		$result = $db->get('suppliers');
		$data = $result->row();
		return $data;
	}
	
	function insertSupplier($supplierName){
		global $db;
		$db->insert('suppliers', array('name'=>$supplierName), true);
		return $db->lastInsertId();
	}
}