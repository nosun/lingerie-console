<?php
class Order_Model extends Common_Model
{
	private static $_DEFAULT_ATTRIBUTE_VALUE = array('Color'=>'As Shown', 'Size'=>'One Size');
	/**
	 * @return Order_Model
	 * Enter description here ...
	 */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function insertOrder($sid, $number, $othersArray, $outInventory = '0'){
		global $db;
		$set = array('sid'=>$sid, 'number'=>$number);
		foreach($othersArray as $key=>$value){
			$set[$key] = $value;
		}
		$set['outinventory'] = $outInventory;
	  	$db->insert('orders', $set);
	  	return $db->lastInsertId();
	}

	function updateOrder($sid, $number, $set){
		global $db;
		//if the timestamp is smaller than current timestamp;
		if(key_exists('updated', $set)){
			$db->update('orders', $set, array('sid'=>$sid, 'number'=>$number, 'updated <= '=>$set['updated']));
		}
		return $db->affected();
	}
	
	function updateOrderById($oid, $set){
		global $db;
		//if the timestamp is smaller than current timestamp;
		if(key_exists('updated', $set)){
			$db->update('orders', $set, array('oid'=>$oid, 'updated <= '=>$set['updated']));
		}
		return $db->affected();
	}
	
	function updateOrderConsoleFields($oid, $set){
		global $db;
		//if the timestamp is smaller than current timestamp;
		$db->update('orders', $set, array('oid'=>$oid));
		return $db->affected();
	}
	
	function updateOrderOutInventory($oid, $outInventory){
		global $db;
		//if the timestamp is smaller than current timestamp;
		$db->update('orders', array('outinventory'=>$outInventory), array('oid'=>$oid));
		return $db->affected();
	}
	
	
	function getOrder($sid, $number){
		global $db;
		$db->where('sid', $sid);
		$db->where('number', $number);
		$result = $db->get('orders');
		return $result->row();
	}
	
	function getOrderById($oid){
		global $db;
		$db->where('oid', $oid);
		$result = $db->get('orders');
		return $result->row();
	}
	
	function getOrderByNumber($number) {
	    global $db;
	    $db->where('number', $number);
	    $result = $db->get('orders');
	    $orderInfo = $result->row();
	    return $orderInfo;
	}
	
	function getOrders($sort= null, $limit = null, $filter=null, $offset = null){
		global $db;
		if(isset($sort)){
			$db->orderby($sort);
		}
		if(isset($filter)){
			foreach ($filter as $key => $value) {
				$db->where($key, $value);
      		}
		}
		$result = $db->get('orders', $limit, $offset);
		$data = $result->allWithKey('oid');
		return $data;
	}
	
	function getIncompleteHandlingOrders(){
		$sort = 'communicate_time desc, created desc';
		$filter = array('status_payment'=>'1', 'status_shipping <>'=>'1', 'outinventory'=>'0', 'status_handling <>' => '-2');
		return $this->getOrders($sort, null, $filter);
	}
	
	function getOrdersCount(){
		global $db;
		$db->select('count(*) as count');
		$result = $db->get('orders');
		$data = $result->row();
		return $data->count;
	}
	
	function getLatestCreatedOrderTime(){
		global $db;
		$result = $db->query('SELECT MAX(created) AS created FROM orders;');
		$data = $result->row();
		return $data->created;
	}
	
