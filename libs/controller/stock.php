<?php
class Stock_Controller extends MD_Controller  {
  private $_productInstance;
  private $_stockInstance;

  public function init()
  {
    $this->_productInstance = Product_Model::getInstance();
    $this->_stockInstance = Stock_Model::getInstance();
    $this->view->assign('pageLabel', 'stock');
  }

  public function showAction(){
  	$this->stockupdateAction();
  }
  
  
  public function stockupdateAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    $this->view->assign('pageLabel', 'stock');
    if(isset($_SESSION['stock_unchanged'])){
    	$this->view->assign('unchanged', $_SESSION['stock_unchanged']);
    	unset($_SESSION['stock_unchanged']);
    }
    if(isset($_SESSION['stock_update_count'])){
    	$this->view->assign('update_count', $_SESSION['stock_update_count']);
    	unset($_SESSION['stock_update_count']);
    }
    $this->view->assign('stock_title', '增加库存');
    $this->view->assign('templatefile', 'update_stock.tpl');
    $this->view->render('stock/stock.tpl');
  }
  
  public function stockreplaceAction() {
    global $user;
    if(!$user->uid){
        gotoUrl('');
    }
    $this->view->assign('pageLabel', 'stock');
    if(isset($_SESSION['stock_unchanged'])){
        $this->view->assign('unchanged', $_SESSION['stock_unchanged']);
        unset($_SESSION['stock_unchanged']);
    }
    if(isset($_SESSION['stock_update_count'])){
        $this->view->assign('update_count', $_SESSION['stock_update_count']);
        unset($_SESSION['stock_update_count']);
    }
    $this->view->assign('stock_title', '替换库存');
    $this->view->assign('templatefile', 'update_stock.tpl');
    $this->view->assign('overwrite', true);
    $this->view->render('stock/stock.tpl');
  }
  
  public function viewAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    $this->view->assign('pageLabel', 'stock');
    $this->view->assign('stock_title', '查看库存');
    $this->view->assign('templatefile', 'view_stock.tpl');
    $this->view->render('stock/stock.tpl');
  }
  
  
  public function ajaxgetstockdataAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$stockInstance = Stock_Model::getInstance();
  	$stock_lock_enable = MD_Config::get('stock_lock_enable', true);
  	$stockData = $stockInstance->getStockData($stock_lock_enable);
  	$statisticsInstance = Statistics_Model::getInstance();
  	
  	if($stock_lock_enable){
  		$newData = $statisticsInstance->convertDBdata2JSONArray($stockData, array('string','int', 'int', 'string', 'string'));
  	}else{
  		$newData = $statisticsInstance->convertDBdata2JSONArray($stockData, array('string','int', 'string', 'string'));
  	}
  	
  	echo json_encode($newData);
  }
  
  public function generatecsvfileAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$stockInstance = Stock_Model::getInstance();
  	$stock_lock_enable = MD_Config::get('stock_lock_enable', true);
  	$stockData = $stockInstance->getStockData($stock_lock_enable);
  	$statisticsInstance = Statistics_Model::getInstance();
  	if($stock_lock_enable){
  		$newData = $statisticsInstance->convertDBdata2JSONArray($stockData, array('magicstring','int', 'int', 'string', 'string'));
  	}else{
  		$newData = $statisticsInstance->convertDBdata2JSONArray($stockData, array('magicstring','int', 'string', 'string'));
  	}
  	
    $filename = 'stock-'.strval(TIMESTAMP).'.csv';
    download_send_headers($filename);
    outputCSV($newData);
  }
  
  
  
  public function insertnewstockdataAction(){
  	
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}

    if($this->isPost()){
    	$post = $_POST;
    	if(strpos($_POST['stock_data'], "\r\n") !== false){
    		$stock_data = explode("\r\n", $post['stock_data']);
    	}else{
    		$stock_data = explode("\n", $post['stock_data']);
    	}
    	if(count($stock_data) > 0){
    		$delimeter = "\t";
    		if(strpos($stock_data[0], "\t") === false){
    			$delimeter = ",";
    		}
    		if(!preg_match('/[0-9]/', $stock_data[0])){
		    	//contains title.
		    	unset($stock_data[0]);
		    }
		    if(count($stock_data) > 0){
		    	if(isset($post['overwrite'])){
		    		list($unchanged, $update_count) = $this->updateStockFromData($stock_data, $delimeter, false);
		    	}else{
		    		list($unchanged, $update_count) = $this->updateStockFromData($stock_data, $delimeter, true);
		    	}
		    	$_SESSION['stock_unchanged'] = $unchanged;
		    	$_SESSION['stock_update_count'] = $update_count;
		    }
    	}
    	if(isset($post['overwrite'])) {
    	    gotoUrl('stock/stockreplace');
    	}
    }
    gotoUrl('stock');
  }
  
  public function insertnewstockfileAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    if($this->isPost()){
	  	$contents = iconv('gb2312', 'UTF-8',file_get_contents($_FILES['upload_file']['tmp_name']));
	  	if(strpos($contents, "\r\n") !== false){
	    	$stock_data = explode("\r\n", $contents);
	    }else{
	    	$stock_data = explode("\n", $contents);
	    }
	    if(count($stock_data) > 0){
	        if(!preg_match('/[0-9]/', $stock_data[0])){
		    	//contains title.
		    	unset($stock_data[0]);
		    }
		    if(isset($post['overwrite'])){
	    	  list($unchanged, $update_count)= $this->updateStockFromData($stock_data, ",", false);
		    } else {
		        list($unchanged, $update_count) = $this->updateStockFromData($stock_data, ",", true);
		    }
	    	$_SESSION['stock_unchanged'] = $unchanged;
	    	$_SESSION['stock_update_count'] = $update_count;
	    }
    }
    gotoUrl('stock');
  }
  
  function updateStockFromData($stock_data, $delimeter, $is_add){
    $productInstance = Product_Model::getInstance();
    $unchanged = array();
    $update_count = 0;
    
    if(count($stock_data) == 0){
    	return $unchanged;
    }

      foreach ($stock_data as $v){
      	if(trim($v, ' ,"\t\n\r\0\x0B') == '') continue;
    	
      	$dataItem = explode($delimeter, $v);
      	$sn = isset($dataItem[0])?trim($dataItem[0], ' "'):null;
      	$color = isset($dataItem[1])?trim($dataItem[1], ' "'):null;
      	$size = isset($dataItem[2])?trim($dataItem[2], ' "'):null;
      	$stock_qty = isset($dataItem[3])?trim($dataItem[3], ' "'): "0";
      	if ($stock_qty < 0){
      	    $unchanged[] = $v;
      	    continue;
      	}
      	$supplier = isset($dataItem[4])?trim($dataItem[4], ' "'):null;
      	$bought_price = isset($dataItem[5])?trim($dataItem[5], ' "'):"0.0";
        $supplier_sn = isset($dataItem[6])?trim($dataItem[6], ' "'):null;
      	$type = isset($dataItem[7])?trim($dataItem[7], ' "'):null;
      	$weight = isset($dataItem[8])?trim($dataItem[8], ' "'):null;
      	
      	if($sn == null ||$sn == '') continue;
      	
      	$productInfo = $productInstance->getProductBySn($sn);
      	if(empty($bought_price) || $bought_price == "0.0") {
      	    if ($productInfo) {
      	        $bought_price = $productInfo->stock_price;
      	    }
      	} 
    	//首先获得avid.
    	$prop = array('color'=>$color, 'size'=>$size);
    	$affected = $this->_stockInstance->updateStockQtyByProperties($sn, $prop, $stock_qty, $bought_price, $is_add);

    	
    	if($affected == false){
    		$unchanged[] = $v;
    	}else{
    		$update_count++;
    	}
    	
    	//记录不在终端出现的产品。
    	if(!$productInfo){
    		//该产品不存在，记录该产品。
    		//如果有weight
    		if(!isset($weight)){
    			$weight = '0.0';
    		}
    	   	if(!isset($supplier)){
    			$supplier = '';
    		}
    		if(!isset($type)){
    			$type = 'lingerie';
    		}
    		$attributes = array();
    		$attributes['color'] = $color;
    		$attributes['size'] = $size;
    		//马上插入该产品。type只有在产品插入的时候才有用。
    		$productInstance->insertProduct($sn, $type, $weight, $supplier, $attributes, $supplier_sn);
    	}else{
    		//产品已经存在
    		$changedAttr = false;
    		$attrData = $productInfo->attributes;
    		if(strpos($productInfo->attributes['color'], $color) === false){
    			//该颜色属性不存在
    			$changedAttr = true;
    			$attrData['color'] .=','.$color;
    		}
    		if(strpos($productInfo->attributes['size'], $size) === false){
    			//该大小属性不存在
    			$changedAttr = true;
    			$attrData['size'] .=','.$size;
    		}
    		$set = array();
    		if($changedAttr){
    			$set['attributes'] = $attrData;
    		}
    		if(isset($supplier) && $supplier != ''){
    			$set['suppliers'] = $supplier;
    		}
    		//如果有weight
    		if(isset($weight) && $weight > 0){
    			$set['wt'] = $weight;
    		}
    		// 如果有supplier_sn
    		if(isset($supplier_sn)) {
    		    $set['suppliers_sn'] = $supplier_sn;
    		}
    		if(count($set) > 0){
    			$productInstance->updateProductBySn($sn, $set);
    		}
    	}
    }
    return array($unchanged, $update_count);
  }
   
  public function adjuststockAction(){
    global $user;
    if(!$user->uid){
        gotoUrl('');
    }
    //获得准备出货订单，缺货订单， 已做安排订单列表。从memento中。
    $orders_pools = cache::get('order_memento');
    if($orders_pools){
        //readyExportOrders needImportOrders arrangedOrders ommitedOrders
        $orders_pools = $orders_pools->data;
        
        //获得准备出货订单，缺货订单， 已做安排订单列表
        $orders = array_merge($orders_pools['readyExportOrders'], $orders_pools['needImportOrders'], $orders_pools['arrangedOrders']);
        $orderSummary = $this->getProductSummaryByOrders($orders);
        $skuItemList = $this->convertOrderSummaryToSkuList($orderSummary);
        $this->sortSkuItemByAdjustTime($skuItemList);
        
        $this->view->assign('pageLabel', 'stock_adjust');
        $this->view->assign('skuItemList', $skuItemList);
        $this->view->render('stock/adjust_stock.tpl');
    }
  }

  private function convertOrderSummaryToSkuList($orderSummary) {
      foreach ($orderSummary as $p_sn => $product) {
          foreach ($product->requirements as $avid => $requirement) {
              $skuItem = new stdClass();
              $skuItem->imageSource = $product->imageSource;
              $skuItem->p_sn = $p_sn;
              $skuItem->avid = $avid;
              $skuItem->real_qty = $requirement->real_qty;
              $skuItem->lack_qty = $requirement->lack_qty;
              $skuItem->data = $requirement->data;
              $stockItem = $this->_stockInstance->getStockItem($p_sn, $avid);
              $skuItem->stock_qty = $stockItem->stock_qty;
              $skuItem->adjust_time = isset($stockItem->adjust_time) ? intval($stockItem->adjust_time) : 0;
              $skuItemList[] = $skuItem;
          }
      }
      return $skuItemList;
  }
  
  public function checkStockParam($post, &$error) {
      if (!isset($post['p_sn'])) {
          $error = "p_sn is empty";
          return false;
      }
      if (!isset($post['avid'])) {
          $error = "avid is empty";
          return false;
      }
      if (!isset($post['value'])) {
          $error = "stock_qty is emtpy";
          return false;
      } else if (!preg_match("/^\d+$/", $post['value'])) {
          $error = "stock_qty format error";
          return false;
      }
      
      return true;
  }
  
  public function ajaxadjustproductstockAction() {
      $post = $_POST;
      if (!$this->checkStockParam($post, $error)) {
          echo json_encode(array('success' => false, 'error' => $error));
          exit -1;
      }
      $stockItem = $this->_stockInstance->getStockItem($post['p_sn'], $post['avid']);
      $adjustTime = time();
      if (empty($stockItem)) {
          if ($this->_stockInstance->insertStockItem($post['p_sn'], $post['avid'], $post['value'], null, null, $adjustTime) > 0) {
              echo json_encode(array('success' => true, 'p_sn' => $post['p_sn'], 'avid' => $post['avid'], 'adjust_time' => date('Y-m-d H:i:s', $adjustTime)));
          } else {
              echo json_encode(array('success' => false, 'error' => 'failed to update stock db'));
          }
      } else {
          if ($this->_stockInstance->updateStock($post['p_sn'], $post['avid'], array('stock_qty' => $post['value'], 'adjust_time' => $adjustTime))) {
              echo json_encode(array('success' => true, 'p_sn' => $post['p_sn'], 'avid' => $post['avid'], 'adjust_time' => date('Y-m-d H:i:s', $adjustTime)));
          } else {
              echo json_encode(array('success' => false, 'error' => 'failed to update stock db'));
          }
      }
      exit -1;
  }
  
  public function ajaxrefreshproductsstockAction() {
      $post = $_POST;
      $error = "";
      if (!isset($post['p_sn'])) {
          $error = "p_sn is empty";
      } elseif (!isset($post['avid'])) {
          $error = "avid is empty";
      }
      if (!empty($error)) {
          echo json_encode(array('success' => false, 'error' => $error));
          exit -1;
      }
      $adjustTime = time();
      if ($this->_stockInstance->updateStock($post['p_sn'], $post['avid'], array('adjust_time' => $adjustTime))) {
          echo json_encode(array('success' => true, 'p_sn' => $post['p_sn'], 'avid' => $post['avid'], 'adjust_time' => date('Y-m-d H:i:s', $adjustTime)));
      } else {
          echo json_encode(array('success' => false, 'error' => 'failed to update stock db'));
      }
  }
  
  public function sortSkuItemByAdjustTime(&$skuItemList) {
      usort($skuItemList, array($this, "skuItemComp"));
  }
  /**
   * 根据校准时间排序(从小到大序)
   */
  private function skuItemComp($lhs, $rhs) {
      if ($lhs->adjust_time == $rhs->adjust_time) {
          return 0;
      }
      return ($lhs->adjust_time > $rhs->adjust_time) ? 1 : -1;
  }
 
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
  
	/**
	  * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
	  * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
	  */
	function arrayToCsv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
	    $delimiter_esc = preg_quote($delimiter, '/');
	    $enclosure_esc = preg_quote($enclosure, '/');
	
	    $output = array();
	    foreach ( $fields as $field ) {
	        if ($field === null && $nullToMysqlNull) {
	            $output[] = 'NULL';
	            continue;
	        }
	
	        // Enclose fields containing $delimiter, $enclosure or whitespace
	        if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
	            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
	        }
	        else {
	            $output[] = $field;
	        }
	    }
	
	    return implode( $delimiter, $output );
	}

}