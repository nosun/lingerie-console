<?php
class Site_MDTrade_Model extends Site_API_Model
{
	private $_siteDB;
	private $_siteName;
	private $_siteUrl;
	private $_client;
	//private $_siteImgDir;
	
   /**
   * @return Site_MDTrade_Model
   */
	public static function getInstance($siteName, $siteUrl)
	{
		return parent::getInstance(__CLASS__, $siteName, $siteUrl);
	}
  public function __construct($siteName, $siteUrl)
  {
    $this->codebase = 'MDTrade';
    $this->init($siteName, $siteUrl);
  }
  
  public function init($siteName, $siteUrl){
  	$sitesDBConfig = MD_Config::get('sitesdb');
  	$this->_siteName = $siteName;
  	$this->_siteUrl = $siteUrl;
  	if($sitesDBConfig && key_exists($siteName, $sitesDBConfig)){
		$this->_siteDB = new MD_Database();
		$this->_siteDB->connect($sitesDBConfig[$siteName]);
	}
	$this->_client = new PHPRPC_Client($siteUrl.'/apiindex.php');
  }
  public function getCategories(){
  	$this->_siteDB->select('tid as site_cid, ptid1 as site_pcid, name');
  	$this->_siteDB->where('vid', 3);
  	$result = $this->_siteDB->get('terms');
  	//now we input the category info into the console db.
  	return $result->all();
  }
  
  public function getProductFields($field_name, $type){
  	$field_name = substr($field_name, 0, strlen($field_name) - 1);
  	$this->_siteDB->select('settings');
  	$this->_siteDB->where('field_name', $field_name);
  	$this->_siteDB->where('type', $type);
  	$result = $this->_siteDB->get('products_type_fields');
  	$rawData = $result->row();
  	if($rawData){
  		$data = unserialize($rawData->settings);
  		$data['options'] = explode("\r\n", $data['options']);
  		return $data;
  	}
  	return null;
  }
  
  public function getProductTypes(){
  	$this->_siteDB->select('type');
  	$result = $this->_siteDB->get('products_type');
  	return $result->all();
  }
  
  public function syncProductsFromSite(){return false;}
  
  public function syncToSite($productInfo, $product_siteInfo, $categoryInfo, $isSyncImg = true){
  	//insert into product.
  	$site_pid = $this->insertProduct($productInfo, $product_siteInfo, $categoryInfo);
  	//config related products.
  	$this->configRelatedProducts($site_pid, $productInfo->sn);
  	//copy product information for another same product.
  	
  	//insert into field_{type}_color and field_{type}_size.
  	foreach ($productInfo->attributes as $aName=>$aValue){
  		$this->insertFields($site_pid, $aName, $productInfo->type, $aValue);
  	}
  	
  	
  	//$this->insertFields($site_pid, 'color', $productInfo->type, $productInfo->colors);
  	//$this->insertFields($site_pid, 'size', $productInfo->type, $productInfo->sizes);

  	//TODO: insert into terms_products.
  	//upload files at last.
  	$returnCode = -1;
  	if($isSyncImg == 'true'){
	  	$response = $this->uploadImage($site_pid, 'files/'.$product_siteInfo->sn, $product_siteInfo->url.'/imgbatchuploader/upload/', $product_siteInfo->sn.'/');
	  	log::save('DEBUG', 'image upload response', $response);
	  	if($response == 'success' || strlen($response) <= 10){
	  		$returnCode = 1;
	  	}else{
	  		$returnCode = 0;
	  	}
  	}else{
  		$returnCode = 2;
  	}
  	//if there are other_cids in product site table. need to copy product information to other products.
  	$this->sendProductCopy($productInfo, $product_siteInfo, $site_pid);
  	return $returnCode;
  }
  
  public function uploadImage($site_pid, $fileDir, $target_url, $targetDir){
  	$files = glob($fileDir ."/*.*");
  	$uselessFiles = glob($fileDir ."/blob*");
  	$files = array_diff($files, $uselessFiles);
  	$post = array();
  	foreach($files as $k=>$filepath){
  		$filename = basename($filepath);
  		$post[$filename] = '@'.realpath($filepath);
  	}
  	$post['pid'] = $site_pid;
  	$url  = $target_url;//target url
  	$ch = curl_init();
  	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  	curl_setopt($ch, CURLOPT_POST, 1 );
  	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  	$response = curl_exec( $ch );
	if ($error = curl_error($ch) ) {
	   die($error);
	}
	curl_close($ch);
	return $response;
  }
  