	function insertOrderItem($oid, $p_sn, $sid, $number, $qty, $total_amount, $image_source, $data, $current_qty = 0){
		global $db;

		$productInstance = Product_Model::getInstance();
		$attrs = unserialize($data);
		
		if (is_array($attrs)) {
			//get the $attrs. If attrs is lacking, will fill default attribute to it.
			foreach ($attrs as $attr_key=>$attr_value){
				if(ctype_lower($attr_key[0])){
					$real_key = ucfirst($attr_key);
					if(!isset($attr_value) || $attr_value == ''){
						$attrs[$real_key] = Order_Model::$_DEFAULT_ATTRIBUTE_VALUE[$real_key];
					}else{
						$attrs[$real_key] = $attr_value;
					}
					unset($attrs[$attr_key]);
				}else{
					if(!isset($attr_value) || $attr_value == ''){
						$attrs[$attr_key] = Order_Model::$_DEFAULT_ATTRIBUTE_VALUE[$attr_key];
					}
				}
			}
		}
		$db->select('*');
		$db->from('attr_values');
		$result = $db->get();
		$attr_values = $result->all();
		$avid = -1;
		foreach ($attr_values as $v){
			$attrs2 = unserialize($v->data);
			if($productInstance->isPropertiesEqual($attrs, $attrs2)){
				$avid = $v->id;
				break;
			}
		}
		if($avid == -1){
			//this is a new attr_values pair.
			$db->insert('attr_values', array('data'=>$data));
			$avid = $db->lastInsertId();
		}

		$set = array('oid'=>$oid, 'p_sn'=>$p_sn,  'sid'=>$sid, 'o_number'=>$number, 'qty'=>$qty, 'total_amount'=>$total_amount, 'image_source'=>$image_source, 'avid'=>$avid, 'current_qty'=>$current_qty);
	  	$db->insert('orders_items', $set);
	  	return $db->lastInsertId();
	}
	
	function updateOrderItemsLockQty($orderItems, $oid, $reset = false){
		global $db;
		if(!isset($orderItems) || count($orderItems) == 0){
			return false;
		}
		$sql = 'update orders_items set current_qty = ';
		
		$oiids = array();
		if($reset){
			$sql .= '0 ';
			foreach($orderItems as $orderItem){
				$oiids[] = $orderItem->oiid;
			}
		}else{
		$sql .= 'case oiid ';
		foreach($orderItems as $orderItem){
			$oiids[] = $orderItem->oiid;
			$sql .= sprintf("WHEN %d THEN %d ", $orderItem->oiid, $orderItem->current_qty);
		}
			$sql .= "END ";
		}
		$oiids = implode(',', $oiids);

		$sql .= 'WHERE oid= '. $oid . ' AND oiid IN ('.$oiids.')';
		
		$db->query($sql);
		$returnCode = $db->affected();
		
		if($returnCode != -1){
			//update success. Need Update the cache.
			cache::remove('orderitems-'.$oid);
		}
		return $returnCode;
	}
	
	function updateOrderAmount($oid, $orderAmount) {
	    global $db;
	    if (empty($oid)) {
	        return false;
	    }
	    $sql = sprintf("update orders set total_amount=%f, pay_amount=total_amount + fee_amount where oid=%d;", $orderAmount, $oid);
	    $db->query($sql);
        $returnCode = $db->affected();
        
        if($returnCode != -1){
            //update success. Need Update the cache.
            cache::remove('orderitems-'.$oid);
        }
        return $returnCode;
	}
	function getOrderItemsDetail($sid, $number){
		global $db;
		$db->select('orders_items.*, attr_values.data');
		$db->from('orders_items');
		$db->join('attr_values', 'orders_items.avid = attr_values.id');
		$db->where('sid', $sid);
		$db->where('o_number', $number);
		$result = $db->get();
		$data = $result->all();
		if($data){
			foreach($data as $k=>$v){
				$v->data = unserialize($v->data);
			}
		}
		return $data;
	}
	
	function getOrderItemsDetailByOid($oid){
		global $db;
		static $list = array();
		if(!isset($list[$oid])){
			$cacheId = 'orderitems-'.$oid;
			if ($cache = cache::get($cacheId)) {
		    	$data = $cache->data;
		    }else{
				$db->select('orders_items.*, attr_values.data');
				$db->from('orders_items');
				$db->join('attr_values', 'orders_items.avid = attr_values.id');
	    		$db->where('oid', $oid);
				$result = $db->get();
				$data = $result->all();
				if($data && count($data) > 0){
					foreach($data as $k=>$v){
						$v->data = unserialize($v->data);
						$productImages = glob("files/". $v->p_sn ."/*.*");
						if(count($productImages) > 0){
							$v->imageSource = $productImages[0];
						}else{
							if(isset($v->image_source) && $v->image_source != ''){
								$v->imageSource = $v->image_source;
							}else{
								$v->imageSource = null;
							}
						}
					}
					cache::save($cacheId, $data);
				}else{
					$data = false;
				}
		    }
		    if($data){
		    	$list[$oid] = $data;
		    }
		} else {
    		$data = $list[$oid];
    	}
		return $data;
	}
	
