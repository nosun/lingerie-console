<?php
class Order_Controller extends MD_Controller  {
  private $_productInstance;
  private $_productSiteInstance;
  private $_siteInstance;
  private $_orderInstance;

  public function init()
  {
    $this->_productInstance = Product_Model::getInstance();
    $this->_productSiteInstance = ProductSite_Model::getInstance();
    $this->_siteInstance = Site_Model::getInstance();
    
    $this->_orderInstance = Order_Model::getInstance();
    parent::assignPageVariables();
    $this->view->assign('pageLabel', 'order');
    $this->view->assign('orderStatuses', $this->_orderInstance->getOrderStatuses());
    $this->view->assign('orderPaymentStatuses', $this->_orderInstance->getOrderPaymentStatuses());
    $this->view->assign('orderPaymentMethods', $this->_orderInstance->getOrderPaymentMethods());
    $this->view->assign('orderShippingStatuses', $this->_orderInstance->getOrderShippingStatuses());
    $this->view->assign('orderShippingMethods', $this->_orderInstance->getOrderShippingMethods());
    $this->view->assign('outInventoryStatuses', $this->_orderInstance->getOutInventoryStatuses());
  }
  public function showAction($page = 1){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  	}
  	$itemPerPage = 30;
  	$itemCount = $this->_orderInstance->getOrdersCount();
  	$orders = $this->_orderInstance->getOrders('created desc', 30, null, $itemPerPage * ($page - 1));
  	foreach($orders as $index=>$order){
  		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
  		if(!$orderItems){$orderItems = array();}
  		$order->items = $orderItems;
  		
  	  	//@TODO get order total qty. This can be optimized.
  		$order->total_qty = 0;
  		foreach($orderItems as $orderItem){
  			$order->total_qty += $orderItem->qty;
  		}
  		
  	}
  	$this->view->assign('orders', $orders);
  	$this->view->assign('pagination', pagination('order/show/%d', ceil($itemCount/$itemPerPage), $page));
    $this->view->render('order/order.tpl');
  }
  
  public function syncallAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	//sync all orders. (make sure only used in the first run of each site).
    $sites = $this->_siteInstance->getAllSites();

  	$communicationInstance = Communication_Model::getInstance();
  	foreach($sites as $index => $site){
  		$communicationInstance->syncOrders($site->id, 0, time());
  	}
  }
  
  public function syncordersAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}

    $sites = $this->_siteInstance->getAllSites();

    //get MAX created value. because order can not be created from console.
    $latestTime = $this->_orderInstance->getLatestCreatedOrderTime();
    
  	$communicationInstance = Communication_Model::getInstance();
  	foreach($sites as $index => $site){
  		//@TODO if date after 2036, this should be fixed.
  		//obtain from the day before last updated.
  		$communicationInstance->syncOrders($site->id, intval($latestTime) - 3600*24, time());
  	}
  	
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
    	header('Location: ' . $_SERVER['HTTP_REFERER'], true);
    	exit;
    } else {
    	gotoUrl('order/orderfilter');
    }
  }
  
  public function ordersearchAction($page = 1) {
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$this->view->assign('pageLabel', 'ordersearch');
  	$filters = array();
  	$orders = array();
  	if($this->isPost()){
  		$post = $_POST;
  	
  		if(!isset($post['clear_filters'])){
  			//if(isset($post['name_filter']) && $post['name_filter'] != ''){$filters['name'] = $post['name_filter'];}
  			if(isset($post['number_pre_filter']) && $post['number_pre_filter'] != '' && $post['number_pre_filter']!='订单前缀'){$filters['number'] = trim($post['number_pre_filter']);}
  			if(isset($post['delivery_email_filter']) && $post['delivery_email_filter'] != '' && $post['delivery_email_filter'] != '客户Email'){$filters['delivery_email'] = trim($post['delivery_email_filter']);}
  			if(isset($post['createdafter_filter']) && $post['createdafter_filter'] != ''){$filters['created >='] = strtotime($post['createdafter_filter']);}
  			//if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['created <='] = strtotime($post['createdbefore_filter'] ) + 3600*24 ;}
  			if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['created <='] = strtotime($post['createdbefore_filter']) + (3600* 24 - 1);}
  			if(isset($post['status_filter']) && $post['status_filter'] != ''){$filters['status'] = $post['status_filter'];}
  			if(isset($post['status_payment_filter']) && $post['status_payment_filter'] != ''){$filters['status_payment'] = $post['status_payment_filter'];}
  			if(isset($post['payment_method_filter']) && $post['payment_method_filter'] != ''){$filters['payment_method'] = $post['payment_method_filter'];}
  			if(isset($post['status_shipping_filter']) && $post['status_shipping_filter'] != ''){$filters['status_shipping'] = $post['status_shipping_filter'];}
  			if(isset($post['shipping_method_filter']) && $post['shipping_method_filter'] != ''){$filters['shipping_method'] = $post['shipping_method_filter'];}
  			if(isset($post['outinventory_filter']) && $post['outinventory_filter'] != ''){$filters['outinventory'] = $post['outinventory_filter'];}
  		}
  		$orders = $this->_orderInstance->getOrdersLike('created DESC', null, $filters);
  		$orders_pools = cache::get('order_memento');
  		if (!empty($orders_pools)) {
  			$orders_pools = $orders_pools->data;
  		}
  		foreach($orders as $index=>$order){
  			$in_cache = false;
  			if (!empty($orders_pools)) {
	  			foreach ($orders_pools as $pool_name => $order_list) {
	  				if (key_exists($order->oid, $order_list)) {
	  					$orders[$order->oid] = $order_list[$order->oid];
	  					$in_cache = true;
	  					break;
	  				}
	  			}
  			}
  			if (!$in_cache) {
	  			$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
	  			if(!$orderItems) {
	  				$orderItems = array();
	  			}
	  			$orders[$order->oid]->items = $orderItems;
  			} 
  			//@TODO get order total qty. This can be optimized.
  			$orders[$order->oid]->total_qty = 0;
  			foreach($orders[$order->oid]->items as $orderItem){
  				$orders[$order->oid]->total_qty += $orderItem->qty;
  			}
  		}
  	} else {
  		$filters = array('created >='=>strtotime(date('m/d/Y', 0)),
  			'created <='=>strtotime(date('m/d/Y', time())) + (3600*24 - 1),
  			'status'=>'default',
  			'status_payment'=>'default',
  			'payment_method'=>'default',
  			'status_shipping'=>'default',
  			'shipping_method'=>'default',
  			'outinventory'=>'default');
  	}
  	$this->view->assign('orders', $orders);
  	$this->view->assign('filters', $filters);
  	$this->view->assign('pagination', pagination('order/orderfilter/%d', 1, 1));
  	
  	$this->view->render('order/search_order.tpl');
  }
  public function orderfilterAction($page = 1){
   	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    $this->view->assign('pageLabel', 'orderfilter');
  	
  	if($this->isPost()){
  		$post = $_POST;
  		$this->clearSessionOrderFilterData();

  		if(!isset($post['clear_filters'])){
	  	  	if(isset($post['page'])){
	  			$page = $post['page'];
	  			gotoUrl('product/orderfilter/'.$page);
	  		}
	  		$filters = array();
	  		//if(isset($post['name_filter']) && $post['name_filter'] != ''){$filters['name'] = $post['name_filter'];}
		  	if(isset($post['number_pre_filter']) && $post['number_pre_filter'] != '' && $post['number_pre_filter']!='订单前缀'){$filters['number'] = $post['number_pre_filter'];}
		  	if(isset($post['createdafter_filter']) && $post['createdafter_filter'] != ''){$filters['created >='] = strtotime($post['createdafter_filter']);}
		  	//if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['created <='] = strtotime($post['createdbefore_filter'] ) + 3600*24 ;}
  			if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['created <='] = strtotime($post['createdbefore_filter']) + (3600* 24 - 1);}
		  	if(isset($post['status_filter']) && $post['status_filter'] != ''){$filters['status'] = $post['status_filter'];}
	  		if(isset($post['status_payment_filter']) && $post['status_payment_filter'] != ''){$filters['status_payment'] = $post['status_payment_filter'];}
	  		if(isset($post['payment_method_filter']) && $post['payment_method_filter'] != ''){$filters['payment_method'] = $post['payment_method_filter'];}
		  	if(isset($post['status_shipping_filter']) && $post['status_shipping_filter'] != ''){$filters['status_shipping'] = $post['status_shipping_filter'];}
		  	if(isset($post['shipping_method_filter']) && $post['shipping_method_filter'] != ''){$filters['shipping_method'] = $post['shipping_method_filter'];}
  			if(isset($post['outinventory_filter']) && $post['outinventory_filter'] != ''){$filters['outinventory'] = $post['outinventory_filter'];}
	  		$_SESSION['order_filters'] = $filters;
  		}
  	}

  	
  	$filters = $this->getOrderFilters();
  	
    //update latest order.
    $sites = $this->_siteInstance->getAllSites();
    //get MAX created value. because order can not be created from console.
    $startTime = $filters['created >='];
    $endTime = $filters['created <='];
    //$latestTime = strtotime($this->_orderInstance->getLatestCreatedOrderTime());
  	$communicationInstance = Communication_Model::getInstance();
    foreach($sites as $index => $site){
  		//@TODO if date after 2036, this should be fixed.
  		//obtain from the day before last updated. but currently no push action for orders, so need sync the range we searched.
  		//$communicationInstance->syncOrders($site->id, intval($latestTime) - 3600*24, time());
  		$communicationInstance->syncOrders($site->id, intval($startTime) - 3600*24, intval($endTime) + 3600*24);
  	}
  	
  	//getProducts($order = null, $limit = null, $filter=null, $offset = null)
  	
  	/*Tempororily Disable The Page Break******

  	$itemPerPage = 50;
  	$itemCount = $this->_orderInstance->getOrdersCountByFilter('created DESC', $filters);
  	$orders = $this->_orderInstance->getOrdersLike('created DESC', $itemPerPage, $filters, $itemPerPage * ($page - 1));
  	
	*****End Of Tempororily Disable The Page Break*/

  	$orders = $this->_orderInstance->getOrdersLike('created DESC', null, $filters);
  	
  	/* 如果订单在缓存中采用缓存中的数据 */
  	$orders_pools = cache::get('order_memento');
  	if (isset($orders_pools)) {
  		$orders_pools = $orders_pools->data;
  	}
  	foreach($orders as $index=>$order){
  		$in_cache = false;
  		if (isset($orders_pools)) {
  			foreach ($orders_pools as $pool_name => $order_list) {
  				if (key_exists($order->oid, $order_list)) {
  					$orders[$order->oid] = $order_list[$order->oid];
  					$in_cache = true;
  					break;
  				}
  			}
  		}
  		if (!$in_cache) {
  			$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
  			if(!$orderItems) {
  				$orderItems = array();
  			}
  			$orders[$order->oid]->items = $orderItems;
  		}
  		//@TODO get order total qty. This can be optimized.
  		$orders[$order->oid]->total_qty = 0;
  		foreach($orders[$order->oid]->items as $orderItem){
  			$orders[$order->oid]->total_qty += $orderItem->qty;
  		}
  	}
  	
  	$excluded_orders = isset($_SESSION['excluded_orders'])?$_SESSION['excluded_orders']:array();
  	
  	$this->view->assign('excluded_orders', $excluded_orders);
  	$this->view->assign('orders', $orders);
  	$this->view->assign('filters', $filters);
  	
  	/*Tempororily Disable The Page Break******
  	$this->view->assign('pagination', pagination('order/orderfilter/%d', ceil($itemCount/$itemPerPage), $page));
	*****End Of Tempororily Disable The Page Break*/
  	
  	$this->view->assign('pagination', pagination('order/orderfilter/%d', 1, 1));
    $this->view->render('order/filter_order.tpl');
  }

  
  public function filterbyitemremoveAction(){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		if(!isset($_SESSION['excluded_orders'])){
  			$_SESSION['excluded_orders'] = array();
  		}
  		//added to session excluded orders.
  		$_SESSION['excluded_orders'][] = $post['oid'];
  	}
  }
  
  public function filterbyitemaddAction(){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		if(isset($_SESSION['excluded_orders'])){
  			//if already have this value been excluded.
  			$index = array_search($post['oid'], $_SESSION['excluded_orders']);
  			if($index !== false){
  				unset($_SESSION['excluded_orders'][$index]);
  				if(count($_SESSION['excluded_orders']) == 0){
  					unset($_SESSION['excluded_orders']);
  				}
  			}
  		}
  	}
  }
  
  
  public function processedAction($page=1){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    $this->view->assign('pageLabel', 'orderprocessed');
    
    $filters = array();
  	if($this->isPost()){
  		$post = $_POST;
  		$this->clearSessionShippedOrderByDateFilterData();
  		if(!isset($post['clear_filters'])){
	  		
	  	  	if(isset($post['page'])){
	  			$page = $post['page'];
	  			gotoUrl('order/processed/'.$page);
	  		}
		  	if(isset($post['updated_filter']) && $post['updated_filter'] != ''){$filters['updated'] = strtotime($post['updated_filter']);}
		  	$filters['status'] = 2;
	  		$filters['status_payment'] = 1;
		  	$filters['status_shipping'] = 1;
  			if(isset($post['shipping_method_filter']) && $post['shipping_method_filter'] != ''){$filters['shipping_method'] = $post['shipping_method_filter'];}
	  		if(isset($post['outinventory_filter']) && $post['outinventory_filter'] != ''){$filters['outinventory'] = $post['outinventory_filter'];}
		  	$_SESSION['shipped_order_filters'] = $filters;
  		}
  	}
  	
  	$filters = $this->getOrderShippingDateFilter();
  	
    //update latest order.
    $sites = $this->_siteInstance->getAllSites();
    //get MAX created value. because order can not be created from console.
    $updated = $filters['updated'];

    //$latestTime = strtotime($this->_orderInstance->getLatestCreatedOrderTime());
  	$communicationInstance = Communication_Model::getInstance();
    foreach($sites as $index => $site){
  		//@TODO if date after 2036, this should be fixed.
  		//obtain from the day before last updated. but currently no push action for orders, so need sync the range we searched.
  		//$communicationInstance->syncOrders($site->id, intval($latestTime) - 3600*24, time());
  		$communicationInstance->syncOrders($site->id, intval($updated) - 3600*24, intval($updated) + 3600*24);
  	}
  	
  	//getProducts($order = null, $limit = null, $filter=null, $offset = null)
  	
  	
  	
  	
  if(!isset($page)){$page = 1;}
  
  
  	/******Tempororily Disable The Page Break**********

  	$itemPerPage = 50;
  	$itemCount = $this->_orderInstance->getOrdersCountByCompleteDate($filters['updated'], $filters['outinventory'], $filters['shipping_method']);
  	$orders = $this->_orderInstance->getOrdersByCompleteDate($filters['updated'], $filters['outinventory'], $filters['shipping_method'], $itemPerPage, $itemPerPage * ($page - 1));

	*******End Of Tempororily Disable The Page Break*/
  	
  	$orders = $this->_orderInstance->getOrdersByCompleteDate($filters['updated'], $filters['outinventory'], $filters['shipping_method']);
  	
    foreach($orders as $index=>$order){
  		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
    	if(!$orderItems){$orderItems = array();}
  		$order->items = $orderItems;
  		
      	//@TODO get order total qty. This can be optimized.
  		$order->total_qty = 0;
  		foreach($orderItems as $orderItem){
  			$order->total_qty += $orderItem->qty;
  		}
  	}
  	$this->view->assign('orders', $orders);
  	$this->view->assign('filters', $filters);
  	
  	/******Tempororily Disable The Page Break**********
  	//$this->view->assign('pagination', pagination('order/processed/%d', ceil($itemCount/$itemPerPage), $page));
	*******End Of Tempororily Disable The Page Break*/
  	
  	$this->view->assign('pagination', pagination('order/processed/%d', 1, 1));
    $this->view->render('order/order_processed.tpl');
  }

  public function generateorderprintAction2(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$orders = $this->getOrdersBySessionFilter();
  	//start to print orders.
  	$this->view->assign('orders', $orders);
  	$this->view->render('order/order_print.tpl');
  }
  
  public function generateorderprintAction($pool_name){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$orders_pools = cache::get('order_memento');
  	if($orders_pools){
  		$orders_pools = $orders_pools->data;
  		
  		//获得准备出货列表
  		$orders = $orders_pools[$pool_name];
  		$this->view->assign('orders', $orders);
  		$this->view->render('order/order_print.tpl');
  	}
  }
  
  public function generateoosprintAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	//获得缺货列表。从memento中。
  	$orders_pools = cache::get('order_memento');
  	if($orders_pools){
  		$orders_pools = $orders_pools->data;
  		
  		//获得缺货list.
  		$orders = $orders_pools['needImportOrders'];
	  	//$orders = $this->getOrdersBySessionFilter();
	  	$pOrderSums = $this->getProductSummaryByOrders($orders);
	  	//$pOrderSums = $this->getLackStockProducts2($pOrderSums);
	
	  	//these 2 variables use supplier as index
	  	$goodsShortage = array();
	  	$totalLackStylesCounts = array();

	  	$clearanceList = MD_Config::get('clearance', array());
	  	foreach ($pOrderSums as $p_sn=>$pOrderSummary){
	  		//只处理缺货的订单。
	  		//首先去除不需要的项。
	  		foreach($pOrderSummary->requirements as $avid=>$stock_data){
	  			if($stock_data->lack_qty <= 0){
	  				//不缺这个货，将它从目录中去掉。
	  				unset($pOrderSummary->requirements[$avid]);
	  			} else {
	  				if(!in_array($p_sn, $clearanceList)) {
	  					$average_qty = intval(Stock_Model::getInstance()->getAverageSalesBySn($p_sn, $avid));
	  					$pOrderSummary->requirements[$avid]->predict_qty = $stock_data->lack_qty + $average_qty;
	  				} else {
	  					$pOrderSummary->requirements[$avid]->predict_qty = $stock_data->lack_qty;
	  				}
	  			}
	  		}
	  		
	  		if(count($pOrderSummary->requirements) == 0){continue;}
	  		
	  		//get suppliers and supplier_sn
	  		$product = $this->_productInstance->getProductBySn($p_sn);
	  		$pOrderSummary->product = $product;
	  		
	  		if(!$product){
	  			//该产品不在终端中
	  			if(!key_exists('UNCATEGORIZED', $goodsShortage)){
	  				$goodsShortage['UNCATEGORIZED'] = array();
	  				$totalLackStylesCounts['UNCATEGORIZED'] = 0;
	  			}
	  			$goodsShortage['UNCATEGORIZED'][$p_sn] = $pOrderSummary;
	  			$totalLackStylesCounts['UNCATEGORIZED'] +=  $this->getStyleCountByProductOrderSummary($pOrderSummary);
	  			
	  		}else{
	  			//if this product exists in our system.
		  		$suppliers = explode(',', $product->suppliers);
		  		if(isset($suppliers) && count($suppliers) > 0){
		  			$supplier= trim($suppliers[0]);
		  			if(!key_exists($supplier, $goodsShortage)){
		  				$goodsShortage[$supplier] = array();
		  				$totalLackStylesCounts[$supplier] = 0;
		  			}
		  			$goodsShortage[$supplier][$p_sn] = $pOrderSummary;
		  			$totalLackStylesCounts[$supplier] +=  $this->getStyleCountByProductOrderSummary($pOrderSummary);
		  		}else{
		  			if(!key_exists('UNCATEGORIZED', $goodsShortage)){
		  				$goodsShortage['UNCATEGORIZED'] = array();
		  				$totalLackStylesCounts['UNCATEGORIZED'] = 0;
		  			}
		  			$goodsShortage['UNCATEGORIZED'][$p_sn] = $pOrderSummary;
		  			$totalLackStylesCounts['UNCATEGORIZED'] +=  $this->getStyleCountByProductOrderSummary($pOrderSummary);
		  		}
	  		}
	  	}
	  	/*
	  	if(isset($goodsShortage['UNCATEGORIZED'])){
		  	$noSupplierGoods = $goodsShortage['UNCATEGORIZED'];
			unset($goodsShortage['UNCATEGORIZED']);
			$goodsShortage['UNCATEGORIZED'] = $noSupplierGoods;
	  	}*/
	  	$this->view->assign('goodsShortage', $goodsShortage);
	  	$this->view->assign('totalLackStylesCounts', $totalLackStylesCounts);
	  	$this->view->render('order/order_needsupply.tpl');
  	}
  }
  
  public function generateproductlackfileAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	//获得缺货列表。从memento中。
  	$orders_pools = cache::get('order_memento');
  	if($orders_pools){
  		$orders_pools = $orders_pools->data;
  		
  		//获得缺货list.
  		$orders = $orders_pools['needImportOrders'];
	  	//$orders = $this->getOrdersBySessionFilter();
	  	$pOrderSums = $this->getProductSummaryByOrders($orders);
	  	//$pOrderSums = $this->getLackStockProducts2($pOrderSums);
	
	  	//these 2 variables use supplier as index
	  	$goodsShortage = array();
	  	$totalLackStylesCounts = array();
	  	
	  	$output_arr = array();
	  	$output_arr[] = array('sn','color','size','lack_qty', 'predict');

	  	foreach ($pOrderSums as $p_sn=>$pOrderSummary){
	  		//只处理缺货的订单。
	  		//首先去除不需要的项。
	  		foreach($pOrderSummary->requirements as $avid=>$stock_data){
	  			if($stock_data->lack_qty <= 0){
	  				//不缺这个货，将它从目录中去掉。
	  				unset($pOrderSummary->requirements[$avid]);
	  			}else{
	  				//
	  				$attrs = array('color'=>'', 'size'=>'');
	  				foreach ($stock_data->data as $prop_key => $prop_value){
	  					if(strpos($prop_key, 'olor')){
	  						$attrs['color'] = $prop_value;
	  					}else if(strpos($prop_key, 'ize')){
	  						$attrs['size'] = $prop_value;
	  					}
	  				}
	  				if(startsWithNumber($p_sn)){
						$formatted_psn = ' '.$p_sn;
					}else{
						$formatted_psn = $p_sn;
					}
					$averageSales = intval(Stock_Model::getInstance()->getAverageSalesBySn($p_sn, $avid));
	  				$output_arr[] = array($formatted_psn, $attrs['color'], $attrs['size'], $stock_data->lack_qty, $stock_data->lack_qty + $averageSales);
	  			}
	  		}
	  		if(count($pOrderSummary->requirements) == 0){continue;}
	  	}
	  	$filename = 'lack_stock-'.strval(TIMESTAMP).'.csv';
	    download_send_headers($filename);
	    outputCSV($output_arr);
  	}
  }
  
  public function generateaddressprintAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    //need to clear the session settings for the filter.
    $orders = $this->getOrdersByOrderShippingDateFilter();
    
  	foreach ($orders as $k=>$order){
  		// 去除掉亚马逊发货订单
  	    if ($order->status_handling == 3) {
  		    unset($orders[$k]);
  		}
  	    $order->total_qty = 0;
  		foreach($order->items as $index=>$orderItem){
  			$order->total_qty += $orderItem->qty;
  		}
  	}
  	$filters = $this->getOrderShippingDateFilter();
  	$this->view->assign('filters', $filters);
  	$this->view->assign('orders', $orders);
  	$this->view->render('order/order_shipaddress.tpl');
  }
  
  public function generateaddressprintinlineAction(){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	//need to clear the session settings for the filter.
  	$orders = $this->getOrdersByOrderShippingDateFilter();
  
  	foreach ($orders as $k=>$order){
  		// 去除掉亚马逊发货订单
  		if ($order->status_handling == 3) {
  			unset($orders[$k]);
  		}
  		$order->total_qty = 0;
  		foreach($order->items as $index=>$orderItem){
  			$order->total_qty += $orderItem->qty;
  		}
  	}
  	
  	$filters = $this->getOrderShippingDateFilter();
  	/*
  	$this->view->assign('filters', $filters);
  	$this->view->assign('orders', $orders);
  	$this->view->render('order/order_shipaddress_inline.tpl');*/
  	ob_clean();
  	require_once LIBPATH . '/thirdparty/PHPExcel.php';
  	$now = new DateTime();
  	$address_book = new PHPExcel();
  	$work_sheet = $address_book->getActiveSheet();
  	$orderShippingMethods = $this->_orderInstance->getOrderShippingMethods();
  	$work_sheet->setCellValue('E1', '地址文档(' . $orderShippingMethods[$filters['shipping_method']] . ')');
  	$work_sheet->mergeCells('E1:F1');
  	$work_sheet->getDefaultColumnDimension()->setAutoSize(true);
  	$orderIndex = 2;
  	foreach ($orders as $index=>$order) {
  		$delivery_mobile = '';
  		if (isset($order->delivery_mobile)) {
  			$delivery_mobile = $order->delivery_mobile;
  		} else if (isset($order->delivery_phone)) {
  			$delivery_mobile = $order->delivery_phone;
  		}
  		$work_sheet->setCellValue('A' . $orderIndex, $order->number);
  		$work_sheet->setCellValue('B' . $orderIndex, $order->total_qty);
  		$work_sheet->setCellValue('C' . $orderIndex, $order->delivery_last_name);
  		$work_sheet->setCellValue('D' . $orderIndex, $order->delivery_first_name);
  		$work_sheet->setCellValue('E' . $orderIndex, $order->delivery_address);
  		$work_sheet->setCellValue('F' . $orderIndex, $order->delivery_city);
  		$work_sheet->setCellValue('G' . $orderIndex, $order->delivery_province);
  		$work_sheet->setCellValueExplicit('H' . $orderIndex, $order->delivery_postcode, PHPExcel_Cell_DataType::TYPE_STRING);
  		$work_sheet->setCellValue('I' . $orderIndex, $order->delivery_country);
  		$work_sheet->setCellValueExplicit('J' . $orderIndex, $delivery_mobile, PHPExcel_Cell_DataType::TYPE_STRING);
  		$orderIndex++;
  	}
  	$outputFileName = sprintf("下单表_%s.xlsx", date('Y-m-d', time()));
  	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  	header('Content-Disposition: attachment;filename="' . $outputFileName . '"');
  	header('Cache-Control: max-age=0');
  	 
  	$objWriter = PHPExcel_IOFactory::createWriter($address_book, 'Excel2007');
  	$objWriter->save('php://output');
  }
  
  public function updateStockAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
    //need to clear the session settings for the filter.
  	$all_orders = $this->getOrdersByOrderShippingDateFilter();

  	//获得未出库订单。
  	$target_orders = array();
  	$amazon_orders = array();
  	foreach($all_orders as $order){
  		if($order->outinventory == '0' && $order->status_handling != '-2'){
  			if($order->status_handling == '3'){
  				$amazon_orders[] = $order;	
  			}else{
  				$target_orders[] = $order;
  			}
  		}
  	}
  	$pOrderSums = $this->getProductSummaryByOrders($target_orders);
  	$this->updateStock($pOrderSums);
  	foreach ($target_orders as $order){
  		$this->_orderInstance->updateOrderOutInventory($order->oid, '1');
  	}
  	
  	foreach($amazon_orders as $order){
  		$this->_orderInstance->updateOrderOutInventory($order->oid, '1');
  	}
  	
  	//last step is clear restored session data.
  	$this->clearSessionShippedOrderByDateFilterData();
  	
  	//remove the cache. Next time will recompute orders.
  	cache::remove('order_memento');
  	cache::remove('order_memento_stocklocks');
  	
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
    	header('Location: ' . $_SERVER['HTTP_REFERER'], true);
    	exit;
    } else {
    	gotoUrl('order/processed');
    }
  }
  
  public function orderhandlingAction(){
  	global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	$this->view->assign('pageLabel', 'orderhandling');
  	$orders_pools = cache::get('order_memento');
  	
  	if(!$orders_pools){
  		list($orders_pools, $stockLockedItems) = $this->computeIncompleteHandlingOrders();
	  	//数据保存两个月
	  	cache::save('order_memento', $orders_pools, 5184000);
	  	cache::save('order_memento_stocklocks', $stockLockedItems, 5184000);

  	}else{
  		$amazonOrders = $this->_orderInstance->getAmazonOrders();
  		$this->view->assign('amazonOrders', $amazonOrders);
  		$orders_pools = $orders_pools->data;
  	}

  	$this->view->assign('orders_pools', $orders_pools);
  	$this->view->render('order/order_handling.tpl');
  }
  
  public function changelockstateAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		$orders_pools = cache::get('order_memento');
  		$status_locking = 0;
  		if($post['lock_state'] == 'locked'){
  			$status_locking = 1;
  		}
  		
  		if($orders_pools){
  			$orders_pools = $orders_pools->data;
  			$orders_pools[$post['pool']][$post['oid']]->status_locking = $status_locking;
  			cache::save('order_memento', $orders_pools, 5184000);
  		}else{
  			$this->_orderInstance->updateOrderConsoleFields($post['oid'], array('status_locking'=>$status_locking));
  		}
  	}
  }

  public function movetotrashAction(){
  	global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
    if($this->isPost()){
  		$post = $_POST;
  		$orders_pools = cache::get('order_memento');
  		if($orders_pools){
  			$orders_pools = $orders_pools->data;
  			$trashOrder = $orders_pools['ommitedOrders'][$post['oid']];
  			unset($orders_pools['ommitedOrders'][$post['oid']]);
  			cache::save('order_memento', $orders_pools, 5184000);
  		}
  		$this->_orderInstance->updateOrderConsoleFields($post['oid'], array('status_handling'=>'-2'));
  	}
  }

  /**
   * array{p_sn=>array(avid=>lock_qty)};
   * Enter description here ...
   */
  public function fillnewitemsAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		$orders_pools = cache::get('order_memento');
  		$stockLockedItems = cache::get('order_memento_stocklocks');
  		
  		if($orders_pools){
  			$orders_pools = $orders_pools->data;
  			$stockLockedItems = $stockLockedItems->data;
  			
  		  	if(!key_exists($post['oid'], $orders_pools[$post['pool']])){
  				echo 'ERROR: 要更新的订单尚未保存。请首先保存设定。';
  				return;
  			}
  			$orderInfo = $orders_pools[$post['pool']][$post['oid']];
  			$orderItems = $orderInfo->items;
  			foreach($orderItems as $index=>$orderItem){
  				if($orderItem->current_qty == $orderItem->qty){
  					//已经全部满足。
  					continue;
  				}
  				if($orderInfo->lack_qty == 0){
  					break;
  				}
  				
  				if(!key_exists($orderItem->p_sn, $stockLockedItems)){
  					$stockLockedItems[$orderItem->p_sn] = array();
  				}
  				if(!key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
  					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = 0;
  				}
  				$locked_qty = $stockLockedItems[$orderItem->p_sn][$orderItem->avid];
  				$stockInstance = Stock_Model::getInstance();
  				$stockQty = $stockInstance->getStockQty($orderItem->p_sn, $orderItem->avid);
  				$needQty = $orderItem->qty - $orderItem->current_qty;
  				$availableQty = $stockQty - $locked_qty;
  				if($needQty > $availableQty){
  					$orderItem->current_qty = $orderItem->current_qty + $availableQty;
  					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $locked_qty + $availableQty;
  					$orderInfo->lack_qty = $orderInfo->lack_qty - $availableQty;
  				}else{
  					$orderItem->current_qty = $orderItem->current_qty + $needQty;
  					$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $locked_qty + $needQty;
  					$orderInfo->lack_qty = $orderInfo->lack_qty - $needQty;
  				}
  			}
  			
  			//如果已经填充满，则将其移到ready队列。
  		  	if($orderInfo->lack_qty > 0){
  				$orderInfo->status_handling = 2;
  				$orderInfo->export_decision = 0;
  				$this->view->assign('poolName', 'arrangedOrders');
  			}else{
  				$orderInfo->status_handling = 1;
  				$orderInfo->export_decision = 1;
  				$orders_pools['readyExportOrders'][$orderInfo->oid] = $orderInfo;
  				unset($orders_pools['arrangedOrders'][$orderInfo->oid]);
  				$this->view->assign('poolName', 'readyExportOrders');
  			}
  			
  			//存储新的信息。
		  	cache::save('order_memento', $orders_pools, 5184000);
		  	cache::save('order_memento_stocklocks', $stockLockedItems, 5184000);
  			
  			//更新数据库。
  			$this->_orderInstance->updateOrderConsoleFields($orderInfo->oid, 
  				array('status_locking' => '1', 
  					  'status_handling'=> $orderInfo->status_handling, 
  					  'export_decision'=> $orderInfo->export_decision,
  					  'lack_qty' => $orderInfo->lack_qty
  				));
  			$this->_orderInstance->updateOrderItemsLockQty($orderInfo->items, $orderInfo->oid);
  			
  			
  			$this->view->assign('order', $orderInfo);
  			$this->view->render('order/order_handling_item.tpl');
  		}
  	}
  }
  
  private function updateOrderLockedItems(&$orderInfo, &$stockLockedItems){
    	$orderItems = $orderInfo->items;
  		foreach($orderItems as $index=>$orderItem){
  			if($orderItem->current_qty == $orderItem->qty){
  				//已经全部满足。
  				continue;
  			}
  			if($orderInfo->lack_qty == 0){
  				break;
  			}
  			
  			if(!key_exists($orderItem->p_sn, $stockLockedItems)){
  				$stockLockedItems[$orderItem->p_sn] = array();
  			}
  			if(!key_exists($orderItem->avid, $stockLockedItems[$orderItem->p_sn])){
  				$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = 0;
  			}
  			$locked_qty = $stockLockedItems[$orderItem->p_sn][$orderItem->avid];
  			$stockInstance = Stock_Model::getInstance();
  			$stockQty = $stockInstance->getStockQty($orderItem->p_sn, $orderItem->avid);
  			$needQty = $orderItem->qty - $orderItem->current_qty;
  			$availableQty = $stockQty - $locked_qty;
  			if($needQty > $availableQty){
  				$orderItem->current_qty = $orderItem->current_qty + $availableQty;
  				$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $locked_qty + $availableQty;
  				$orderInfo->lack_qty = $orderInfo->lack_qty - $availableQty;
  			}else{
  				$orderItem->current_qty = $orderItem->current_qty + $needQty;
  				$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $locked_qty + $needQty;
  				$orderInfo->lack_qty = $orderInfo->lack_qty - $needQty;
  			}
  		}
  		
  		//更新数据库。
  		$this->_orderInstance->updateOrderConsoleFields($orderInfo->oid, 
  			array('status_locking' => '1', 
  				  'status_handling'=> $orderInfo->status_handling, 
  				  'export_decision'=> $orderInfo->export_decision,
  				  'lack_qty' => $orderInfo->lack_qty
  			));
  		$this->_orderInstance->updateOrderItemsLockQty($orderInfo->items, $orderInfo->oid);
  }
  
  public function batchorderlockeditemsupdateAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}

  	$orders_pools = cache::get('order_memento');
  	$stockLockedItems = cache::get('order_memento_stocklocks');
  	if($orders_pools){
	  	$orders_pools = $orders_pools->data;
	  	$stockLockedItems = $stockLockedItems->data;
	  	$orderInfo=end($orders_pools['arrangedOrders']);
	  	do {
	  	  		$this->updateOrderLockedItems($orderInfo, $stockLockedItems);
	  	  		//如果已经填充满，则将其移到ready队列。
	  		  	if($orderInfo->lack_qty > 0){
	  				$orderInfo->status_handling = 2;
	  				$orderInfo->export_decision = 0;
	  			}else{
	  				$orderInfo->status_handling = 1;
	  				$orderInfo->export_decision = 1;
	  				$orders_pools['readyExportOrders'][$orderInfo->oid] = $orderInfo;
	  				unset($orders_pools['arrangedOrders'][$orderInfo->oid]);
	  			}
		} while ($orderInfo=prev($orders_pools['arrangedOrders']));
		
		//change the array pointer back to normal.
	  	reset($orders_pools['arrangedOrders']);

	  	//数据保存两个月
	  	cache::save('order_memento', $orders_pools, 5184000);
	  	cache::save('order_memento_stocklocks', $stockLockedItems, 5184000);

	  	$this->view->assign('orders_pools', $orders_pools);
	  	$this->view->render('order/order_handling_pools.tpl');
  	}
  }
  
  
  
  /**
   * status_handling: 1=> 不缺货, 2=>缺货, 0=>未处理,  -1=>忽略, -2=>回收站, 3=> amazon.
   * status_locking: 0=>未锁定, 1=>锁定.
   * export_decision: 0=>不发货, 1=>发货.
   * incompletedOrders: status_payment = 1, status_shipping = 0;
   * Enter description here ...
   */
  public function refreshandcomputeAction(){
      global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		
  		//$this->batchorderlockeditemsupdateAction();
  		
  		$post = $_POST;

  		$orders_pools = cache::get('order_memento');
  		$stockLockedItems = cache::get('order_memento_stocklocks');
  		//get order lists for different pool.
  		if($orders_pools){
		  	$orders_pools = $orders_pools->data;
		  	//start the procedures for computing the stock arrangement.

	  		if(isset($post['json'])){
		  		$rep_changed_orders = json_decode($post['json']);
		  		//oid=>{old=>'', new=>'', new_lockstate=>''}
		  		foreach ($rep_changed_orders as $order_key=>$deltaData){
		  			$order_key_segs = explode('_', $order_key);
		  			$oid = $order_key_segs[1];
		  			$orderInfo = $orders_pools[$deltaData->old][$oid];
		  			unset($orders_pools[$deltaData->old][$oid]);
		  			$orders_pools[$deltaData->new][$oid] = $orderInfo;
		  			$orderInfo->status_locking = $deltaData->new_lockstate;
		  		}
	  		}
	  		
	  		//更新数据库。
	  		foreach($orders_pools as $pool_type => $orders){
	  			if($pool_type == 'readyExportOrders'){
	  				foreach($orders as $oid=>$order){
	  					if($order->status_handling < 1){
	  						$order->status_handling = 0;
	  						$order->status_locking = 0;
	  					}else if($order->lack_qty > 0){
	  						$order->status_handling = 2;
	  					}else{
	  						$order->status_handling = 1;
	  					}
	  					$this->_orderInstance->updateOrderConsoleFields($oid, 
	  						array('status_locking' => $order->status_locking, 
	  							  'status_handling'=> $order->status_handling, 
	  							  'export_decision'=> '1',
	  							  'lack_qty' => $order->lack_qty
	  						));
	  					$this->_orderInstance->updateOrderItemsLockQty($order->items, $oid);
	  				}
	  			}else if($pool_type == 'needImportOrders'){
	  				//lock qty 全都设为0.
	  				foreach($orders as $oid=>$order){
	  					/*
	  					if($order->status_handling < 1){
	  						$order->status_handling = 2;
	  						$order->status_locking = 0;
	  					}else if($order->lack_qty > 0){
	  						$order->status_handling = 2;
	  					}else{
	  						$order->status_handling = 1;
	  					}
	  					*/
	  					$this->_orderInstance->updateOrderConsoleFields($oid, 
	  						array('status_locking' => '0', 
	  							  //'status_handling'=> $order->status_handling,
	  							  'status_handling'=> '2',
	  							  'export_decision'=> '0',
	  							  'lack_qty' => '0'
	  						));
	  					$this->_orderInstance->updateOrderItemsLockQty($order->items, $oid, true);
	  				}
	  			}else if($pool_type == 'arrangedOrders'){
	  			  	foreach($orders as $oid=>$order){
	  			  		if($order->status_handling < 1){
	  						$order->status_handling = 2;
	  					}else if($order->lack_qty > 0){
	  						$order->status_handling = 2;
	  					}else{
	  						$order->status_handling = 1;
	  					}
	  					$this->_orderInstance->updateOrderConsoleFields($oid, 
	  						array('status_locking' => '1', 
	  							  'status_handling'=> $order->status_handling, 
	  							  'export_decision'=> '0',
	  							  'lack_qty' => $order->lack_qty
	  						));
	  					$this->_orderInstance->updateOrderItemsLockQty($order->items, $oid);
	  				}
	  			}else{
	  			  	//lock qty 全都设为0.
	  				foreach($orders as $oid=>$order){
	  					$this->_orderInstance->updateOrderConsoleFields($oid, 
	  						array('status_locking' => '0', 
	  							  'status_handling'=> '-1',
	  							  'export_decision'=> '0',
	  						      'lack_qty'=> '0'));
	  					$this->_orderInstance->updateOrderItemsLockQty($order->items, $oid, true);
	  				}
	  			}
	  		}
  		}
  		list($orders_pools, $stockLockedItems) = $this->computeIncompleteHandlingOrders();
 
	  	//数据保存两个月
	  	cache::save('order_memento', $orders_pools, 5184000);
	  	cache::save('order_memento_stocklocks', $stockLockedItems, 5184000);

	  	$this->view->assign('orders_pools', $orders_pools);
	  	$this->view->render('order/order_handling_pools.tpl');
  	}
  }
  
  public function addadminnoteAction(){
  	if($this->isPost()){
  		$post = $_POST;
  		$oid = $post['pk'];
  		$admin_note = $post['value'];
  		$orders_pools = cache::get('order_memento');
  		
  		if($orders_pools){
  			$orders_pools = $orders_pools->data;
  		}
  		if(isset($orders_pools[$post['name']][$oid])){
  			$orders_pools[$post['name']][$oid]->admin_note = $admin_note;
  			
  		} else {
  			foreach ($orders_pools as $name => $pool) {
  				if (key_exists($oid, $pool)) {
  					$orders_pools[$name][$oid]->admin_note = $admin_note;
  					break;
  				}
  			}
  		}
  		cache::save('order_memento', $orders_pools);
  		$this->_orderInstance->updateOrderConsoleFields($oid, array('admin_note'=>$admin_note));
  	}
  }
  
  public function addcommunicatetimeAction() {
      if ($this->isPost()) {
          $post = $_POST;
          $oid = $post['oid'];
          $communicate_time = time();
          $orders_pools = cache::get('order_memento');
          
          if($orders_pools) {
              $orders_pools = $orders_pools->data;
          }
  		  $order = $orders_pools[$post['pool_name']][$oid];
          $siteInstance = Site_Model::getInstance();
          $siteInfo = $siteInstance->getSite($order->sid);
          $stockItemList = array();
          foreach ($order->items as $index=>$orderItem) {
              if ($orderItem->current_qty > 0) {
                  if (!key_exists($orderItem->p_sn, $stockItemList)) {
                      $stockItemList[$orderItem->p_sn] = array();
                  }
                  $attrArray = array();
                  foreach($orderItem->data as $attr => $value) {
                  	if ($value == '2XL') {
                  		$value = 'XXL';
                  	}
                  	$attrArray[strtolower($attr)] = strtolower($value);
                  }
                  $data = json_encode($attrArray);
                  $stockItemList[$orderItem->p_sn][$data] = $orderItem->current_qty;
              }
          }
          
          $communicationInstance = Communication_Model::getInstance();
          $ret = $communicationInstance->communicateOrder($order->number, $stockItemList, $siteInfo);
  		  if ($ret == "success") {
  		      if(isset($orders_pools[$post['pool_name']][$oid])){
                $orders_pools[$post['pool_name']][$oid]->communicate_time = $communicate_time;
              }
              $this->sortArrangedOrders($orders_pools[$post['pool_name']]);
              cache::save('order_memento', $orders_pools);
              $this->_orderInstance->updateOrderConsoleFields($oid, array('communicate_time'=>$communicate_time));
              echo json_encode(array('communicate_time' => date('Y/m/d:H', $communicate_time)));
  		      return;
  		  }
          echo json_encode(array('error' => $ret));
      }
  }
  
  public function getselectoptionsAction($selector){
  	if($selector == 'status'){
  		$selection = $this->_orderInstance->getOrderStatuses(false);
  	}else if($selector == 'status_payment'){
  		$selection = $this->_orderInstance->getOrderPaymentStatuses(false);
  	}

  	else if($selector == 'status_shipping'){
  		$selection = $this->_orderInstance->getOrderShippingStatuses(false);
  	}
  	else if($selector == 'shipping_method'){
  		$selection = $this->_orderInstance->getOrderShippingMethods(false);
  	}else if($selector == 'payment_method'){
  		$selection = $this->_orderInstance->getOrderPaymentMethods(false);
  	}
  	$convertedSelection = array();
  	foreach($selection as $key => $v){
  		$convertedSelection[] = array('value'=>$key, 'text'=>$v);
  	}
  	echo json_encode($convertedSelection);
  }

  public function updateorderAction(){
  	if($this->isPost()){
  		$post = $_POST;
  		$set = array($post['name']=>$post['value'], 'updated'=>TIMESTAMP);
  		//finish this order when it has been shipped.
  		if($post['name'] == 'status_shipping'){
  			$set['finished'] = TIMESTAMP;
  		}
  		//firstly, get the order.
  		$old_order = $this->_orderInstance->getOrderById($post['pk']);
  		//$old_order_status = $old_order->status;
  		$old_status_shipping = $old_order->status_shipping;
  		
  		$this->_orderInstance->updateOrderById($post['pk'], $set);
  		
  		//update remote order(also send mail).
  		$order = $this->_orderInstance->getOrderById($post['pk']);
  		$siteInstance = Site_Model::getInstance();
  		$siteInfo = $siteInstance->getSite($order->sid);
  		$communicationInstance = Communication_Model::getInstance();
  		$communicationInstance->updateOrder($order->number, array($post['name']=>$post['value'], 'delivery_email'=>$order->delivery_email), $siteInfo);
  	}
  }

  //$post['name'] = supplier_name, $post['value'] = shippingno;
  public function editshippingnoAction(){
  	if($this->isPost()){
  		$post = $_POST;
  		//update the console order.
  		$orderInfo = $this->_orderInstance->getOrderById($post['pk']);
  		$shippingInfo = $post['value'];
  		if (empty($shippingInfo)) {
  		    echo json_encode(array('success' => false, 'msg' => '请填写运单信息'));
  		    exit -1;
  		}
  		$shippingInfo = explode(',', $shippingInfo);
  		if (count($shippingInfo) < 2) {
  		    echo json_encode(array('success' => false, 'msg' => '运单信息格式错误(注意运输方式和运单号之间用英文逗号分开)'));
  		    exit -1;
  		}
  		$ret = $this->editshippingno($orderInfo, $shippingInfo[1], $shippingInfo[0]);
  		if ($ret['success'] === false) {
  		    echo json_encode(array('success' => false, 'msg' => '运单号上传失败'));
  		    exit -1;
  		}
  	}
  }

  private function editshippingno($orderInfo, $shippingNo, $shippingMethod) {
      $ret = array();
      $data = unserialize($orderInfo->data);
      if(!is_array($data)){
          $data = array();
      }
      $data['shipping_no'] = $shippingMethod . ',' . $shippingNo; //终端订单保存实际运输方式和运单号
      $this->_orderInstance->updateOrderById($orderInfo->oid, array('data'=>serialize($data), 'updated'=>TIMESTAMP));
      $data['shipping_no'] = $shippingNo;
      $orderInfo = $this->_orderInstance->getOrderById($orderInfo->oid);
      
      //send mail to client(and also update the shipping status to the order item).
      require_once LIBPATH . '/globebill.php';
      $globebill = new Globebill();
      $error = '';
      if ($orderInfo->payment_method == "creditcard" && !$globebill->uploadTrackingNumber($orderInfo->number, $shippingNo, $shippingMethod, $error)) {
          $ret['success'] = false;
          $ret['msg'] = "上传trackingNumber到钱宝失败,原因:$error";
      } else {
          $remote_data = array('delivery_email'=>$orderInfo->delivery_email);
          if(isset($data)){
              $remote_data['data'] = serialize($data);
          }else{
              $remote_data[shipping_no] = $shippingNo;
          }
          $remote_data['username'] = $orderInfo->delivery_first_name . ' ' . $orderInfo->delivery_last_name;
          $remote_data['itemno'] = $orderInfo->number;
          $remote_data['actual_shipping_method'] = $shippingMethod;
          $siteInstance = Site_Model::getInstance();
          $siteInfo = $siteInstance->getSite($orderInfo->sid);
          $communicationInstance = Communication_Model::getInstance();
          if ($communicationInstance->updateOrder($orderInfo->number, $remote_data, $siteInfo)) {
              $ret['success'] = true;
              $ret['oid'] = $orderInfo->oid;
          } else {
              $ret['success'] = false;
              $ret['msg'] = '上传运单号到' . $siteInfo->name . '失败!';
          }
      }
      return $ret;
  }
  
  public function addactualshippingfeeAction() {
  	if ($this->isPost()) {
  		$post = $_POST;
  		$oid = $post['pk'];
  		$actualShippingFee = $post['value'];
  		$orderInfo = $this->_orderInstance->getOrderById($oid);
  		$ret = $this->addactualshippingfee($orderInfo, $actualShippingFee);
  		echo json_encode($ret);
  	}
  }
  
  private function addactualshippingfee($orderInfo, $actualShippingFee) {
      $ret = array();
      if (empty($orderInfo)) {
          $ret['success'] = false;
          $ret['msg'] = '无效的订单号';
      } else {
          if ($this->_orderInstance->updateOrderById($orderInfo->oid, array('actual_shipping_fee' => $actualShippingFee, 'updated'=>TIMESTAMP))) {
              $ret['success'] = true;
          } else {
              $ret['success'] = false;
              $ret['msg'] = '添加运费失败';
          }
      }
      return $ret;
  }
  
  public function uploadordershippingnoAction() {
      if ($this->isPost()) {
          $orderNumber = $_POST['orderNumber'];
          if (empty($orderNumber)) {
              echo json_encode(array('success' => false, 'msg' => '订单号为空'));
              exit -1;
          }
          $shippingFee = $_POST['shippingFee'];
          if (empty($shippingFee) || intval($shippingFee) <= 0) {
              echo json_encode(array('success' => false, 'msg' => '运费不能为空或者小于等于0'));
              exit -1;
          }
          $shippingNo = $_POST['shippingNo'];
          if (empty($orderNumber)) {
              echo json_encode(array('success' => false, 'msg' => '运单号不能为空'));
              exit -1;
          }
          $shippingMethod = $_POST['shippingMethod'];
          if (empty($shippingMethod)) {
              echo json_encode(array('success' => false, 'msg' => '运输方式不能为空'));
              exit -1;
          }
          $orderInfo = Order_Model::getInstance()->getOrderByNumber($orderNumber);
          if (empty($orderInfo)) {
              echo json_encode(array('success' => false, 'msg' => '订单不存在'));
              exit -1;
          }
          $ret = $this->addactualshippingfee($orderInfo, $shippingFee);
          if (!$ret['success']) {
              echo json_encode($ret);
              exit -1;
          }
          $ret = $this->editshippingno($orderInfo, $shippingNo, $shippingMethod);
          echo json_encode($ret);
      } else {
          $this->view->render('order/upload_order_shipping_no.tpl',
                  array('pageLable' => 'uploadordershippingno'));
      }
  }
  
  public function ajaxupdateownstockAction(){
  	global $user;
      if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
	  	$post = $_POST;
	  	$oid = $post['oid'];
	  	$order = $this->_orderInstance->getOrderById($oid);

	  	$set = array('status'=>2, 'status_shipping'=>1, 'outinventory'=>1, 'finished'=>TIMESTAMP, 'updated'=>TIMESTAMP);
	  	
	  	$orders_pools = cache::get('order_memento');
	  	if($orders_pools){
	  		$orders_pools = $orders_pools->data;
		  	$stockLockedItems = cache::get('order_memento_stocklocks')->data;
		  	//do something.
		  	$targetOrder = $orders_pools['readyExportOrders'][$oid];
		  	
		  	$stockInstance = Stock_Model::getInstance();
		  	//update current_qty added by chenzhigao
		  	//比较缓存和数据库的值是否一致
		  	$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($oid);
		  	foreach($orderItems as $orderItem) {
		  	    foreach($targetOrder->items as $cacheOrderItem) {
		  	        if ($orderItem->oiid == $cacheOrderItem->oiid) {
		  	            if ($orderItem->current_qty != $cacheOrderItem->current_qty) {
		  	                log::save('DEBUG', 'current_qty mismatch', array('oiid' => $orderItem->oiid, 'current_qty' => $orderItem->current_qty, 'cache_qty' => $cacheOrderItem->current_qty));
		  	                break;
		  	            }
		  	        }
		  	    }
		  	}
		  	//$this->_orderInstance->updateOrderItemsLockQty($targetOrder->items, $oid); 为了库存的准确性，去掉
		  	
		  	//update stockLockState.
		  	foreach($targetOrder->items as $orderItem){
	  			$stockLockedItems[$orderItem->p_sn][$orderItem->avid] = $stockLockedItems[$orderItem->p_sn][$orderItem->avid] - $orderItem->current_qty;
	  			
	  			//deduct stock qty.
	  	    	$stockItem = $stockInstance->getStockItem($orderItem->p_sn, $orderItem->avid);
	  	    	log::save('DEBUG', 'orderItem', array(
	  	    	   'sn' => $orderItem->p_sn, 
	  	    	   'current_qty' => $orderItem->current_qty,
	  	    	   'stock_qty' => $stockItem->stock_qty,
	  	    	   'avid' => $orderItem->avid,
	  	    	   'oid' => $oid
	  	    	));
		  		if($stockItem){
		  			$stockQty = $stockItem->stock_qty - $orderItem->current_qty;
		  			try {
		  			    if(!$stockInstance->updateStock($orderItem->p_sn, $orderItem->avid, array('stock_qty'=>strval($stockQty)))) {
		  			        log::save('DEBUG', 'update_stock_error', $stockQty);
		  			    }
		  			    $afterOutStock = $stockInstance->getStockItem($orderItem->p_sn, $orderItem->avid);
		  			    log::save('DEBUG', 'after_out_stock_qty', $afterOutStock->stock_qty);
		  			} catch(Exception $exception) {
		  			    log::save('DEBUG', 'out_stock_trace', $exception->getTraceAsString());
		  			    log::save('DEBUG', 'out_stock_message', $exception->getMessage());
		  			}
		  		} else {
		  		    log::save('DEBUG', 'stockItemFalse');
		  		}
		  	}
		  	unset($orders_pools['readyExportOrders'][$oid]);
		  	cache::save('order_memento', $orders_pools, 5184000);
		  	cache::save('order_memento_stocklocks', $stockLockedItems, 5184000);
	  	}
	  	
	  	$this->_orderInstance->updateOrderById($oid, $set);
	  	
	  	$siteInstance = Site_Model::getInstance();
	  	$siteInfo = $siteInstance->getSite($order->sid);
	  	$communicationInstance = Communication_Model::getInstance();
	  	$returnCode = $communicationInstance->updateOrder($order->number, array('status'=>2, 'status_shipping'=>1, 'updated'=>TIMESTAMP, 'delivery_email'=>$order->delivery_email), $siteInfo);
	  	echo json_encode(array('status'=>$returnCode, 'oid'=>$oid));
  	}
  }
  
  public function ajaxupdateamazonstockAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	
  	if($this->isPost()){
	  	//$amazon_orders = $this->_orderInstance->getAmazonOrders();
	  	$post = $_POST;
	  	$oid = $post['oid'];
	  	$amazon_order = $this->_orderInstance->getOrderById($oid);
	  	//foreach($amazon_orders as $oid =>$amazon_order){
	  		//generate order.
	  	$set = array('status'=>2, 'status_shipping'=>1, 'outinventory'=>1, 'finished'=>TIMESTAMP, 'updated'=>TIMESTAMP);
	  	$this->_orderInstance->updateOrderById($oid, $set);
	  		//update remote order(also send mail).
	  		//$order = $this->_orderInstance->getOrderById($oid);
	  	$siteInstance = Site_Model::getInstance();
	  	$siteInfo = $siteInstance->getSite($amazon_order->sid);
	  	$communicationInstance = Communication_Model::getInstance();
	  	$returnCode = $communicationInstance->updateOrder($amazon_order->number, array('status'=>2, 'status_shipping'=>1, 'updated'=>TIMESTAMP, 'delivery_email'=>$amazon_order->delivery_email), $siteInfo);
	  	echo json_encode(array('status'=>$returnCode, 'oid'=>$oid));
  	}
  	//}
  }
  
  
  /**
   * MerchantFulfillmentOrderID	
   * DisplayableOrderID	
   * DisplayableOrderDate	
   * MerchantSKU	
   * Quantity	
   * MerchantFulfillmentOrderItemID	
   * GiftMessage	
   * DisplayableComment	
   * PerUnitDeclaredValue	
   * DisplayableOrderComment	
   * DeliverySLA	
   * AddressName	
   * AddressFieldOne	
   * AddressFieldTwo	
   * AddressFieldThree	
   * AddressCity	
   * AddressCountryCode	
   * AddressStateOrRegion	
   * AddressPostalCode	
   * AddressPhoneNumber	
   * NotificationEmail
   * 
   */
  public function ajaxgenerateamazonorderAction(){
    global $user;
    if(!$user->uid){
  		gotoUrl('');
  	}
  	$amazon_orders = $this->_orderInstance->getAmazonOrders();
  	$amazonStockInstance = Amazonstock_Model::getInstance();
  	$siteInstance = Site_Model::getInstance();
  	$areaInstance = Area_Model::getInstance();
  	$orderRecords = array();
  	$orderRecords[] = array('MerchantFulfillmentOrderID','DisplayableOrderID','DisplayableOrderDate','MerchantSKU','Quantity',
  							'MerchantFulfillmentOrderItemID','GiftMessage','DisplayableComment','PerUnitDeclaredValue',
  							'DisplayableOrderComment','DeliverySLA','AddressName','AddressFieldOne','AddressFieldTwo','AddressFieldThree',
  							'AddressCity','AddressCountryCode','AddressStateOrRegion','AddressPostalCode','AddressPhoneNumber','NotificationEmail');
  	foreach ($amazon_orders as $oid=>$amazon_order){
  		foreach($amazon_order->items as $orderItem){
	  		$orderRecord = array();
	  		$orderRecord[] = $amazon_order->number;
	  		$orderRecord[] = $amazon_order->number;
	  		$orderRecord[] = date('Y-m-d\Th:i:s', TIMESTAMP);
	  		$amazon_sku = $amazonStockInstance->composeSKU($orderItem->p_sn, $orderItem->data);
	  		$orderRecord[] = $amazon_sku;
	  		$orderRecord[] = $orderItem->qty;
	  		$orderRecord[] = $amazon_sku . '-'.strval($orderItem->oiid);
	  		$orderRecord[] = '';
	  		$orderRecord[] = '';
	  		$orderRecord[] = '';
	  		$siteInfo = $siteInstance->getSite($orderItem->sid);
	  		if($siteInfo == false){
	  			$siteInfo = new stdClass();
	  			$siteInfo->name = 'Lingeriemore.com';
	  		}
	  		$orderRecord[] = 'Thank you for ordering from '.$siteInfo->name .'!';
	  		$orderRecord[] = 'Standard';
	  		$orderRecord[] = $amazon_order->delivery_first_name . ' '.$amazon_order->delivery_last_name;
	  		$orderRecord[] = $amazon_order->delivery_address;
	  		$orderRecord[] = '';
	  		$orderRecord[] = '';
	  		$orderRecord[] = $amazon_order->delivery_city;
	  		$orderRecord[] = $areaInstance->getAreaCode($amazon_order->delivery_country);
	  		$orderRecord[] = $areaInstance->getAreaCode($amazon_order->delivery_province);
	  		$orderRecord[] = $amazon_order->delivery_postcode;
	  		$orderRecord[] = $amazon_order->delivery_mobile;
	  		$orderRecord[] = 'heliangdong@mingdabeta.com';
	  		
	  		$orderRecords[] = $orderRecord;
  		}
  	}
  	//generate csv file.
    $filename = 'amazon_order-'.strval(TIMESTAMP).'.txt';
    download_send_headers($filename);
    $outputBuffer = fopen("php://output", 'w');
    foreach($orderRecords as $orderRecord) {
    	fwrite($outputBuffer, implode("\t", $orderRecord) ."\r\n");
    }
    fclose($outputBuffer);
  }
  
  public function ajaxgetorderaddressAction($oid){
  	$orderInfo = $this->_orderInstance->getOrderById($oid);
  	$this->view->assign('order', $orderInfo);
  	$this->view->render('order/order_address.tpl');
  }
  
  public function ajaxgetorderitemsAction($oid){
      $orders_pools = cache::get('order_memento');
      $order = null;
	  if($orders_pools){
	    $orders_pools = $orders_pools->data;
	    foreach($orders_pools as $key => $order_list) {
	        if (key_exists($oid, $order_list)) {
	            $order = $order_list[$oid];
	            break;
	        }
	    }
	}
	$stockItemList = array();
	if (!isset($order)) {
	    $order = $this->_orderInstance->getOrderById($oid);
	    $order->items = $this->_orderInstance->getOrderItemsDetailByOid($oid);
	}
    if (isset($order)) {
        if (!isset($order->refundAmount) && $order->lack_qty > 0) {
        	$refundAmount = new stdClass();
        	foreach ($order->items as $index=>$orderItem) {
                if ($orderItem->current_qty < $orderItem->qty) {
                    $refundAmount->product += ($orderItem->qty - $orderItem->current_qty) * ($orderItem->total_amount / $orderItem->qty);
                }
            }
            if (isset($refundAmount->product)) {
                $order->refundAmount = $refundAmount;
                cache::save('order_memento', $orders_pools);
            }
        }
    }
            
    if (isset($order)) {
	   $this->view->assign('order', $order);
       $this->view->render('order/order_handling_subitem.tpl');
    }
  }
  protected function computeIncompleteHandlingOrders(){
  	  	//get all orders that still incomplete.
	  	$incompleteOrders = $this->_orderInstance->getIncompleteHandlingOrders();
	  	//divide incomplete orders into 4 divisions and amazon orders.
	  	
	  	$amazonOrders = array();
	  	$ommitedOrders = array();
	  	$arrangedOrders = array();
	  	//$stockLockedOrders = array();
	  	$needComputingOrders = array();
	  	$readyOrders = array();
	  	$stockLockedItems = array();
	
	  	foreach ($incompleteOrders as $k=>$v){
	  		$v->lack_qty = 0;
	  		if($v->status_handling == '3'){
	  			//amazon stock.
	  			$amazonOrders[$k] = $v;
	  			$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($v->oid);
	  			if($orderItems == false) $orderItems = array();
	  			$v->items = $orderItems;
	  		}else if($v->status_handling == '-1'){
	  			//回收站
	  			$ommitedOrders[$k] = $v;
	  			$orderItems = $this->_orderInstance->getOrderItemsWithStockInfo($v->oid);
	  			if($orderItems == false) $orderItems = array();
	  			$v->items = $orderItems;
	  		}else if($v->status_handling == '2'){
	  			//缺货
	  			if($v->status_locking == '1'){
	  				//已锁定（已安排）
		  			//this order was locked. Need except from stock.
		  			//get order items.
    	  			$orderItems = $this->_orderInstance->getOrderItemsWithStockInfo($v->oid);
    		  		$v->items = $orderItems;
    		  		foreach($orderItems as $k2=>$v2){
    		  			if(key_exists($v2->p_sn, $stockLockedItems)){
    		  				if(key_exists($v2->avid, $stockLockedItems[$v2->p_sn])){
    		  					$curStockItemQty = $stockLockedItems[$v2->p_sn][$v2->avid];
    		  					if($v2->stock_qty < $curStockItemQty){
    		  						//recount the stock qty.
    		  						$v2->current_qty = 0;
    		  						//$make the stock locked items as the stock qty.
    		  						$stockLockedItems[$v2->p_sn][$v2->avid] = $v2->stock_qty;
    		  					}else if($v2->stock_qty < $curStockItemQty + $v2->current_qty){
    		  						$v2->current_qty = $v2->stock_qty - $curStockItemQty;
    		  						$stockLockedItems[$v2->p_sn][$v2->avid] = $v2->stock_qty;
    		  					}else{
    		  						//库存充足
    		  						$stockLockedItems[$v2->p_sn][$v2->avid] += intval($v2->current_qty);
    		  					}
    		  				}else{
    		  					if($v2->stock_qty < $v2->current_qty){
    		  						$v2->current_qty = $v2->stock_qty;
    		  					}
    		  					$stockLockedItems[$v2->p_sn][$v2->avid] = intval($v2->current_qty);
    		  				}
    		  			}else{
    		  				if($v2->stock_qty < $v2->current_qty){
    		  						$v2->current_qty = $v2->stock_qty;
    		  				}
    		  				$stockLockedItems[$v2->p_sn] = array($v2->avid=>intval($v2->current_qty));
    		  			}
    		  			$v->lack_qty += intval($v2->qty - $v2->current_qty);
    		  		}
	  			  	if($v->export_decision == '0'){
		  				$arrangedOrders[$k] = $v;
	  				}else{
	  					$readyOrders[$k] = $v;
	  				}
	  			}else{
	  				//未锁定，等待安排。
	  				$needComputingOrders[$k] = $v;
	  			}
	  		}else if($v->status_handling == '1'){
	  			//不缺货
	  			//if($v->status_locking == '1'){
	  				//this item was locked. Need except from stock.
		  			$orderItems = $this->_orderInstance->getOrderItemsWithStockInfo($v->oid);
		  			if($orderItems == false) $orderItems = array();
		  			$v->items = $orderItems;
		  			foreach($orderItems as $k2=>$v2){
		  				if(key_exists($v2->p_sn, $stockLockedItems)){

		  					if(key_exists($v2->avid, $stockLockedItems[$v2->p_sn])){
		  						$curStockItemQty = $stockLockedItems[$v2->p_sn][$v2->avid];
		  						if($v2->stock_qty < $curStockItemQty){
		  							//recount the stock qty.
		  							$v2->current_qty = 0;
		  							//$make the stock locked items as the stock qty.
		  							$stockLockedItems[$v2->p_sn][$v2->avid] = $v2->stock_qty;
		  						}else if($v2->stock_qty < $curStockItemQty + $v2->current_qty){
		  							$v2->current_qty = $v2->stock_qty - $curStockItemQty;
		  							$stockLockedItems[$v2->p_sn][$v2->avid] = $v2->stock_qty;
		  						}else{
		  							//库存充足
		  							$stockLockedItems[$v2->p_sn][$v2->avid] += intval($v2->current_qty);
		  						}
		  					}else{
		  						if($v2->stock_qty < $v2->current_qty){
		  							$v2->current_qty = $v2->stock_qty;
		  						}
		  						$stockLockedItems[$v2->p_sn][$v2->avid] = intval($v2->current_qty);
		  					}
		  				}else{
		  					if($v2->stock_qty < $v2->current_qty){
		  							$v2->current_qty = $v2->stock_qty;
		  					}
		  					$stockLockedItems[$v2->p_sn] = array($v2->avid=>intval($v2->current_qty));
		  				}
		  				$v->lack_qty += intval($v2->qty - $v2->current_qty);
		  			}
		  			$readyOrders[$k] = $v;
	  		//}else{
	  				//$needComputingOrders[$k] = $v;
	  			//}
	  		}else{
	  			$needComputingOrders [$k] = $v;
	  		}
	  		//now we already get the 3 arrays
	  	}
	  	
	  	$this->_orderInstance->updateArrangedOrderItemsLackState($arrangedOrders, $readyOrders, $stockLockedItems);
	  	
	  	//now we need compute amazon store.
	  	//$this->computeAmazonOrders($amazonOrders, $needComputingOrders);
	  	
	  	//$this->view->assign('amazonOrders', $amazonOrders);
	  	
	  	$orders_pools = $this->_orderInstance->calculateOrderItemsLackState($needComputingOrders, $stockLockedItems);
	  	$this->sortArrangedOrders($arrangedOrders);
	  	$orders_pools['arrangedOrders'] = $arrangedOrders;
	  	$orders_pools['ommitedOrders'] = $ommitedOrders;
	  	
	  	foreach ($readyOrders as $oid=>$order){
	  		$orders_pools['readyExportOrders'][$oid] = $order;
	  	}
	  	
	  	return array($orders_pools, $stockLockedItems);
  }
  
  public function sortArrangedOrders(&$arrangedOrders) {
      $communicated_orders = array();
      $not_communicated_orders = array();
      foreach ($arrangedOrders as $order) {
          if ($order->communicate_time > 0) {
              $communicated_orders[$order->oid] = $order;
          } else {
              $not_communicated_orders[$order->oid] = $order;
          }
      }
      usort($communicated_orders, array($this, "orderComp"));
      usort($not_communicated_orders, array($this, "orderComp"));
      $order_list = array();
      foreach ($not_communicated_orders as $order) {
          $order_list[$order->oid] = $order;
      }
      foreach ($communicated_orders as $order) {
          $order_list[$order->oid] = $order;
      }
      $arrangedOrders = $order_list;
  }
  /**
   * 订单排序(从小到大序)
   */
  private function orderComp($lhs, $rhs) {
      if ($lhs->created == $rhs->created) {
          return 0;
      } else {
          return ($lhs->created > $rhs->created) ? 1 : -1;
      }
   }
  
  private function clearSessionOrderFilterData(){
  	unset($_SESSION['order_filters']);
  	unset($_SESSION['owb_id']);
  	unset($_SESSION['excluded_orders']);
  }
  
  private function getOrderFilters(){
    if(isset($_SESSION['order_filters'])){
  		$filters = $_SESSION['order_filters'];
  	}else{
  		//use default settings.
  		$filters = array('created >='=>strtotime(date('m/d/Y', time())), 
  						'created <='=>strtotime(date('m/d/Y', time())) + (3600*24 - 1), 
  						'status'=>'<>2', 
  						'status_payment'=>'1', 
  						'payment_method'=>'default',
  						'status_shipping'=>'0', 
  						'shipping_method'=>'default',
  						'outinventory'=>'0');
  	}
  	return $filters;
  }
  
  private function clearSessionShippedOrderByDateFilterData(){
  	unset($_SESSION['shipped_order_filters']);
  }
  
  private function getOrderShippingDateFilter(){
    if(isset($_SESSION['shipped_order_filters'])){
  		$filters = $_SESSION['shipped_order_filters'];
  	}else{
  		//use default settings.
  		$filters = array();
  		$filters['updated'] = strtotime(date('Y-m-d', time()));
	  	$filters['status'] = 2;
  		$filters['status_payment'] = 1;
	  	$filters['status_shipping'] = 1;
	  	$filters['outinventory'] = 'default';
	  	$filters['shipping_method'] = 'default';
  	}
  	return $filters;
  }
  
  private function getOrdersByOrderShippingDateFilter(){
  	$sid = session_id();
  	$filters = $this->getOrderShippingDateFilter();
    $orders = $this->_orderInstance->getOrdersByCompleteDate($filters['updated'], $filters['outinventory'], $filters['shipping_method']);
    foreach($orders as $index=>$order){
  		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
    	if(!$orderItems){$orderItems = array();}
  		$order->items = $orderItems;
  	}
  	return $orders;
  }
  
  private function getOrdersBySessionFilter(){

    $sid = session_id();
    
    if(isset($_SESSION['owb_id'])){
    	$owb_id = $_SESSION['owb_id'];
    	//try for the result.
    	$data = $this->_orderInstance->getRecordFromOrderWasteBookById($owb_id);
    	if($data){
    		//get oids from data.
    		$oids = explode(',', $data->orders);
    		
    		//remove the excluded orders.
    		$excluded_oids = explode(',', $data->excluded_orders);
    		foreach($excluded_oids as $excludedIndex=>$excluded_oid){
    			if(isset($oids[$excluded_oid])){
    				unset($oids[$excluded_oid]);
    			}
    		}
    		
    	  	$orders= $this->_orderInstance->getOrdersByOids($oids);
		    foreach($orders as $index=>$order){
		  		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
		    	if(!$orderItems){$orderItems = array();}
		  		$order->items = $orderItems;
		  	}
		  	return $orders;
    	}
    }
    //need to get the order sets again.
    //insert into wastebook for this filter operation.
  	$filters = $this->getOrderFilters();
    $orders = $this->_orderInstance->getOrdersLike('shipping_method DESC, created DESC', null, $filters, null);

    if(isset($_SESSION['excluded_orders'])){
    	//added into excluded_orders fields.
    	$excluded_oids = $_SESSION['excluded_orders'];
    }else{
    	$excluded_oids = array();
    }
    
    //remove those already been excluded.
    foreach($excluded_oids as $excludedIndex=>$excluded_oid){
    	if(isset($orders[$excluded_oid])){
    		unset($orders[$excluded_oid]);
    	}
    }
    
    $oidArray = array();
    foreach($orders as $key=>$order){
    	$oidArray[] = $order->oid;
    }
    $oids = implode(',', $oidArray);
    
    $owb_id = $this->_orderInstance->insertIntoOrderWasteBook($filters, $oids, $excluded_oids);
    
    $_SESSION['owb_id'] = $owb_id;
    
    //add order items to order.
    foreach($orders as $index=>$order){
  		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($order->oid);
    	if(!$orderItems){$orderItems = array();}
  		$order->items = $orderItems;
  	}
  	return $orders;
  }
  
  
  
  //array(p_sn=>stdClass(imageSource->imageSource, requirements->array(avid=>stdClass(lack_qty->lack_qty, data->data))))
  //array{p_sn=>array('avid'=>stdClass(lack_qty=>lack_qty, imageSource->imageSource))};
  private function getProductSummaryByOrders($orders){
  	$result = array();

  	foreach ($orders as $k=>$order){
  		foreach($order->items as $key=>$item){
  			if(!key_exists($item->p_sn, $result)){
  				$result[$item->p_sn] =new stdClass();
  				$result[$item->p_sn]->imageSource = (isset($item->imageSource))? $item->imageSource : $item->image_source;
  				$result[$item->p_sn]->requirements = array();
  			}
  			if(!key_exists($item->avid, $result[$item->p_sn]->requirements)){
  				$result[$item->p_sn]->requirements[$item->avid] = new stdClass();
  				$result[$item->p_sn]->requirements[$item->avid]->lack_qty = $item->qty - $item->current_qty;
  				$result[$item->p_sn]->requirements[$item->avid]->real_qty = $item->current_qty;
  				$result[$item->p_sn]->requirements[$item->avid]->data = $item->data;
  			}else{
  				$result[$item->p_sn]->requirements[$item->avid]->lack_qty += $item->qty - $item->current_qty;
  				$result[$item->p_sn]->requirements[$item->avid]->real_qty += $item->current_qty;
  			}
  		}
  	}
  	return $result;
  }
  
  private function updateStock($pOrderSums){
    $stockInstance = Stock_Model::getInstance();
  	foreach($pOrderSums as $p_sn=>$pOrderSum){
  		
  		foreach($pOrderSum->requirements as $avid=>$qtys){
  			$stockItem = $stockInstance->getStockItem($p_sn, $avid);
  			if($stockItem){
  				$stockQty = $stockItem->stock_qty - $qtys->real_qty;
  				$stockInstance->updateStock($p_sn, $avid, array('stock_qty'=>strval($stockQty)));
  			}else{
  				//no such a stock item, create one.
  				$stockInstance->insertStock($p_sn, $qtys->data, 0, $bought_price=null, $sell_price_delta=null);
  			}
  		}
  	}

  }
  
  
  private function getStyleCountByProductOrderSummary($pOrderSummary){
  	$count = 0;
  	foreach($pOrderSummary->requirements as $k=>$v){
  		if($v->lack_qty > 0){
  			$count++;	
  		}
  	}
  	return $count;
  }
  
  
  public function updatedbAction($page = 1){
    global $db;
  	$result = $db->query('select oiid, data from orders_items limit '.strval(intval($page - 1) * 5000).' , 5000');
  	$productInstance = Product_Model::getInstance();
  	$data = $result->all();
  	foreach ($data as $v){
  		$firstData = unserialize($v->data);
  		$result2 = $db->query('select * from attr_values');
  		$data2 = $result2->all();
  		$existed = false;
  		foreach($data2 as $v2){
  			$secondData = unserialize($v2->data);

  			if(is_array($secondData) && is_array($firstData) && $productInstance->isPropertiesEqual($secondData, $firstData)){
  				//已经有了
  				$existed = true;
  				$db->update('orders_items', array('avid'=>$v2->id), array('oiid'=>$v->oiid));
  				break;
  			}
  		}
  		//如果没有
  		if(!$existed && is_array($firstData)){
  			//插进去
  			$db->insert('attr_values', array('data'=>$v->data));
  			$avid = $db->lastInsertId();
  			
  			$db->update('orders_items', array('avid'=>$avid), array('oiid'=>$v->oiid));
  		}
  	}

  	//for stock table.
    $result = $db->query('select stock_id, parameters from stock');
  	$productInstance = Product_Model::getInstance();
  	$data = $result->all();
  	foreach ($data as $v){
  		$firstData = unserialize($v->parameters);
  		$result2 = $db->query('select * from attr_values');
  		$data2 = $result2->all();
  		$existed = false;
  		foreach($data2 as $v2){
  			$secondData = unserialize($v2->data);
  			if(is_array($secondData) && is_array($firstData) && $productInstance->isPropertiesEqual($secondData, $firstData)){
  				//已经有了
  				$existed = true;
  				$db->update('stock', array('avid'=>$v2->id), array('stock_id'=>$v->stock_id));
  				break;
  			}
  		}
  		//如果没有
  		if(!$existed && is_array($firstData)){
  			//插进去
  			$db->insert('attr_values', array('data'=>$v->parameters));
  			$avid = $db->lastInsertId();
  			$db->update('stock', array('avid'=>$avid), array('stock_id'=>$v->stock_id));
  		}
  	}
  }
  
  private function computeAmazonOrders(&$amazonOrders, &$needComputeOrders){
	$amazonInstance = Amazonstock_Model::getInstance();
	
	$amazonStock = array();
	
	foreach ($needComputeOrders as $oid=>$order){
		//search amazon stock table. Currently only support united states.
		if($order->delivery_country != 'United States'){
			continue;
		}
		$orderItems = $this->_orderInstance->getOrderItemsDetailByOid($oid);
		$amazon_can_fill = true;
		foreach($orderItems as $orderItem){
			if(!key_exists($orderItem->p_sn, $amazonStock)){
				$amazonStock[$orderItem->p_sn] = array();
			}
			if(!key_exists($orderItem->avid, $amazonStock[$orderItem->p_sn])){
				$amazonStock[$orderItem->p_sn][$orderItem->avid] = new stdClass();
				$amazonStock[$orderItem->p_sn][$orderItem->avid]->qty = $amazonInstance->getStockQty($orderItem->p_sn, $orderItem->avid);
				$amazonStock[$orderItem->p_sn][$orderItem->avid]->synced = true;
			}

			if($amazonStock[$orderItem->p_sn][$orderItem->avid]->qty < $orderItem->qty){
				$amazon_can_fill = false;
				break;
			}
		}
		if($amazon_can_fill){
			//foreach order that can make amazon fill.
			$order->status_handling = '3';
			$this->_orderInstance->updateOrderConsoleFields($oid, array('status_handling'=>'3'));
			
			$amazonOrders[$oid] = $order;
			$order->items = $orderItems;
			foreach ($orderItems as $orderItem){
				$orderItem->current_qty = $orderItem->qty;
				$amazonStock[$orderItem->p_sn][$orderItem->avid]->qty = $amazonStock[$orderItem->p_sn][$orderItem->avid]->qty - $orderItem->qty;
				if($amazonStock[$orderItem->p_sn][$orderItem->avid]->synced){
					$amazonStock[$orderItem->p_sn][$orderItem->avid]->synced = false;
				}
			}
			$this->_orderInstance->updateOrderItemsLockQty($orderItems, $oid);
			
			unset($needComputeOrders[$oid]);
		}
	}
  	//store updated amazon stock.
	
	foreach ($amazonStock as $p_sn=>$variaties){
		foreach($variaties as $avid=>$details){
			if(!$details->synced){
				//need update db stock_qty;
				$amazonInstance->updateStock($p_sn, $avid, $details->qty);
			}
		}
	}
	
  }
  
  public function updateorderaddressAction() {
      global $user;
      $ret = array();
       
      if (!$user->uid) {
          $ret['success'] = false;
          $ret['message'] = 'Access Denied.';
          echo json_encode($ret);
          exit -1;
      }
      $oid = $_POST['oid'];
      if (empty($oid)) {
          $ret['success'] = false;
          $ret['message'] = 'order id is empty.';
          echo json_encode($ret);
          exit -1;
      }
      $propList = $_POST['prop-list'];
      $orderInstance = Order_Model::getInstance();
      $orderInfo = $orderInstance->getOrderById($oid);
      if (empty($orderInfo)) {
          $ret['success'] = false;
          $ret['message'] = 'order not exists.';
          echo json_encode($ret);
          exit -1;
      }
      $siteInstance = Site_Model::getInstance();
      $siteInfo = $siteInstance->getSite($orderInfo->sid);
      $communicationInstance = Communication_Model::getInstance();
       
      $addressChanged = false;
      $propVars = get_object_vars((object)$propList);
      foreach($propVars as $key => $value) {
          if ($orderInfo->{$key} != $value) {
              $addressChanged = true;
              break;
          }
      }
      if ($addressChanged) {
          if ($communicationInstance->updateOrderAddress($orderInfo->number, $propList, $siteInfo)) {
              $propList['updated'] = TIMESTAMP;
              if ($orderInstance->updateOrderById($oid, $propList)) {
                  $ret['success'] = true;
              } else {
                  $ret['success'] = false;
                  $ret['message'] = "faild to update address.";
              }
          } else {
              $ret['success'] = false;
              $ret['message'] = "faild to update address on remote site.";
          }
      } else {
          $ret['success'] = true;
      }
      echo json_encode($ret);
  }
}