  public function getWholesaleUser($startId) {
  	$sql = sprintf("select * from wholesale_user where id > %d", $startId);
  	$result = $this->_siteDB->query($sql);
  
  	if ($result) {
  		return $result->all();
  	}
  	return false;
  }
  
  public function insertProduct($productInfo, $product_siteInfo, $categoryInfo){
  	$type = $productInfo->type;
  	$sn = $productInfo->sn;
  	$name = $product_siteInfo->site_pname;
  	$status = $product_siteInfo->shelfstate;
  	$name = trim($name);
  	if($categoryInfo === false){
  		$parentTid = '0';
  		$tid = '0';
  	}
  	else{
	  	$parentTid = $categoryInfo->site_pcid;
	
	  	if(intval($parentTid) === 0){
	  		$parentTid = $categoryInfo->site_cid;
	  		$tid = '0';
	  	}else{
	  		$tid = $categoryInfo->site_cid;
	  	}
  	}
  	$price = $product_siteInfo->price;
  	$listprice = $product_siteInfo->listprice;
  	$wt = $productInfo->wt;
  	$path_alias = str_replace(array(' ','/'), '-', $name);

  	return $this->insertProductRow($type, $sn, $sn, $name, $parentTid, $tid, $price, $listprice, $wt, $path_alias, '', $status);
  }
  
  public function updateProduct($updateData, $sn){
  	$response = $this->_client->product_update($updateData, $sn);
  	return $response;
  }
  
  public function updateOrder($updateData, $orderNumber){
  	$response = $this->_client->order_update($updateData, $orderNumber);
  	return $response;
  }

  public function getOrders($startTime, $endTime){
  	$result = $this->_siteDB->query('select * from orders where (created >= '.strval($startTime).' and created <= '.strval($endTime + 3600*24) . ') or (updated >= '.strval($startTime).' and updated <= '. strval($endTime + 3600*24).') order by created DESC' );
  	$orders = $result->all();
  	
    //transform order to the current order format.
    foreach($orders as $index=>$order){
    	unset($order->oid);
    	unset($order->uid);
    	unset($order->description);
    	//@TODO implement this later.
    	unset($order->delivery_cid);
    	unset($order->delivery_pid);
    	unset($order->created_ip);
    	unset($order->updated_ip);
    }
    return $orders;
  }
  
  public function getOrderItems($orderNumber){
  	$this->_siteDB->select('orders_items.sn, orders_items.number, orders_items.qty,orders_items.total_amount, orders_items.data, products.filepath as image_source');
  	$this->_siteDB->from('orders_items');
  	$this->_siteDB->join('orders', 'orders.oid = orders_items.oid');
  	$this->_siteDB->join('products', 'orders_items.pid = products.pid');
  	$this->_siteDB->where('orders.number', $orderNumber);
  	$result = $this->_siteDB->get();
  	$orderItems = $result->all();
  	

  	foreach($orderItems as $k=> $v){
		//GET SITE IMAGE PREFIX.
		
		$v->image_source = $this->_siteUrl . '/images/cache/60x90/' . $v->image_source;
		$v->data = $this->convertOrderItemPropertyData($v->data);
	}
	
  	return $orderItems;
  	
  }
  
  public function batchGetOrderItems($orderList, $orderCountPerFetch=500){
      $orderCount = count($orderList);
      if ($orderCount == 0){
          return array();
      }
      $orderItemList = array();
      $index = 0;
      while ($index < $orderCount) {
          $fetchOrders = array_slice($orderList, $index, $orderCountPerFetch);
          $index += $orderCountPerFetch;
          $this->_siteDB->select('orders_items.sn, orders_items.number, orders_items.qty,orders_items.total_amount, orders_items.data, products.filepath as image_source, orders.number as order_number');
          $this->_siteDB->from('orders_items');
          $this->_siteDB->join('orders', 'orders.oid = orders_items.oid');
          $this->_siteDB->join('products', 'orders_items.pid = products.pid');
          $this->_siteDB->where('orders.number IN ', $fetchOrders);
          $result = $this->_siteDB->get();
          $orderItems = $result->all();
          
          foreach($orderItems as $k=> $v){
              $v->image_source = $this->_siteUrl . '/images/cache/60x90/' . $v->image_source;
              $v->data = $this->convertOrderItemPropertyData($v->data);
              $orderItemList[] = $v;
          }
      }
      return $orderItemList;
  }
  