	public function getOrderStatuses($combine_state = true){
		if($combine_state){
			return array('<>2'=>'订单状态：非已完成', 0=>'待处理',1=>'处理中',2=>'已完成',-1=>'已取消',-2=>'已删除','default'=>'订单状态：所有');
		}
		return array(0=>'待处理',1=>'处理中',2=>'已完成',-1=>'已取消',-2=>'已删除');
	}
	public function getOrderPaymentStatuses($combine_state = true){
		if($combine_state){
			return array(1=>'已付款', 0=>'未付款', 2=>'部分退款', 3=>'已退款', 'default'=>'付款状态：所有');
		}
		return array(0=>'未付款', 1=>'已付款', 2=>'部分退款', 3=>'已退款');
	}
	public function getOrderShippingStatuses($combine_state = true){
		if($combine_state){
			return array(0=>'未送货', 1=>'已送货', 'default'=>'送货状态：所有');
		}
		return array(0=>'未送货', 1=>'已送货');
	}

	public function getOrderPaymentMethods($combine_state = true){
		if($combine_state){
			return array('default'=>'付款方式：所有', '<>paypal'=>'付款方式：非贝宝','paypal'=>'付款方式：贝宝','western'=>'付款方式：西联','wiretrasfer'=>'付款方式：电汇');
		}
		return array('paypal'=>'贝宝','western'=>'西联','wiretrasfer'=>'电汇');
	}
	public function getOrderShippingMethods($combine_state = true){
		if($combine_state){
			return array('default'=>'运送方式：所有', 'not in ("hongkongpost", "ePacket")' => '运送方式：非航空小包', 'ems'=>'运送方式：EMS','ups'=>'运送方式：UPS','hongkongpost'=>'运送方式：航空小包', 'ePacket' => '运送方式：ePacket');
		}
		return array('ems'=>'EMS','ups'=>'UPS','hongkongpost'=>'航空小包', 'ePacket' => '运送方式：ePacket');
	}
	public function getOutInventoryStatuses($combine_state = true){
		if($combine_state){
			return array(0=>'未出库', 1=>'已出库', 'default'=>'出库状态：所有');
		}
		return array(0=>'未出库', 1=>'已出库');
	}
	function getOrdersCountByCompleteDate($timestamp, $outinventory = 'default', $shipping_method = 'default'){
		global $db;
		$startTime = $timestamp;
		//$sql = 'SELECT count(*) as count FROM orders WHERE date(FROM_UNIXTIME(finished))= "'
		//		.date('Y-m-d', $timestamp) 
		$sql = 'SELECT count(*) as count FROM orders WHERE `status_shipping` = "1" AND `finished` >= "' . strval($timestamp) . '" AND `finished` < "' . strval(($timestamp + 3600 * 24)).'"';
		if($outinventory != 'default'){
			$sql .= ' AND `outinventory` = "' .$outinventory . '"';
		}
		if($shipping_method != 'default'){
			$sql .= ' AND `shipping_method`';
			if(startsWith($shipping_method, '<>')){
				$sql .= '<> "' .substr($shipping_method, 2) . '"';
			}else{
				$sql .= ' = "' .$shipping_method . '"';
			}
		}
		$result = $db->query($sql);
		$data = $result->row();
		return $data->count;
	}
	
