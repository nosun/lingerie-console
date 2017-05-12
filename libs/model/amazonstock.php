<?php
class Amazonstock_Model extends Common_Model
{
  /**
   * @return Amazonstock_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function getStock($p_sn){
		global $db;
		$db->where('MarchantSKU like', '%_'.$p_sn.'_%');
		$result = $db->get('amazon_stock');
		$data= $result->all();
		return $data;
	}
	
	function getStockItem($p_sn, $avid){
		global $db;
		$db->select('data');
		$db->where('id', $avid);
		$result = $db->get('attr_values');
		$data = unserialize($result->one());
		$merchantSKU = $this->composeSKU($p_sn, $data);
		
		$db->where('MerchantSKU', $merchantSKU);
		$result2 = $db->get('amazon_stock');
		
		$stockItemData = $result2->row();
		return $stockItemData;
	}

	function getStockQty($p_sn, $avid){
		global $db;
		$db->select('data');
		$db->where('id', $avid);
		$result = $db->get('attr_values');
		$data = unserialize($result->one());
		$merchantSKU = $this->composeSKU($p_sn, $data);
		
		$db->select('Quantity');
		$db->where('MerchantSKU', $merchantSKU);
		$result2 = $db->get('amazon_stock');
		
		$stock_qty = $result2->one();
		if(!$stock_qty){
			$stock_qty = 0;
		}
		return $stock_qty;
	}
	
	function updateStock($p_sn, $avid, $qty){
		global $db;
		$db->select('data');
		$db->where('id', $avid);
		$result = $db->get('attr_values');
		$data = unserialize($result->one());
		$merchantSKU = $this->composeSKU($p_sn, $data);
		
		$db->update('amazon_stock', array('Quantity'=>$qty), array('MerchantSKU'=>$merchantSKU));
		return $db->affected();
	}
	
	function composeSKU($p_sn, $properties, $prefix = 'MC'){
		$size = 'OneSize';
		if(isset($properties['size'])){
			$size = $properties['size'];
		}if(isset($properties['Size'])){
			$size = $properties['Size'];
		}
		if($size == 'One size' || $size == 'One Size'){
			$size = 'OneSize';
		}
		return $prefix . '_'. $p_sn .'_'.$size;
	}
}