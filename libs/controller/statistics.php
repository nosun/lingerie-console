<?php
class Statistics_Controller extends MD_Controller  {


  private $_statisticInstance;
  
  public function init()
  {
    $this->_statisticInstance = Statistics_Model::getInstance();
    $this->view->assign('pageLabel', 'statistics');
  }
  public function showAction(){
  	
  	$this->productAction();
  	
  	/*
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$this->view->assign('statistics_title', '综合统计');
  	$this->view->assign('templatefile', 'summary.tpl');
    $this->view->render('statistics.tpl');
    */
  }
  

  public function productAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	
  	$filters = array();

  	if($this->isPost()){
  		$post = $_POST;
  		unset($_SESSION['statistics_filters']);
  		if(!isset($post['clear_filters'])){
  			//加一个新的过滤条件。
  			if(isset($post['sn_filter']) && $post['sn_filter'] != '' && $post['sn_filter'] != 'SN'){$filters['sn'] = $post['sn_filter'];}
	  		if(isset($post['createdafter_filter']) && $post['createdafter_filter'] != ''){$filters['starttime'] = strtotime($post['createdafter_filter']);}
	  		if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['endtime'] = strtotime($post['createdbefore_filter']) + (3600* 24 - 1);}
			if(isset($post['site_filter']) && $post['site_filter'] != '-1'){
				$filters['site'] = $post['site_filter'];
			}else{
				$filters['site'] = null;
			}
			if(isset($post['limit_filter']) && preg_match('/^0|[1-9]\d*$/', $post['limit_filter']) > 0) {
			    $filters['limit'] = intval($post['limit_filter']);
			}
	  		$_SESSION['statistics_filters'] = $filters;
  		}
  	}

  	$filters = $this->getStatisticsFilters();
  	$this->view->assign('filters', $filters);
  	
  	$siteInstance = Site_Model::getInstance();
  	$sitesInfo = $siteInstance->getAllSites();
  	if(isset($filters['site'])){
  		$curSite = $siteInstance->getSite($filters['site']);
  		$pageSiteMark = $curSite->name;
  	}else{
  		$pageSiteMark = '所有网站';
  	}
  	
  	$topProducts = $this->_statisticInstance->getPopularOrderItems(isset($filters['sn'])?$filters['sn']:null, $filters['starttime'], $filters['endtime'],$filters['site'],  1, isset($filters['limit']) ? $filters['limit'] : 10);
  	
  					foreach($topProducts as $k=>$v){
						$productImages = glob("files/". $v->SN ."/*.*");
						if(count($productImages) > 0){
							$v->imageSource = $productImages[0];
						}else{
							if(isset($v->Image) && $v->Image != ''){
								$v->imageSource = $v->Image;
							}else{
								$v->imageSource = null;
							}
						}
					}

  	$this->view->assign('popularProducts', $topProducts);
  	
  	$this->view->assign('pageSiteMark', $pageSiteMark);
  	$this->view->assign('sites', $sitesInfo);
  	
  	$this->view->assign('statistics_title', '产品统计');
  	$this->view->assign('templatefile', 'product.tpl');
  	$this->view->render('statistics.tpl');
  }
  
  public function userAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$filters = array();

  	if($this->isPost()){
  		$post = $_POST;
  		unset($_SESSION['statistics_filters']);
  		if(!isset($post['clear_filters'])){
	  		if(isset($post['createdafter_filter']) && $post['createdafter_filter'] != ''){$filters['starttime'] = strtotime($post['createdafter_filter']);}
	  		if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['endtime'] = strtotime($post['createdbefore_filter']) + (3600* 24 - 1);}
			if(isset($post['site_filter']) && $post['site_filter'] != '-1'){
				$filters['site'] = $post['site_filter'];
			}else{
				$filters['site'] = null;
			}
			$filters['sn'] = null;
	  		$_SESSION['statistics_filters'] = $filters;
  		}
  	}

  	$filters = $this->getStatisticsFilters();
  	
  	$this->view->assign('filters', $filters);
  	
  	$siteInstance = Site_Model::getInstance();
  	$sitesInfo = $siteInstance->getAllSites();
  	if(isset($filters['site'])){
  		$curSite = $siteInstance->getSite($filters['site']);
  		$pageSiteMark = $curSite->name;
  	}else{
  		$pageSiteMark = '所有网站';
  	}
  	
  	$this->view->assign('pageSiteMark', $pageSiteMark);
  	$this->view->assign('sites', $sitesInfo);
  	
  	$this->view->assign('statistics_title', '用户统计');
  	$this->view->assign('templatefile', 'user.tpl');
  	$this->view->render('statistics.tpl');
  }
  
  public function orderAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$filters = array();

  	if($this->isPost()){
  		$post = $_POST;
  		unset($_SESSION['statistics_filters']);
  		if(!isset($post['clear_filters'])){
	  		if(isset($post['createdafter_filter']) && $post['createdafter_filter'] != ''){$filters['starttime'] = strtotime($post['createdafter_filter']);}
	  		if(isset($post['createdbefore_filter']) && $post['createdbefore_filter'] != ''){$filters['endtime'] = strtotime($post['createdbefore_filter']) + (3600* 24 - 1);}
  			if(isset($post['site_filter']) && $post['site_filter'] != '-1'){
				$filters['site'] = $post['site_filter'];
			}else{
				$filters['site'] = null;
			}
			$filters['sn'] = null;
	  		$_SESSION['statistics_filters'] = $filters;
  		}
  	}

  	
  	$filters = $this->getStatisticsFilters();
  	
  	$this->view->assign('filters', $filters);
  	
  	
  	$siteInstance = Site_Model::getInstance();
  	$siteInfo = $siteInstance->getAllSites();
  	if(isset($filters['site'])){
  		$curSite = $siteInstance->getSite($filters['site']);
  		$pageSiteMark = $curSite->name;
  	}else{
  		$pageSiteMark = '所有网站';
  	}
  	
  	$this->view->assign('pageSiteMark', $pageSiteMark);
  	$this->view->assign('sites', $siteInfo);
  	$this->view->assign('statistics_title', '订单统计');
  	$this->view->assign('templatefile', 'order.tpl');
  	$this->view->render('statistics.tpl');
  }
  
  
  protected function getStatisticsFilters(){
    if(isset($_SESSION['statistics_filters'])){
  		$filters = $_SESSION['statistics_filters'];
  	}else{
  		//use default settings.
  		$filters = array();
  		$filters['sn'] = null;
  		$filters['endtime'] = strtotime(date('m/d/Y', time())) + (3600* 24 - 1);
  		$filters['starttime'] = strtotime(date('m/d/Y', time())) - 3600*24*7;
  		$filters['site'] = null;
  	}
  	return $filters;
  }
  
  public function getdataAction($param){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$filters = $this->getStatisticsFilters();
  	if($param == 'user'){
  		$user_country_rank_pie = $this->_statisticInstance->get_user_country_group_chart_data('count', 'delivery_country', array('string','int'), $filters['starttime'], $filters['endtime'],$filters['site'], 1, 10);
  		$user_country_rank_table = $this->_statisticInstance->get_user_country_group_table_data(array('string','int', 'float'), $filters['starttime'], $filters['endtime'],$filters['site'],  1, 10);
  		$user_order_nums_rank = $this->_statisticInstance->get_user_rank_by_orders_data(array('string','int'), $filters['starttime'], $filters['endtime'],$filters['site'],  1, 10);
  		$user_order_amount_rank = $this->_statisticInstance->get_user_rank_by_payments_data(array('string','float'), $filters['starttime'], $filters['endtime'],$filters['site'],  1, 10);
  		
  		$jsonArray = array('U_CONTRY_RANK_PIE'=>$user_country_rank_pie, 
  							'U_CONTRY_RANK_TABLE'=>$user_country_rank_table, 
  							'U_ORDER_NUMS_RANK'=>$user_order_nums_rank, 
  							'U_ORDER_AMOUNT_RANK'=>$user_order_amount_rank, 
  		);
  		echo json_encode($jsonArray);
  	}else if($param == 'order'){
  		$order_revenue_data = $this->_statisticInstance->get_order_revenue_data(array('string','float','float','float'), $filters['starttime'], $filters['endtime'],$filters['site'],  1, null);
  		$order_validation_data = $this->_statisticInstance->get_order_validation_data(array('string','int', 'int'), $filters['starttime'], $filters['endtime'],$filters['site'],  null, null);
  		$order_product_qty_data = $this->_statisticInstance->get_order_product_qty_data(array('string', 'int'), $filters['starttime'], $filters['endtime'], $filters['site'],  1, null);
  		$order_by_site_data = $this->_statisticInstance->get_order_data_by_site(array('site', 'string', 'float', 'int', 'int'), $filters['starttime'], $filters['endtime'],$filters['site'],  null, null);
  		$order_profit_data = $this->_statisticInstance->get_order_profit_data(array('string','float','float','float', 'float'), $filters['starttime'], $filters['endtime'],$filters['site'],  1, null);
  		
  		$jsonArray = array('O_REVENUE_DATA'=>$order_revenue_data,
  						   'O_VALIDATION_DATA'=>$order_validation_data,
  						   'O_PRODUCT_QTY_DATA' => $order_product_qty_data,
  						   'O_BYSITE_DATA'=>$order_by_site_data,
  						   'O_PROFIT_DATA'=>$order_profit_data,
  		);
  		echo json_encode($jsonArray);
  	}else if($param == 'product'){
		 $product_selling_data = $this->_statisticInstance->get_product_selling_data(array('string','int','float'), isset($filters['sn'])?$filters['sn']:null, $filters['starttime'], $filters['endtime'],$filters['site'],  1, null);
  		$jsonArray = array('P_SELLING_DATA'=>$product_selling_data);
  		echo json_encode($jsonArray);
  	}
  }
  
  
  
}