	function getOrdersByCompleteDate($timestamp, $outinventory = 'default', $shipping_method = 'default', $limit = null, $offset = null){
		global $db;
		//$sql = 'SELECT * FROM orders WHERE date(FROM_UNIXTIME(finished))= "'
		//		.date('Y-m-d', $timestamp)
		$sql = 'SELECT * FROM orders WHERE `status_shipping` = "1"';
		
		if($outinventory != 'default'){
			$sql .= ' AND `outinventory` = "' .$outinventory . '"';
		}
		if($shipping_method != 'default'){
			$sql .= ' AND `shipping_method`';
			if(startsWith($shipping_method, '<>')){
				$sql .= '<> "' .substr($shipping_method, 2) . '"';
			}else if (startsWith($shipping_method, 'not')){
				$sql .= ' ' . $shipping_method;	
			}else{
				$sql .= ' = "' .$shipping_method . '"';
			}
		}
		
		$sql .=  ' AND `finished` >= "' . strval($timestamp).'" AND `finished` < "' . strval(($timestamp + 3600 * 24)).'"';
		$sql .= ' ORDER BY updated DESC';
		if(isset($limit)){
			$sql .= ' LIMIT ';
			if(isset($offset)){
				$sql .= $offset .', ';
			}
			$sql .= $limit;
		}
		$result = $db->query($sql);
		$data = $result->allWithKey('oid');
		return $data;
	}
	
	function getOrdersCountByFilter($sort = null, $filter=null){
		global $db;
		$db->select('count(*) as count');
		if(isset($sort)){
			$db->orderby($sort);
		}
		if(isset($filter)){
			//@TODO handle special cases.
			foreach ($filter as $key => $value) {
				if($value == 'default'){
					continue;
				}
				if($key=='number'){
					$db->where($key.' like ', '%'. $value .'%');
				}else{
					if(startsWith($value, '<>')){
						$db->where($key.' <> ' , substr($value, 2));
					}else{
						$db->where($key, $value);
					}
				}
      		}
		}
		$result = $db->get('orders');
		$data = $result->row();
		return $data->count;
	}
	
	function getOrdersLike($sort = null, $limit = null, $filter=null, $offset = null){
		global $db;
		if(isset($sort)){
			$db->orderby($sort);
		}
		if(isset($filter)){
			//@TODO handle special cases.
			foreach ($filter as $key => $value) {
				if($value == 'default'){
					continue;
				}
				if($key=='number'){
					$db->where($key.' like ', '%'. $value .'%');
				//}else if($key == 'updated'){
				//	$db->where('date(FROM_UNIXTIME(updated))', date('Y-m-d', $value));
				}else{
					if(startsWith($value, '<>')){
						$db->where($key.' <> ' , substr($value, 2));
					}else if(startsWith($value, 'in')){
						//$db->where($key, $value, 'and', false);
						$db->where($key.' in ' , substr($value, 2), 'and', false);
					}
					else{
						$db->where($key, $value);
					}
				}
      		}
		}
		$result = $db->get('orders', $limit, $offset);
		$data = $result->allWithKey('oid');
		return $data;
	}
	