  private function convertOrderItemPropertyData($oldData){
  	$propsArray = unserialize($oldData);
  	$newData = array();
  	$newData['Color'] = 'As Shown';
  	$newData['Size'] = 'One Size';
  	foreach($propsArray as $k=>$v){
  	    $key = strtolower($k);
  	  	if($key == 'color'){
  	  	  $newData['Color'] = $v;
  	  	}else if($key == 'size' || $key == 'plus size lingerie' || $key == 'sizes'){
  	  	  $newData['Size'] = str_replace('2XL', 'XXL', $v);
  	  	}
  	}
  	return serialize($newData);
  }
  
  public function getSiteName(){
  	return $this->_siteName;
  }
  
  public function getSiteUrl(){
  	return $this->_siteUrl;
  }
  
  protected function configRelatedProducts($site_pid, $sn){
  	$sn_segs = explode('-', $sn);
  	$base_sn = $sn_segs[0];
  	$this->_siteDB->select('pid');
  	$this->_siteDB->where('sn like', $base_sn.'%');
  	$result = $this->_siteDB->get('products');
  	$data = $result->allWithKey('pid');
  	if($data && count($data) > 1){
  		//has related items.
  		$query = "insert ignore into products_relations values ";
  		foreach($data as $k=>$v){
  			if($k != $site_pid){
  				//insert into product relations.
  				$query.="('".$k."', ".$site_pid."),";
  				$query.="('".$site_pid."', ".$k."),";
  			}
  		}
  		$query = substr($query, 0, strlen($query)-1);
  		$this->_siteDB->query($query);
  	}
  }
  
  protected function sendProductCopy($productInfo, $product_siteInfo, $refer_site_pid){
  	//change all sn into another sn.
  	//insert into product.
  	$alt_cids = unserialize($product_siteInfo->alt_cids);
  	
  	$this->_siteDB->select('filepath');
  	$this->_siteDB->where('pid', $refer_site_pid);
  	$result = $this->_siteDB->get('products');
  	
  	$filepath = $result->one();

  	if(!$alt_cids){$alt_cids = array();}
  	foreach($alt_cids as $k=>$v){
	  	$type = $productInfo->type;
	  	$product_version = $k;
	  	$name = $product_siteInfo->site_pname;
	  	$status = $product_siteInfo->shelfstate;
	  	$categoryModel = Category_Model::getInstance();
	  	$categoryInfo = $categoryModel->getCategoryByCid($v);
	  	$parentTid = $categoryInfo->site_pcid;
	  	$tid = $categoryInfo->site_cid;
	  	$price = $product_siteInfo->price;
	  	$listprice = $product_siteInfo->listprice;
	  	$wt = $productInfo->wt;
	  	$sn_suffix = $product_version;
	  	$path_alias = str_replace(' ', '-', $product_siteInfo->site_pname).$sn_suffix;
	  	$site_pid = $this->insertProductRow($type, $productInfo->sn, $productInfo->sn . $sn_suffix, $name, $parentTid, $tid, $price, $listprice, $wt, $path_alias, $filepath, $status);

	  	//config related products.
	  	$this->configRelatedProducts($site_pid, $productInfo->sn);
	  	
	  	//copy product information for another same product.
  	  	//insert into field_{type}_color and field_{type}_size.
	  	
	  	//$this->insertFields($site_pid, 'color', $productInfo->type, $productInfo->colors);
	  	//$this->insertFields($site_pid, 'size', $productInfo->type, $productInfo->sizes);

	  	//insert into field_{type}_color and field_{type}_size.
	  	foreach ($productInfo->attributes as $aName=>$aValue){
	  		$this->insertFields($site_pid, $aName, $productInfo->type, $aValue);
	  	}  	
	  	//$this->insertFields($site_pid, 'color', $productInfo->type, $productInfo->colors);
	  	//$this->insertFields($site_pid, 'size', $productInfo->type, $productInfo->sizes);

	  	//then use the image settings for original product cid.
	  	$this->_siteDB->select('*');
	  	$this->_siteDB->where('pid', $refer_site_pid);
	  	$result = $this->_siteDB->get('products_files');
	  	$data = $result->all();
	  	foreach($data as $k=>$v){
	  		$set = array();
	  		$set['pid'] = $site_pid;
	  		$set['fid'] = $v->fid;
	  		$set['weight'] = $v->weight;
	  		$this->_siteDB->insert('products_files', $set, true);
	  	}
  	}
  }

  /*
   * The terms products only for special terms. Not used now.
   */
  protected function insertTermsProducts($site_pid, $site_cid){
  	$this->_siteDB->select('*');
  	$this->_siteDB->where('pid', $site_pid);
  	$result = $this->siteDB->get('terms_products');
  	$data = $result->all();
  	//TODO. To be completed.
  }
  
