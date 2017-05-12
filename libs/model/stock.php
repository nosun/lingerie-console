<?php
class Stock_Model extends Common_Model
{
  /**
   * @return Stock_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function insertStockItem($p_sn, $avid, $stock_qty, $bought_price=null, $sell_price_delta=null, $adjust_time=null) {
	    global $db;
	    $stock = array(
        'p_sn' => $p_sn,
        'avid' => $avid,
        'stock_qty' => $stock_qty,
        'bought_price' => $bought_price,
        'sell_price_delta' => $sell_price_delta,
	    'adjust_time' => $adjust_time,
        );
        $db->insert('stock', $stock);
        return $db->lastInsertId();
	}
	
	function insertStock($p_sn, $parameters, $stock_qty, $bought_price=null, $sell_price_delta=null){
		global $db;
		$productInstance = Product_Model::getInstance();

		$db->select('*');
		$db->from('attr_values');
		$result = $db->get();
		$attr_values = $result->all();
		$avid = -1;
		foreach ($attr_values as $v){
			$attrs2 = unserialize($v->data);
			if($productInstance->isPropertiesEqual($parameters, $attrs2)){
				$avid = $v->id;
				break;
			}
		}
		if($avid == -1){
			//this is a new attr_values pair.
			$db->insert('attr_values', array('data'=>serialize($parameters)));
			$avid = $db->lastInsertId();
		}
		
	  	$stock = array(
	  	'p_sn' => $p_sn,
	  	'avid' => $avid,
	  	//'parameters' => serialize($parameters),
	  	'stock_qty' => $stock_qty,
	  	'bought_price' => $bought_price,
	  	'sell_price_delta' => $sell_price_delta
	  	);
	  	$db->insert('stock', $stock);
	  	return $db->lastInsertId();
	}
	
	function getStock($p_sn){
		global $db;
		
		$db->select('stock.*, attr_values.data');
		$db->from('stock');
		$db->join('attr_values', 'stock.avid = attr_values.id');
		$db->where('p_sn', $p_sn);
		$result = $db->get();
		$data = $result->all();
		if($data && count($data) > 0){
			foreach($data as $k=>$v){
				$v->parameters = unserialize($v->data);
			}
		}
		return $data;
	}
	
	function getStockById($stock_id) {
	    global $db;
	    
	    $db->select('stock.*, attr_values.data');
        $db->from('stock');
        $db->join('attr_values', 'stock.avid = attr_values.id');
        $db->where('stock.stock_id', $stock_id);
        $result = $db->get();
        $data = $result->row();
        if(!empty($data)){
            $data->parameters = unserialize($data->data);
        }
        return $data;
	}
	function getStockItem($p_sn, $avid){
		global $db;
		
		$db->where('avid', $avid);
		$db->where('p_sn', $p_sn);
		$result = $db->get('stock');
		$data = $result->row();
		return $data;
	}
	
	
	
	function getStockQty($p_sn, $avid){
		global $db;
		
		$db->select('stock_qty');
		$db->from('stock');
		$db->where('avid', $avid);
		$db->where('p_sn', $p_sn);
		$result = $db->get();
		$data = $result->one();
		if(!$data){
			return 0;
		}
		return $data;
	}
	
	function updateStock($p_sn, $avid, $set){
		global $db;
		$db->update('stock', $set, array('p_sn'=>$p_sn, 'avid'=>$avid));
		return (boolean)$db->affected();
	}
	
	function updateStockQtyByProperties($p_sn, $properties, $stock_qty, $bought_price, $increment = false){
		global $db;
		$productInstance = Product_Model::getInstance();

		$db->select('*');
		$db->from('attr_values');
		$result = $db->get();
		$attr_values = $result->all();
		$avid = -1;
		foreach ($attr_values as $v){
			$attrs2 = unserialize($v->data);
			if($productInstance->isPropertiesEqual($properties, $attrs2)){
				$avid = $v->id;
				break;
			}
		}
		if($avid == -1){
			//this is a new attr_values pair.
			$db->insert('attr_values', array('data'=>serialize($properties)));
			$avid = $db->lastInsertId();
		}
		if($increment){
			$db->query('INSERT INTO stock (p_sn,avid, stock_qty, bought_price) VALUES ("'. $p_sn .'",'.$avid.','.strval($stock_qty).','.strval($bought_price).') ON DUPLICATE KEY UPDATE stock_qty = stock_qty + '.strval($stock_qty));
			//$db->query('UPDATE `stock` SET `stock_qty` = stock_qty + '. strval($stock_qty).' WHERE `p_sn` = "'. $p_sn .'" AND `avid` = "'.$avid.'"');
		}else{
			$db->query('INSERT INTO stock (p_sn,avid, stock_qty, bought_price) VALUES ("'. $p_sn .'",'.$avid.','.strval($stock_qty).','.strval($bought_price).') ON DUPLICATE KEY UPDATE stock_qty = '.strval($stock_qty));
			//$db->query('UPDATE `stock` SET `stock_qty` = '.strval($stock_qty).' WHERE `p_sn` = "'. $p_sn .'" AND `avid` = "'.$avid.'"');
		}
		return (boolean)$db->affected();
	}
	
	function updateStockByStockId($stock_id, $set){
		global $db;
		$db->update('stock', $set, array('stock_id'=>$stock_id));
		return (boolean)$db->affected();
	}
	
	function getStockData($lock_enable = true, $limit = null, $page = 1){
		global $db;
		$sql = 'select stock.stock_id, stock.p_sn, stock.stock_qty, stock.avid, attr_values.data from stock inner join attr_values where attr_values.id = stock.avid';
		if(isset($limit)){
			$sql .= ' limit '.strval(intval($page - 1) * $limit).' , '.$limit;
		}
		$result = $db->query($sql);
		$data = $result->all();
		
		if($lock_enable){
		//fetch current locked items by cache.
			$stockLockItems = cache::get('order_memento_stocklocks');
			if($stockLockItems){
				$stockLockItems = $stockLockItems->data;
			}else{
				$stockLockItems = array();
			}
			//p->sn(avid=>lock_qty)
			foreach($data as $stockItem){
				if(!key_exists($stockItem->p_sn, $stockLockItems) || 
				   !key_exists($stockItem->avid, $stockLockItems[$stockItem->p_sn])){
					$stockItem->lock_qty = 0;
				}else{
					//有这个库存
					$stockItem->lock_qty = $stockLockItems[$stockItem->p_sn][$stockItem->avid];
				}
				$props = unserialize($stockItem->data);
				$stockItem->color = '';
				$stockItem->size = '';
		  		foreach ($props as $prop_key => $prop_value){
	  				if(strpos($prop_key, 'olor')){
	  					$stockItem->color = $prop_value;
	  				}else if(strpos($prop_key, 'ize')){
	  					$stockItem->size = $prop_value;
	  				}
	  			}
				unset($stockItem->data);
				unset($stockItem->stock_id);
				unset($stockItem->avid);
			}
		}else{
			//p->sn(avid=>lock_qty)
			foreach($data as $stockItem){
				$props = unserialize($stockItem->data);
				$stockItem->color = '';
				$stockItem->size = '';
		  		foreach ($props as $prop_key => $prop_value){
	  				if(strpos($prop_key, 'olor')){
	  					$stockItem->color = $prop_value;
	  				}else if(strpos($prop_key, 'ize')){
	  					$stockItem->size = $prop_value;
	  				}
	  			}
				unset($stockItem->data);
				unset($stockItem->stock_id);
				unset($stockItem->avid);
			}
		}
		return $data;
	}
	
	function getAverageSalesBySn($sn, $avid) {
		global $db;
		$sql = sprintf("select p_sn, avid, ceil(sum(qty) / 4) as sales from orders,orders_items where orders.oid = orders_items.oid and orders.status_payment = 1 and from_unixtime(created) >= DATE_SUB(NOW(), INTERVAL 1 MONTH) and p_sn = '%s' and avid = %d", $sn, $avid);
		$result = $db->exec($sql);
		if ($result) {
			return $result->one(2);
		}
		return 0;
	}
}