	function getOrdersByOids($set){
		global $db;
		if(isset($set)){
			$db->where('oid in', $set);
		}
		$result = $db->get('orders');
		$data = $result->allWithKey('oid');
		return $data;
	}
	
	
	function getOrderItemsWithStockInfo($oid){
		global $db;
		$db->select('orders_items.*, stock.stock_qty, attr_values.data');
		$db->from('orders_items');
		$db->join('stock', 'orders_items.p_sn = stock.p_sn and orders_items.avid = stock.avid', 'left');
		$db->join('attr_values', 'orders_items.avid = attr_values.id');
		$db->where('oid', $oid);
		$db->orderby('oid desc');
		$result = $db->get();
		$data = $result->all();
		
		foreach($data as $k=>$v){
			if(!isset($v->stock_qty)){
				$v->stock_qty = 0;
			}
			$v->data = unserialize($v->data);
			$productImages = glob("files/". $v->p_sn ."/*.*");
			if(count($productImages) > 0){
				$v->imageSource = $productImages[0];
			}else{
				if(isset($v->image_source) && $v->image_source != ''){
					$v->imageSource = $v->image_source;
				}else{
					$v->imageSource = null;
				}
			}
		}
		
		return $data;
	}
	
	
	function updateArrangedOrderItemsLackState(&$arrangedOrders, &$readyOrders, &$stockLockedItems){
		foreach ($arrangedOrders as $order){
			if ($order->communicate_time > 0) {
			    continue;
			}
		    foreach ($order->items as $orderItem){
				//get stock locked value
				//现在的库存
				//$orderItem->stock_qty;
				//现在锁定的货
				//$orderItem->current_qty;
				//需要的货
				//$orderItem->qty;
				//现在总共锁定的库存
				//$stockLockedItems[$orderItem->p_sn][$orderItem->avid];
				
				if(!key_exists($orderItem->p_sn, $stockLockedItems)){
					$stockLockedItems[$orderItem->p_sn] = array();
				}
				if(!key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = 0;
				}
				
				$needQty = $orderItem->qty - $orderItem->current_qty;
				$remainQty = $orderItem->stock_qty - $stockLockedItems[$orderItem->p_sn][$orderItem->avid];
				if($remainQty >= $needQty){
					//库存已满足需求
					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] += $needQty;
					$orderItem->current_qty = $orderItem->qty;
					$order->lack_qty = $order->lack_qty - $needQty;
				}else{
					//不够用
					$orderItem->current_qty += $remainQty;
					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $orderItem->stock_qty;
					$order->lack_qty = $order->lack_qty - $remainQty;
				}
			}
			if($order->lack_qty  == 0){
				$readyOrders[$order->oid] = $order;
				unset($arrangedOrders[$order->oid]);
			}
		}
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param $needComputingOrders
	 * @param $stockLockedItems Structure: 
	 * array{p_sn=>array(avid=>lock_qty)};
	 */
	function calculateOrderItemsLackState($needComputingOrders, &$stockLockedItems){
		//hungry theory.
		//一次性把所有的库存都拿出来处理。更新只更新做好决定的订单。
		$readyExportOrders = array();
		$needImportOrders = array();
		
		$stockInstance = Stock_Model::getInstance();
		foreach ($needComputingOrders as $order){
			$readyExport = true;
			$orderItems = $this->getOrderItemsWithStockInfo($order->oid);
			foreach ($orderItems as $orderItem){
				//get stock locked value
				$stockLockedQty = 0;
				if(key_exists($orderItem->p_sn, $stockLockedItems) && key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
					$stockLockedQty = $stockLockedItems[$orderItem->p_sn][$orderItem->avid];
				}
				$realStockQty = $orderItem->stock_qty - $stockLockedQty;
				if($realStockQty < intval($orderItem->qty - $orderItem->current_qty)){
					//库存不足。不再处理这一订单，将这一订单暂时放在下次处理列。
					$readyExport = false;
					break;
				}
			}
			if($readyExport == false){
				$needImportOrders[$order->oid] = $order;
				//load the order items for future usage.
				$order->items = $orderItems;
			}else{
				//满足了条件,即库存充足。需要更新$stockLockedItems数据。
				$readyExportOrders [$order->oid] = $order;
				//默认该订单的状态为：
				//if($order->status_handling == 0){
					$order->status_locking = 1;
					$order->status_handling = 1;
					$order->export_decision = 1;
				//}
				$order->items = $orderItems;
				foreach($orderItems as $orderItem){
					if(key_exists($orderItem->p_sn, $stockLockedItems)){
						if(key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
							//更新锁定的库存表
							$stockLockedItems[$orderItem->p_sn][$orderItem->avid] += $orderItem->qty - $orderItem->current_qty;
						}else{
							//创建该属性对应的锁定。
							$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $orderItem->qty - $orderItem->current_qty;
						}
					}else{
						//先创建锁定的p_sn，在创建该属性对应的锁定。
						$stockLockedItems[$orderItem->p_sn] = array($orderItem->avid=>($orderItem->qty - $orderItem->current_qty));
					}
					$orderItem->current_qty = $orderItem->qty;
				}
			}
		}
		//重新处理延迟处理的订单。
		foreach($needImportOrders as $order){
				$order->status_locking = 0;
				$order->status_handling = 2;
				$order->export_decision = 0;

			foreach ($order->items as $orderItem){
				//get stock locked value
				$stockLockedQty = 0;
				if(key_exists($orderItem->p_sn, $stockLockedItems) && key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
					$stockLockedQty = $stockLockedItems[$orderItem->p_sn][$orderItem->avid];
				}
				$realStockQty = $orderItem->stock_qty - $stockLockedQty;
				if($realStockQty < 0){$realStockQty = 0;}
				$needQty = $orderItem->qty - $orderItem->current_qty;

				if($realStockQty < $needQty){
					$order->lack_qty = $order->lack_qty + ($needQty - $realStockQty);
					$newLockQty = $realStockQty;
					$orderItem->current_qty = $orderItem->current_qty + $realStockQty;
				}else{
					//$newLockQty = $realStockQty - $needQty;
					$newLockQty = $needQty;
					$orderItem->current_qty = $orderItem->current_qty + $needQty;
				}
				if(key_exists($orderItem->p_sn, $stockLockedItems)){
					if(key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
						//更新锁定的库存表
						$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $stockLockedQty + $newLockQty;
					}else{
						//创建该属性对应的锁定。
						$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $newLockQty;
					}
				}else{
					//先创建锁定的p_sn，在创建该属性对应的锁定。
					$stockLockedItems[$orderItem->p_sn] = array($orderItem->avid=>$newLockQty);
				}
			}
		}
		return array('readyExportOrders'=>$readyExportOrders, 'needImportOrders'=>$needImportOrders);
	}
	
