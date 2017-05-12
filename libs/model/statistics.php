<?php
class Statistics_Model extends Common_Model
{
	const SUMMER_TIME = 43200;
	const STANDARD_TIME = 46800;
	
	private $time_season = Statistics_Model::STANDARD_TIME;
	/**
	 * @return Statistics_Model
	 * Enter description here ...
	 */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	public function QueryOrderItems($selects, $sn_prefix=null, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit = null, $groupby=null, $orderby=null){
		global $db;
		if(is_array($selects)){
			$selects = implode(',', $selects);
		}
		$db->select($selects);
		$db->from('orders_items');
		$db->join('orders', 'orders.oid = orders_items.oid');

		if(isset($sn_prefix)){
			//for items starts with MD.
			
			if(strtoupper($sn_prefix) == 'MD'){
				//$db->join('product', 'product.sn = orders_items.p_sn and product.suppliers = "Mingda"');
				$db->where('(p_sn like', '"'.$sn_prefix.'%"' . ' OR (p_sn = "7638" or p_sn="7639" or p_sn ="7640" '.
							'or p_sn="7641" or p_sn="7653" or p_sn="7654" or p_sn="7655" or p_sn = "7656" or p_sn="7657"))', 'and', false);
			}
			else{
				$db->where('p_sn like', $sn_prefix.'%');
			}
		}
		
		if(isset($status_payment)){
			$db->where('status_payment', $status_payment);
		}
		if(isset($startdate)){
			$db->where('created >=', $startdate);
		}
		if(isset($enddate)){
			$db->where('created <=', $enddate);
		}
		if(isset($sid)){
			$db->where('orders.sid', $sid);
		}
		if(isset($groupby)){
			$db->groupby($groupby);
		}
		if(isset($orderby)){
			$db->orderby($orderby);
		}
		if(isset($limit)){
			$db->limit($limit);
		}
		$result = $db->get();
		$data= $result->all();
		return $data;
	}

	public function getTotalOrdersSum($selects, $startdate=null, $enddate=null, $sid=null, $status_payment=null){
		global $db;
		if(is_array($selects)){
			$selects = implode(',', $selects);
		}
		$db->select($selects);
		if(isset($status_payment)){
			$db->where('status_payment', $status_payment);
		}
		if(isset($startdate)){
			$db->where('created >=', $startdate);
		}
		if(isset($enddate)){
			$db->where('created <=', $enddate);
		}
		if(isset($sid)){
			$db->where('sid', $sid);
		}
		if(isset($limit)){
			$db->limit($limit);
		}
		$db->from('orders');
		$result = $db->get();
		return intval($result->one());
	}

	/**
	 * 
	 * @param $selects
	 * @param $startdate
	 * @param $enddate
	 * @param $status_payment
	 * @param $limit
	 */
	public function QueryOrder($selects, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit = null, $groupby=null, $orderby=null, $join = null){
		global $db;
		if(is_array($selects)){
			$selects = implode(',', $selects);
		}
		$db->select($selects);
		$db->from('orders');
		if (isset($join)) {
			$db->join($join['table'], $join['condition']);
		}
		if(isset($status_payment)){
			$db->where('status_payment', $status_payment);
		}
		if(isset($startdate)){
			$db->where('created >=', $startdate);
		}
		if(isset($enddate)){
			$db->where('created <=', $enddate);
		}
		if(isset($sid)){
			$db->where('orders.sid', $sid);
		}
		if(isset($groupby)){
			$db->groupby($groupby);
		}
		if(isset($orderby)){
			$db->orderby($orderby);
		}
		if(isset($limit)){
			$db->limit($limit);
		}
		$result = $db->get();
		$data= $result->all();
		return $data;
	}
	
	
	public function getPopularOrderItems($sn_prefix=null, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('orders_items.p_sn as SN', 'sum(qty) as Quantity', 'sum(orders_items.total_amount) as Contribution', 'image_source as Image');
		$data = $this->QueryOrderItems($selects, $sn_prefix, $startdate, $enddate, $sid, $status_payment, $limit, 'SN', 'Quantity DESC');
		return $data;
	}
	