  protected function insertFields($site_pid, $fieldType, $productType, $fields){
    //insert into field_{type}_color and field_{type}_size.

	  	//TODO: Tempororily remove the data.
	  
	  	/*
	  	$this->_siteDB->select('*');
	  	$this->_siteDB->where('pid', $site_pid);
	  	//$this->_siteDB->where('field_'.$fieldType, $field);
	  	$this->_siteDB->orderby('delta DESC');
	  	$result = $this->_siteDB->get('field_'. $productType . '_'.$fieldType);n    
	  	$data = $result->allWithKey('field_'.$fieldType);
	  	if(!$data){
	  		$startDelta = 0;
	  	}else{
	  		$firstElement = array_shift(array_values($data));
	  		$startDelta = $firstElement->delta + 1;
	  	}
	  	*/
	  	$this->_siteDB->delete('field_'. $productType . '_'.$fieldType, array('pid'=>$site_pid));
	  	$startDelta = 0;
	  	
  		$fieldArray = explode(',', $fields);  		
  		foreach($fieldArray as $key=>$field){
  			$field = trim($field);
  			if($field == '')continue;
  			//if(!key_exists($field, $data)){
  				//no such field, add new.
		  		$field_set = array();
		  		$field_set['pid'] = $site_pid;
		  		$field_set['delta'] = $startDelta;
		  		$field_set['field_'.$fieldType] = $field;
		  		$this->_siteDB->insert('field_'. $productType . '_'. $fieldType, $field_set, true);
		  		$startDelta++;
  			//}
  		}
  }
  
  

  
  private function insertProductRow($type, $sn, $number, $name, $parentTid, $tid, $price, $listprice, $wt, $path_alias, $file_path, $status = 1){
  	$set = array();
  	$set['type'] = $type;
  	$set['sn'] = $sn;
  	$set['number'] = $number;
  	$set['name'] = $name;
  	$set['status'] = $status;
  	$set['shippable'] = 1;
  	$set['free_shipping'] = 0;
  	$set['directory_tid1'] = $parentTid;
  	$set['directory_tid2'] = $tid;
  	$set['directory_tid3'] = 0;
  	$set['directory_tid4'] = 0;
  	$set['brand_tid'] = 0;
  	$set['sell_price'] = $price;
  	$set['list_price'] = $listprice;
  	$set['wt'] = $wt;
  	$set['stock'] = 1000000000;
  	$set['sell_min'] = 1;
  	$set['sell_max'] = 0;
  	$set['fid'] = 0;
  	$set['filepath'] = $file_path;
  	$set['videopath'] = '';
  	$set['summary'] = '';
  	$set['description'] = '';
  	$set['path_alias'] = $path_alias;
  	$set['template'] = '';
  	$set['visits'] = 0;
  	$set['transactions'] = 0;
  	$set['created'] = TIMESTAMP;
  	$set['updated'] = TIMESTAMP;
  	$set['pvid'] = 0;
  	$set['visible'] = 1;
  	$set['weight'] = 0;
  	if (strpos($sn, 'MD_') === 0) {
  		$set['weight'] = rand(8000, 10000);
  	}
  	$set['sphinx_key'] = '';
  	$this->_siteDB->select('*');
  	$this->_siteDB->where('sn', $sn);
  	$this->_siteDB->where('number', $number);
  	$result = $this->_siteDB->get('products');
  	$data = $result->row();
  	if($data){
  		//already existed. need to update the product information.
  		//if not specified file path. use old file path instead.
  		if($file_path == '' && isset($data->filepath) && $data->filepath !== ''){
  			$set['filepath'] = $data->filepath;
  		}
  		if($set['description'] == '')unset($set['description']);
  		if($set['summary'] == '') unset($set['summary']);
  		$this->_siteDB->update('products', $set, array('pid'=>$data->pid));
  		return $data->pid;
  	}

  	$this->_siteDB->insert('products', $set);
  	$site_pid = $this->_siteDB->lastInsertId();
  	return $site_pid;
  }

  public function communicateOrder($orderNumber, $stockItemList) {
    $message = $this->_client->order_communicate($orderNumber, $stockItemList);
    return $message;
  }
  
  public function getRefundAmount($orderNumber, $stockItemList) {
    $refundAmount = $this->_client->get_refund_amount($orderNumber, $stockItemList);
    return $refundAmount;
  }
  
  public function updateOrderAddress($orderNumber, $newData) {
      $this->_siteDB->update('orders', $newData, array('number' => $orderNumber));
      return (int)$this->_siteDB->affected();
  }
}