	function getAmazonOrders(){
		global $db;
		$sort = 'created desc';
		$filter = array('status_payment'=>'1', 'status_shipping <>'=>'1', 'status_handling' => '3');
		$orders = $this->getOrders($sort, null, $filter);
		foreach ($orders as $oid=>$order){
			$order->items = $this->getOrderItemsDetailByOid($oid);
		}
		return $orders;
	}
	
	function insertIntoOrderWasteBook($filters, $orders, $excluded_orders = null){
		global $db;
  		$set = array();
  		$set['createdbefore'] = isset($filters['created >='])? $filters['created >=']:null;
  		$set['createdafter'] = isset($filters['created <='])?$filters['created <=']:null;
  		$set['status'] = isset($filters['status'])?$filters['status']:'default';
  		$set['status_payment'] = isset($filters['status_payment'])?$filters['status_payment']:'1';
  		$set['payment_method'] = isset($filters['payment_method'])?$filters['payment_method']:'default';
  		$set['status_shipping'] = isset($filters['status_shipping'])?$filters['status_shipping']:'0';
  		$set['shipping_method'] = isset($filters['shipping_method'])?$filters['shipping_method']:'default';
  		$set['outinventory'] = isset($filters['outinventory'])?$filters['outinventory']:'0';
  		$set['orders'] = $orders;
  		$set['excluded_orders'] = implode(',', $excluded_orders);
  		$db->insert('order_waste_book', $set);
  		return $db->lastInsertId();
	}
	
	function getRecordFromOrderWasteBookById($owb_id){
		global $db;
		$db->where('owb_id',$owb_id);
		$result = $db->get('order_waste_book');
		$data = $result->row();
		return $data;
	}
	
	function getRecordsFromOrderWasteBook($status="default", $status_payment= '1', $payment_method = "default", 
										$status_shipping= '0', $status_method="default", $outinventory="default", $createdbefore=null, $createdafter=null){
		global $db;
		$db->where('status', $status);
		$db->where('status_payment',$status_payment);
		$db->where('payment_method', $payment_method);
		$db->where('status_shipping',$status_shipping);
		$db->where('outinventory', $outinventory);
		$db->where('createdbefore', $createdbefore);
		$db->where('createdafter',$createdafter);
		$result = $db->get('order_waste_book');
		$data = $result->all();
		return $data;
	}
}