	public function get_product_selling_data($typeArray, $sn_prefix=null, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('orders_items.p_sn as SN', 'sum(qty) as Quantity', 'sum(orders_items.total_amount) as Contribution');
		$data = $this->QueryOrderItems($selects, $sn_prefix, $startdate, $enddate, $sid, $status_payment, $limit, 'SN', 'Quantity DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_order_revenue_data($typeArray, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('DATE(FROM_UNIXTIME(created + ' . $this->time_season . ')) as order_date', 'sum(pay_amount) as revenue', 'sum(total_amount) as product_sales', 'sum(fee_amount) as order_fees');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'order_date', 'order_date DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_order_profit_data($typeArray, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		/*
		$sql = sprintf('select order_date, revenue, IFNULL(order_fees, 0) as shipping_fee from
				(SELECT DATE(FROM_UNIXTIME(finished + 43200)) as order_date, sum(pay_amount * exchange_rate) as revenue, sum(actual_shipping_fee) as order_fees from
				orders WHERE `status_payment` = "%d" AND `finished` >= "%d" AND `finished` <= "%d" GROUP BY order_date ORDER BY order_date DESC) as t;', $status_payment, $startdate, $enddate);
		*/
		$sql = sprintf('select order_date, pay_amount, IFNULL(order_fees, 0) as actual_shipping_fee from
				(SELECT DATE(FROM_UNIXTIME(finished + %d)) as order_date, sum((fee_amount - IF(payment_method="paypal", pay_amount * 0.034 + 0.3, IF(payment_method="creditcard", pay_amount * 0.027, 0))) * exchange_rate) as pay_amount, sum(IFNULL(actual_shipping_fee,0)) as order_fees from
				orders WHERE `actual_shipping_fee` is not null and `status_payment` = "%d" AND `finished` >= "%d" AND `finished` <= "%d" GROUP BY order_date ORDER BY order_date DESC) as t;', $this->time_season, $status_payment, $startdate, $enddate);
		global $db;
		$result = $db->exec($sql);
		$data = $result->allWithKey('order_date');
		$sql = sprintf('select DATE(FROM_UNIXTIME(finished + %d)) as order_date, sum(orders_items.total_amount / orders_items.qty * orders_items.current_qty ) as sale_amount, sum(IFNULL(stock.bought_price, 0) * orders_items.current_qty) as supplier_cost 
				from orders, orders_items, stock 
				where orders.oid = orders_items.oid
				and orders.actual_shipping_fee is not null
				and orders_items.p_sn = stock.p_sn 
				and orders_items.avid = stock.avid 
				and orders.status_payment = "%d"
				and orders.finished >= "%d" 
				and orders.finished <= "%d"
				group by order_date 
				order by order_date desc',
				$this->time_season, $status_payment, $startdate, $enddate);
		$result = $db->exec($sql);
		$orderItemsData = $result->allWithKey('order_date');
		
		foreach ($data as $date => &$profitInfo) {
			if (key_exists($date, $orderItemsData)) {
				$profitInfo->pay_amount += $orderItemsData[$date]->sale_amount;
				$profitInfo->supplier_cost = $orderItemsData[$date]->supplier_cost;
				$profitInfo->profit = ($profitInfo->pay_amount) * 6 - $profitInfo->actual_shipping_fee - $profitInfo->supplier_cost;
			}
		}
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_order_validation_data($typeArray, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('DATE(FROM_UNIXTIME(created  + '. $this->time_season . ')) as order_date', 'count(*) as total_numbers', 'sum(status_payment) as deal_numbers');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'order_date', 'order_date DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_order_data_by_site($typeArray, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('sid', 'DATE(FROM_UNIXTIME(created + ' . $this->time_season .')) as order_date', 'sum(pay_amount*status_payment) as revenue', 'count(*) as total_numbers', 'sum(status_payment) as deal_numbers');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'substring_index(number,"-",1)', 'order_date DESC, revenue DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_order_product_qty_data($typeArray, $startdate=null, $enddate=null, $sid=null, $status_payment=null, $limit=null){
		$selects = array('DATE(FROM_UNIXTIME(created + ' . $this->time_season . ')) as order_date', 'sum(orders_items.qty)');
		$join = array('table' => 'orders_items', 'condition' => 'orders.oid = orders_items.oid');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'order_date', 'order_date desc', $join);
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_user_country_group_chart_data($sumType = 'count', $sumColumn, $typeArray, $startdate=null, $enddate = null, $sid=null,  $status_payment=null, $limit = null){
		
		$selects = array('delivery_country', $sumType . '('. $sumColumn.') as key_data');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'delivery_country', 'key_data DESC');
		$total_sums = $this->getTotalOrdersSum($sumType .'('.$sumColumn .') as sum', $startdate, $enddate, $sid, $status_payment);
		$sum = 0;
		foreach($data as $v){
			$position = array_search('key_data', array_keys(get_object_vars($v)));
			$sum += $this->convertValue($typeArray[$position], $v->key_data);
		}
		$jsonArray = $this->convertDBdata2JSONArray($data, $typeArray);
		$jsonArray[] = 	array('Others', $total_sums - $sum);
		return $jsonArray;
	}
	
	public function get_user_country_group_table_data($typeArray, $startdate=null, $enddate = null,$sid=null,  $status_payment=null, $limit = null){
		$selects = array('delivery_country', 'count(delivery_country) as Orders', 'sum(pay_amount) as Contribution');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'delivery_country', 'Orders DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	
	public function get_user_rank_by_orders_data($typeArray, $startdate=null, $enddate = null, $sid=null,  $status_payment=null, $limit = null){
		$selects = array('delivery_email', 'count(delivery_email) as OrderNums');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'delivery_email', 'OrderNums DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_user_rank_by_payments_data($typeArray, $startdate=null, $enddate = null, $sid=null, $status_payment=null, $limit = null){
		$selects = array('delivery_email', 'sum(pay_amount) as Amounts');
		$data = $this->QueryOrder($selects, $startdate, $enddate, $sid, $status_payment, $limit, 'delivery_email', 'Amounts DESC');
		return $this->convertDBdata2JSONArray($data, $typeArray);
	}
	
	public function get_daily_orders($startdate, $enddate){
		global $db;
	}

	public function convertDBdata2JSONArray($dbData, $typeArray){
		$dataArray = array();
		$titleElements = $this->getColumnsFromDbData($dbData);
		$dataArray[] = $titleElements;
		foreach($dbData as $k=>$v){
			$valueElements = array();
			foreach($titleElements as $k2=>$v2){
				$valueElements[] = $this->convertValue($typeArray[$k2], $v->$v2);
			}
			$dataArray[] = $valueElements;
		}
		return $dataArray;
	}
	
	public function getColumnsFromDbData($dbData){
		$columns = array();
		if(count($dbData) > 0){
			$vars = get_object_vars($dbData[key($dbData)]);
			foreach($vars as $k=>$v){
				$columns[] = $k;
			}
			return $columns;
		}
		return array();
	}
	
	private function convertValue($type, $valueStr){
		if($type == 'int'){
			$value = intval($valueStr);
			return $value;
		}
		if($type == 'float'){
			$value = floatval($valueStr);
			return $value;
		}
		if($type == 'magicstring'){
			if(startsWithNumber($valueStr)){
				$valueStr = ' '.$valueStr;
			}
			return $valueStr;
		}
		//if($type == 'date'){return date('Y-m-d', intval($valueStr));}
		if($type == 'site') {
			$siteInstance = Site_Model::getInstance();
			$siteInfo = $siteInstance->getSite($valueStr);
			return $siteInfo->name;
		}
		return $valueStr;
	}

}
