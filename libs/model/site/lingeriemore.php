<?php
class Site_LingerieMore_Model extends Site_API_Model
{
	private $_siteDB;
	private $_siteName;
	private $_siteUrl;
	private $_client;
	//private $_siteImgDir;
	
   /**
   * @return Site_LingerieMore_Model
   */
	public static function getInstance($siteName, $siteUrl)
	{
		return parent::getInstance(__CLASS__, $siteName, $siteUrl);
	}
	public function __construct($siteName, $siteUrl)
	{
		$this->codebase = 'LingerieMore';
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
	//$this->_client = new PHPRPC_Client($siteUrl.'/apiindex.php');
  }
  public function getCategories(){
  	$this->_siteDB->select('id as site_cid, father as site_pcid, name');
  	$result = $this->_siteDB->get('productclass');
  	//now we input the category info into the console db.
  	return $result->all();
  }
  
  /*
  protected function getTypeId($type){
  	$type_name = '内衣one size属性模板';
  	if($type == 'corset'){
  		$type_name = '内衣corset属性模板（S,M,L,XL,XXL)';
  	}
  	$this->_siteDB->select('id');
  	$this->_siteDB->where('name', $type_name);
  	$result = $this->_siteDB->get('propertyclass');
  	$type_id = $result->column();
  	return $type_id;
  }
  */
  
  public function getProduct($sn) {
      $this->_siteDB->select('product.*');
      $this->_siteDB->where('itemno', $sn);
      $result = $this->_siteDB->get('product');
      return $result->row();
  }
  
  public function getProductFields($field_name, $type){
  	//内衣one size属性模板.内衣corset属性模板
  	/*
  	$field_name = substr($field_name, 0, strlen($field_name) - 1);
  	$type_id = $this->getTypeId($type);
  	
  	$this->_siteDB->select('name, value');
  	$this->_siteDB->where('classid', $type_id);
  	$result = $this->_siteDB->get('property');
  	*/
  	return null;
  }
  public function getProductTypes(){
  	return null;
  }
  
  /**
   * (non-PHPdoc)
   * @see libs/model/site/Site_API_Model::syncProductsFromSite()
   * return name, p_sn, created, price, listprice, site_cid, shelfstate.
   */
  public function syncProductsFromSite($startTimestamp = null){
  	$sql = 'select id, name, itemno as p_sn, UNIX_TIMESTAMP(addtime) as created,'
  			.' price1 as price, price2 as listprice,'
  			.' classid as site_cid, SIGN(state) as shelfstate from product';
  	if(isset($startTimestamp)){
  		$sql .= ' where addtime > ' . date( 'Y-m-d H:i:s', $startTimestamp);
  	}
  	$result = $this->_siteDB->query($sql);
  	$data =  $result->all();
  	foreach ($data as $k=>$v){
  		$v->site_purl = str_replace(' ', '-', $v->name).'-p'.$v->id .'.html';
  	}
  	return $data;
  }
  
  public function syncToSite($productInfo, $product_siteInfo, $categoryInfo, $isSyncImg = true){
    // step1 sync base infomation
    $returnCode = -1;
    $product = $this->getProduct($productInfo->sn);
    $pid = 0;
    if (!empty($product)) {
        $sn = $productInfo->sn;
        $name = trim($product_siteInfo->site_pname);
        $status = $product_siteInfo->shelfstate;
        if($categoryInfo === false){
            $tid = '0';
        } else{
            $tid = $categoryInfo->site_cid;
        }
        $price = $product_siteInfo->price;
        $listprice = $product_siteInfo->listprice;
        $wt = $productInfo->wt;
        $attributes = $productInfo->attributes;
        $templateType = 48;
        /*
        if (strcasecmp($attributes['size'], 'One size') != 0) {
            $templateType = 48;
        }*/
        $updateData = array(
            "name" => $name,
            "classid" => $tid,
            "cvalue" =>trim($attributes['size'], ' \t\n\r\0\x0B,'), /* . "卐" . $attributes['color'],*/
            "price1" => $price, 
            "price2" => $listprice,
            "weight" => $wt,
            "itemno" => $sn,
            "propertyid" => $templateType);
        $pid = $product->id;
        $this->_siteDB->update('product', $updateData, array('id'=>$pid));
        $returnCode = $this->_siteDB->affected() > 0 ? 1 : -1;
    } else {
        $pid = $this->insertProduct($productInfo, $product_siteInfo, $categoryInfo);
        if ($pid <= 0) {
            return -1;
        }
    }
    // step2 sync image infomation
    if($isSyncImg == 'true'){
        $files = glob('files/'.$product_siteInfo->sn ."/*.*");
        $remoteDir = date("Y-m-d-H", time());
        $response = $this->uploadImage(null, 'files/'.$product_siteInfo->sn, $product_siteInfo->url . '/uploadimage.php', $remoteDir);
        log::save('DEBUG', 'image upload response', $response);
        if($response == 'success' || strlen($response) < 10){
            $first = true;
            // first delete all the old otherimage
            $sql = sprintf("delete from otherimage where pid = %d", $pid);
            $this->_siteDB->exec($sql);
            
            foreach($files as $k=>$filepath){
                $filename = basename($filepath);
                if ($first) {
                    $updateData = array();
                    $updateData['pic'] = $remoteDir . "/" . $filename;
                    $sql = sprintf("select id from product where pic = '%s' and id = %d", $updateData['pic'], $pid);
                    $result = $this->_siteDB->query($sql);
                    if (!$result->row()) {
                        $this->_siteDB->update('product', $updateData, array('id'=>$pid));
                        $returnCode = $this->_siteDB->affected() > 0 ? 1 : -1;
                    } else {
                        $returnCode = 1;
                    }
                    $first = false;
                } else {
                    // add other image
                    $insertData = array();
                    $insertData['pic'] = $remoteDir . "/" . $filename;
                    $insertData['pid'] = $pid;
                    /*
                    $sql = sprintf("select id from otherimage where pic = '%s' and pid = %d", $insertData['pic'], $insertData['pid']);
                    $result = $this->_siteDB->query($sql);
                    if (!$result->row()) {
                    */
                    $this->_siteDB->insert('otherimage', $insertData);
                    $imageId = $this->_siteDB->lastInsertId();
                    if ($imageId <= 0) {
                    	$returnCode = -1;
                    } else {
                        $updateData = array();
                        $updateData['sort'] = $imageId;
                        $this->_siteDB->update('otherimage', $updateData, array('id' => $imageId));
                        $returnCode = $this->_siteDB->affected() > 0 ? 1 : -1;
                    }
                }
                if ($returnCode == -1) {
                    break;
                }
            }
        }else{
            $returnCode = 0;
        }
    }else{
        $returnCode = 2;
    }
  	return $returnCode;
  }
  
  public function uploadImage($site_pid, $fileDir, $target_url, $targetDir){
    $files = glob($fileDir ."/*.*");
    $post = array();
    foreach($files as $k=>$filepath){
        $filename = basename($filepath);
        $post[$filename] = '@'.realpath($filepath);
    }
    $post['dir'] = $targetDir;
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
  
  public function insertProduct($productInfo, $product_siteInfo, $categoryInfo){
  	//do nothing.
    $sn = $productInfo->sn;
    $name = trim($product_siteInfo->site_pname);
    $status = $product_siteInfo->shelfstate;
    if($categoryInfo === false){
        $tid = '0';
    } else{
        $tid = $categoryInfo->site_cid;
    }
    $price = $product_siteInfo->price;
    $listprice = $product_siteInfo->listprice;
    $wt = $productInfo->wt;
    $attributes = $productInfo->attributes;
    
    return $this->insertProductRow($sn, $name, $tid, $price, $listprice, $wt, $status, $attributes); 
  }
  
  private function insertProductRow($sn, $name, $tid, $price, $listprice, $wt, $status, $attributes){
      $colorAttr = $attributes['color'];
      $sizeAttr = $attributes['size'];
      $templateType = 48;
      /*
      if (strcasecmp($sizeAttr, 'One size') != 0) {
          $templateType = 48;
      }*/
      $sort = 0;
      if (strpos($sn, 'MD_') === 0) {
      	$sort = rand(8000, 10000);
      }
      $set = array(
        "name" => $name,
        "classid" => $tid,   
        "brandid" => 5, 
        "ckey" => "Sizes",
        "cvalue" => $sizeAttr, /* . "卐" . $colorAttr,*/
        "ctype" =>  "4",  
        "cprice" => "卐",
        "pprice" => "卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐",
        "pnum" => "卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐",
        "premark" => "卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐卐",
        "price1" => $price, 
        "price2" => $listprice,
        "weight" => $wt,
      	"sort" => $sort, 
        "itemno" => $sn,
        "propertyid" => $templateType,
        "addtime" => date('Y-m-d H:i:s', time()), 
      );
      $this->_siteDB->insert('product', $set);
      $pid = $this->_siteDB->lastInsertId();
      if ($pid <= 0) {
          return -1;
      }
      $updateData = array();
      $updateData['state'] = 50 * $pid * $status;
      $updateData['sort'] = $pid;
      $this->_siteDB->update('product', $updateData, array('itemno'=>$sn));
      return $this->_siteDB->affected() > 0 ? $pid : -1;
  }
  
  //目前只用来上下架
  public function updateProduct($updateData, $sn){
  	$realData = array();

  	foreach($updateData as $k=>$v){
  		//currently only support change shelf state option.
  		if($k == 'status'){
  			$realData['state'] = $v;
  		}
  	}
  	if(isset($realData['state'])){
	  	$this->_siteDB->where('itemno', $sn);
	  	$result = $this->_siteDB->get('product');
	  	$product = $result->row();
	  	//for shelf state update.
	  	$oldState = intval($product->state);
	  	if(($realData['state'] == '1' && $oldState < 0)
	  	 ||($realData['state'] == '0' && $oldState > 0)){
	  		$newState = -$oldState;
	  		$realData['state'] = strval($newState);
	  	}else{
	  		$realData['state'] = strval($oldState);
	  	}
  	}
  	if(count($realData) > 0){
  		$this->_siteDB->update('product', $realData, array('itemno'=>$sn));
  		return $this->_siteDB->affected() > 0;
  	}
  }
  
  /**
  *  Currently Not Supported.
  */  
    public function updateOrder($updateData, $orderNumber){
        if (empty($orderNumber)) {
            return "Fail";
        }
        $set = array();
        if (isset($updateData['status']) && $updateData['status'] == 2) {
            $set['state'] = 4;
        }
        if (isset($updateData['status_shipping']) && $updateData['status_shipping'] == 1) {
            $set['shippingstate'] = 4;
        }
        if (!empty($updateData['data'])) {
            $data = unserialize($updateData['data']);
            if (!empty($data['shipping_no']) && !empty($updateData['delivery_email'])) {
                $set['shipno'] = $data['shipping_no'];
                $shippingMethod = $updateData['actual_shipping_method'];
                require_once LIBPATH . '/mail.php';
                $mailInstance = Mail_Model::getInstance();
                $content = $this->generateShippingnoEmail($updateData['username'], $updateData['itemno'], 'www.lingeriemore.com', $set['shipno'], $this->getOrderTrackingWebsite($data['shipping_no'], $shippingMethod));
                if (!$mailInstance->sendMail($updateData['delivery_email'], 'Tracking number for order ' . $updateData['itemno'], $content, true, '', 'lingeriemore.com')) {
                    return "Fail";   
                }
            }
        }
        if (empty($set)) {
            return "Fail";
        }
        $this->_siteDB->update('order', $set, array('itemno' => $orderNumber));
        if ($this->_siteDB->affected() > 0) {
            return "Success";
        } else {
            return "Fail";
        }
  }
  
   private function generateShippingnoEmail($username, $itemno, $httpdomain, $shipno, $shippingwebsite) {
       $content = '<div style="border-bottom: #9edcf4 1px solid; border-left: #9edcf4 1px solid; padding-bottom: 3px; background-color: #c7ecff; padding-left: 3px; width: 700px; padding-right: 3px; border-top: #9edcf4 1px solid; border-right: #9edcf4 1px solid; padding-top: 3px">
                    <div style="border-bottom: #88bedc 1px solid; border-left: #88bedc 1px solid; padding-bottom: 3px; background-color: #ffffff; padding-left: 3px; padding-right: 3px; border-top: #88bedc 1px solid; border-right: #88bedc 1px solid; padding-top: 3px">
                    <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                <div style="border-bottom: #5ecbef 1px dotted; border-left: #5ecbef 1px dotted; padding-bottom: 4px; background-color: #f3fcff; padding-left: 4px; padding-right: 4px; border-top: #5ecbef 1px dotted; border-right: #5ecbef 1px dotted; padding-top: 4px">Dear <strong>{{$order.username}}</strong><br />
                                We have sent out your order <span style="color: #f00"><strong>{{$order.itemno}}</strong></span> .<br />
                                If your want to see your order information , please <a target="_blank" href="{{$httpdomain}}/profile.php?action=search_order&amp;itemno={{$order.itemno}}"><span style="color: #f00">Click Here</span></a> .<br />
                                If you have any questions, please feel free to contact us . <br />
                                <br />
                                The Tracking number:<span style="color: #f00"><strong><br />
                                <a href="{{$order.shippingwebsite}}">{{$order.shipno}}</a></strong></span><br />
                                <br />
                                Safety Notice: We make every effort to ensure that all of our emails are subject to virus check before delivery, and pursuant to our policy, any &quot;.exe&quot; files or files containing any form of potential risks are not allowed to be sent out. Please do not attempt to open any attachments containing these file extensions without prior confirmation with the sender.</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    </div>';
       $content = str_replace('{{$order.username}}', $username, $content);
       $content = str_replace('{{$order.itemno}}', $itemno, $content);
       $content = str_replace('{{$order.shippingwebsite}}', $shippingwebsite, $content);
       $content = str_replace('{{$order.shipno}}', $shipno, $content);
       $content = str_replace('{{$httpdomain}}', $httpdomain, $content);
       return $content;
   }
  /**
   * (non-PHPdoc)
   * @see libs/model/site/Site_API_Model::getOrders()
   * 
   * order 对应关系
   * -----------------------------------------------------------------------------------
   * oid
   * sid
   * number  -> itemno
   * status  -> 3(处理中), 0(未处理）,4(已完成）
   * outinventory
   * status_payment -> 5(已付款），0-4（未付款）paymentstate
   * status_shipping -> 4（已运送），0-3（未配送） shippingstate
   * payment_method  ->westernunion, PAYPAL, T/T.
   * shipping_method ->EMS, DHL, UPS,TNT,KCS, Fedex, Hongkong Post/China Post
   * total_amount ->每个产品的价格加起来
   * fee_amount ->每个sub的价格加起来
   * pay_amount -> total_amount + fee_amount (需要考虑换算）
   * remark  -> content
   * delivery_first_name -> address[0]
   * delivery_last_name -> address[8]
   * delivery_email -> address[7]
   * delivery_phone -> address[6]
   * delivery_mobile -> address[6]
   * delivery_country -> address[5]
   * delivery_province -> address[4]
   * delivery_city -> address[3]
   * delivery_address -> address[1]
   * delivery_postcode -> address[2]
   * delivery_time ->   空
   * data -> shipno 改格式
   * created -> addtime
   * updated -> 没有
   * finished -> 没有
   * -----------------------------------------------------------------------------------
   */
  public function getOrders($startTime, $endTime){
  	/*
    $result = $this->_siteDB->query('select `order`.*, sum(orderproduct.pprice) as total_amount, order_update_trigger.updated as updated, `order`.sub1 +  `order` .sub2 + `order`.sub3 +  `order` .sub4 as fee_amount from `order`'
  				.' join orderproduct on `order`.id = orderproduct.orderid'
  				.' left join order_update_trigger on `order`.itemno = order_update_trigger.itemno'
  				.' where (addtime >= "'.date( 'Y-m-d H:i:s', $startTime).'" and addtime <= "'.date( 'Y-m-d H:i:s', $endTime + 3600*24) . '") '
  				.' or (updated >= "'.strval($startTime).'" and updated <= "'.strval($endTime + 3600*24). '")'
  				.' GROUP BY `order`.id order by `order`.addtime DESC');
  	$orders = $result->all();
  	
  	$convertedOrders = array();
  	foreach($orders as $index=>$order){
	  	$convertedOrder = new stdClass();
	  	$convertedOrder->number = $order->itemno;

	  	$orderStatusInt = intval($order->state);
	  	if($orderStatusInt == 4){
	  		$convertedOrder->status = 2;
	  	}else if($orderStatusInt == 3){
	  		$convertedOrder->status = 1;
	  	}else{
	  		$convertedOrder->status = 0;
	  	}
	  	$convertedOrder->status_payment = intval($order->paymentstate) < 5 ? '0' : '1'; 
	  	$convertedOrder->status_shipping = intval($order->shippingstate) < 4 ? '0' : '1';
	  	$convertedOrder->shipping_method = $this->getTranslatedString($order->express, 'shipping_method');
	  	$convertedOrder->payment_method = $this->getTranslatedString($order->payment, 'payment_method');
	  	$convertedOrder->fee_amount = $order->fee_amount;
	  	$convertedOrder->total_amount = $order->total_amount;
	  	$convertedOrder->pay_amount = $convertedOrder->fee_amount + $convertedOrder->total_amount;
	  	$convertedOrder->remark = $order->content;
	  	$addresses = explode('卐', $order->address);
		$convertedOrder->delivery_first_name = $addresses[0];
		$convertedOrder->delivery_last_name = $addresses[8];
		$convertedOrder->delivery_email = $addresses[7];
		$convertedOrder->delivery_phone = $addresses[6];
		$convertedOrder->delivery_mobile = $addresses[6];
		$convertedOrder->delivery_country = $addresses[5];
		$convertedOrder->delivery_province = $addresses[4];
		$convertedOrder->delivery_city = $addresses[3];
		$convertedOrder->delivery_address = $addresses[1];
		$convertedOrder->delivery_postcode = $addresses[2];
		$convertedOrder->delivery_time = '';
		if(isset($order->shipno)){
			$convertedOrder->data = serialize(array('shipping_no'=> $order->shipno));
		}
		$convertedOrder->created = strtotime($order->addtime);
		$convertedOrder->updated = isset($order->updated)? $order->updated: $convertedOrder->created;
		
		$convertedOrders[] = $convertedOrder;
  	}
  	return $convertedOrders;*/
      $addedOrderList = $this->getOrdersByAddedTime($startTime, $endTime);
      $updatedOrderList = $this->getOrdersByUpdateTime($startTime, $endTime);
      return array_merge($addedOrderList, $updatedOrderList);
  }
  
  public function getOrdersByAddedTime($startTime, $endTime){
      $result = $this->_siteDB->query('select `order`.*, `order`.sub1 +  `order` .sub2 + `order`.sub3 +  `order` .sub4 as fee_amount from `order`'
                .' where (addtime >= "'.date( 'Y-m-d H:i:s', $startTime).'" and addtime <= "'.date( 'Y-m-d H:i:s', $endTime + 3600*24) . '") '
                .' order by `order`.addtime DESC');
      return $this->convertToOrderList($result);
  }
  
  public function getOrdersByUpdateTime($startTime, $endTime){
      $result = $this->_siteDB->query('select itemno, updated from order_update_trigger '
                                      .' where updated >= "'.strval($startTime).'" and updated <= "'.strval($endTime + 3600*24). '"');
      $orders = $result->all();
      $updatedOrderList = array();
      foreach ($orders as $order) {
          $updatedOrderObj = new stdClass();
          $updatedOrderObj->updated = $order->updated;
          $updatedOrderObj->itemno = $order->itemno;
          $updatedOrderList[$order->itemno] = $updatedOrderObj;
      }
      if (empty($updatedOrderList)) {
          return array();
      }
      $this->_siteDB->select('`order`.*, `order`.sub1 +  `order` .sub2 + `order`.sub3 +  `order` .sub4 as fee_amount');
      $this->_siteDB->from('`order`');
      $this->_siteDB->where('itemno IN', array_keys($updatedOrderList));
      $result = $this->_siteDB->get();
      $orderList = $this->convertToOrderList($result);
      foreach ($orderList as $order) {
          if (key_exists($order->number, $updatedOrderList)) {
              $order->updated = $updatedOrderList[$order->number]->updated;
          }
      }
      return $orderList;
  }
  private function convertToOrderList($result) {
      $orders = $result->all();
      $convertedOrders = array();
      foreach($orders as $index=>$order){
        $convertedOrder = new stdClass();
        $convertedOrder->number = $order->itemno;

        $orderStatusInt = intval($order->state);
        if($orderStatusInt == 4){
            $convertedOrder->status = 2;
        }else if($orderStatusInt == 3){
            $convertedOrder->status = 1;
        }else{
            $convertedOrder->status = 0;
        }
        $convertedOrder->status_payment = intval($order->paymentstate) < 5 ? '0' : '1'; 
        $convertedOrder->status_shipping = intval($order->shippingstate) < 4 ? '0' : '1';
        $convertedOrder->shipping_method = $this->getTranslatedString($order->express, 'shipping_method');
        $convertedOrder->payment_method = $this->getTranslatedString($order->payment, 'payment_method');
        $convertedOrder->fee_amount = $order->fee_amount;
        //$convertedOrder->total_amount = 0;
        //$convertedOrder->pay_amount = $convertedOrder->fee_amount + $convertedOrder->total_amount;
        $convertedOrder->remark = $order->content;
        $addresses = explode('卐', $order->address);
        $convertedOrder->delivery_first_name = $addresses[0];
        $convertedOrder->delivery_last_name = $addresses[8];
        $convertedOrder->delivery_email = $addresses[7];
        $convertedOrder->delivery_phone = $addresses[6];
        $convertedOrder->delivery_mobile = $addresses[6];
        $convertedOrder->delivery_country = $addresses[5];
        $convertedOrder->delivery_province = $addresses[4];
        $convertedOrder->delivery_city = $addresses[3];
        $convertedOrder->delivery_address = preg_replace('/\r|\n/', ' ', $addresses[1]);
        $convertedOrder->delivery_postcode = $addresses[2];
        $convertedOrder->delivery_time = '';
        $convertedOrder->currency = $order->coin;
        $convertedOrder->exchange_rate = round(1 / $order->rate, 2);
        if(isset($order->shipno)){
            $convertedOrder->data = serialize(array('shipping_no'=> $order->shipno));
        }
        $convertedOrder->created = strtotime($order->addtime);
        $convertedOrder->updated = isset($order->updated)? $order->updated: $convertedOrder->created;
        
        $convertedOrders[$order->id] = $convertedOrder;
    }
    return $convertedOrders;
  }
  
  private function getTranslatedString($original, $strType){
  	if($strType == 'shipping_method'){
  		if($original == 'DHL') return 'ups';
  		if($original == 'EMS') return 'ems';
  		if($original == 'UPS') return 'ups';
  		if($original == 'Hongkong Post') return 'hongkongpost';
  		if($original == 'Hongkong Post/China Post') return 'hongkongpost';
  		return $original;
  	}
  	if($strType == 'payment_method'){
  		if($original == 'westernunion') return 'western';
  		if($original == 'PAYPAL') return 'paypal';
  		if($original == 'T/T') return 'wiretransfer';
  	}
  }
  
  /**
   * (non-PHPdoc)
   * @see libs/model/site/Site_API_Model::getOrderItems()
   * orderitems对应关系：
   * -----------------------------------------------------------------------------------
   * p_sn ->　pitemno
   * o_number ->　orderid->itemno
   * qty->　pnum
   * total_amount->　pnum * pprice
   * image_source->     'http://www.lingeriemore.com/image.php?pic='.ppic( / to %2F).'&style=-1&folder=uploadImage%2F'
   * data-> pstyle 
   * a:2:{s:5:"Color";s:8:"as shown";s:4:"Size";s:2:"XL";}   
   * -> Size:one size<br />Color:As shown | plus size lingerie:XXL | Sizes:L
   * -----------------------------------------------------------------------------------
   */

  public function getOrderItems($orderNumber){
    $result = $this->_siteDB->query('select orderproduct.*, `order`.itemno from orderproduct'
  									.' join `order` on `order`.id = orderproduct.orderid'
  									.' where `order`.itemno = "'.$orderNumber.'"');
  	$orderItems = $result->all();
  	$newOrderItems = array();
  	foreach($orderItems as $k=>$v){
  		//$v->sn, $sid, $target_order->number, $v->qty, $v->total_amount,$v->image_source, $v->data
  		//orders_items.sn, orders_items.number, orders_items.qty,orders_items.total_amount, orders_items.data, products.filepath as image_source
  		$newOrderItem = new stdClass();
  		$newOrderItem->sn = $v->pitemno;
  		$newOrderItem->number = $v->pitemno;
  		$newOrderItem->qty = $v->pnum;
  		$newOrderItem->wt = $v->pweight;
  		$newOrderItem->price = $v->pprice;
  		$newOrderItem->name = $v->pname;
  		$newOrderItem->total_amount = strval(intval($v->pnum) * floatval($v->pprice));
  		$newOrderItem->url = $this->_siteUrl . '/' . str_replace(' ', '-', trim($v->pname)) . '-p' . $v->pid . '.html';
  		$newOrderItem->image_source = $this->_siteUrl . '/image.php?pic='. str_replace('/', '%2F', $v->ppic).'&style=3&folder=uploadImage%2F';
  		$newOrderItem->data = $this->convertOrderItemPropertyData($v->pstyle);
  
  		$newOrderItems[] = $newOrderItem;
  	}
  	return $newOrderItems;
    //return array();
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
          $this->_siteDB->select('orderproduct.*, `order`.itemno');
          $this->_siteDB->from('orderproduct');
          $this->_siteDB->join('`order`', '`order`.id = orderproduct.orderid');
          $this->_siteDB->where('`order`.itemno IN', $orderList);
          $result = $this->_siteDB->get();
          $orderItems = $result->all();
          $newOrderItems = array();
          foreach($orderItems as $k=>$v){
              $newOrderItem = new stdClass();
              $newOrderItem->order_number = $v->itemno;
              $newOrderItem->sn = $v->pitemno;
              $newOrderItem->number = $v->pitemno;
              $newOrderItem->qty = $v->pnum;
              $newOrderItem->total_amount = strval(intval($v->pnum) * floatval($v->pprice));
              $newOrderItem->image_source = $this->_siteUrl . '/image.php?pic='. str_replace('/', '%2F', $v->ppic).'&style=3&folder=uploadImage%2F';
              $newOrderItem->data = $this->convertOrderItemPropertyData($v->pstyle);
              $orderItemList[] = $newOrderItem;
          }
      }
     
      return $orderItemList;
  }

  private function getOrder($orderNumber) {
      $this->_siteDB->select('`order`.*');
      $this->_siteDB->where('itemno', $orderNumber);
      $result = $this->_siteDB->get('`order`');
      if ($result) {
          return $result->row();
      }
      return $result;
  }
  
  private function convertOrderItemPropertyData($oldData){
  	$propsArray = explode('<br />', $oldData);
  	$newData = array();
  	$newData['Color'] = 'As Shown';
  	foreach($propsArray as $v){
  		$prop = explode(':', $v);
  		if($prop[0] == 'Color'){
  			$newData['Color'] = $prop[1];
  		}else if($prop[0] == 'Size' || $prop[0] == 'plus size lingerie' || $prop[0] == 'Sizes'){
  			$newData['Size'] = str_replace('2XL', 'XXL', $prop[1]);
  		}
  	}
  	return serialize($newData);
  }
  
  private function getShippingMethodInfo($shippingMethodName, $shippingCountry) {
      $sql = sprintf('select * from express where express.name = "%s" and (country in (select id from deliveryarea where deliveryarea.id in (select areaid from areacountry, country where areacountry.countryid = country.id and country.name = "%s")) or country = 0)', $shippingMethodName, $shippingCountry);
      $result = $this->_siteDB->query($sql);
      if ($result) {
          return $result->row();
      }
      return $result;
  }
  
  public function communicateOrder($orderNumber, $stockItemList) {
      $stockoutItemsInfoList = array();
      $refundAmount = $this->getRefundAmount($orderNumber, $stockItemList, $orderInfo, $stockoutItemsInfoList);
      if (isset($refundAmount->product)) {
        $ret = $this->send_refund_email($orderInfo, $stockoutItemsInfoList, $refundAmount);
        return $ret;
      } else {
          return $refundAmount;
      }
  }
  
  public function getWholesaleUser($startId) {
  	$sql = sprintf("select * from wholesale_user where id > %d", $startId);
  	$result = $this->_siteDB->query($sql);

  	if ($result) {
  		return $result->all();
  	}
  	return false;
  }
  
    public function getRefundAmount($orderNumber, $stockItemList, &$orderInfo = null, &$stockoutItemsInfoList = array()) {
      $stockItemsInfoList = array();
      $refundAmount = new stdClass;
      $refundAmount->product = 0.0;
      $refundAmount->shipping = 0.0;
      
      // get order
      $orderInfo = $this->getOrder($orderNumber);
      if (!$orderInfo) {
          return "error: failed to get order details";
      }
      // get orderitems
      $orderItems = $this->getOrderItems($orderNumber);
      $orderInfo->items = $orderItems;
      foreach ($orderItems as $orderItem) {
          $orderItem->data = unserialize($orderItem->data);
      	  $attrArray = array();
          
          foreach($orderItem->data as $attr => $value) {
          	if ($value == '2XL') {
        			$value = 'XXL';
        		}
        		$attrArray[strtolower($attr)] = strtolower($value);
          }
          $data = json_encode($attrArray);
          if (key_exists($orderItem->sn, $stockItemList) && key_exists($data, $stockItemList[$orderItem->sn])) {
          	  $qty = $stockItemList[$orderItem->sn][$data];
	          if ($qty > 0 && $qty < $orderItem->qty) {
	          	  $orderItem->lack_qty = $orderItem->qty - $qty;
	              $refundAmount->product += $orderItem->price * ($orderItem->lack_qty);
	              $stockoutItemsInfoList[] = $orderItem;
              }
          } else {
              $orderItem->lack_qty = $orderItem->qty;
              $refundAmount->product += $orderItem->price * ($orderItem->lack_qty);
              $stockoutItemsInfoList[] = $orderItem;
          }
      }
      $address = explode('卐', $orderInfo->address);
      $orderInfo->delivery_name = $address[0];
      $shippingCountry = $address[5];
      return $refundAmount;
    }
    
    private function getShippingAmount($shippingInfo, $totalWeight)
    {
        if($totalWeight <= $shippingInfo->firstweight) {
            return $shippingInfo->price1 ;
        } else {
            return  $shippingInfo->price2 * ceil(($totalWeight-$shippingInfo->firstweight)*2) + $shippingInfo->price1;
        }
        return 0;
    }
      
    private function send_refund_email($orderInfo, $stockoutItemList, $refundAmount) {
        $siteName = 'lingeriemore';
        $siteUrl = 'http://www.lingeriemore.com/';
        
        $stockoutItemInfo = $this->generate_order_items_table($stockoutItemList, $orderInfo->coin, true);
        $orderItemInfo = $this->generate_order_items_table($orderInfo->items, $orderInfo->coin, false);
        //$shippingRefundAmount = $orderInfo->coin . $refundAmount->shipping;
        $productRefundAmount = $orderInfo->coin . $refundAmount->product;
        //$totalRefundAmount = $orderInfo->coin . strval(($refundAmount->product + $refundAmount->shipping));
        $refundEmailTitleTemplate = "Out of stock items of order: $orderInfo->itemno";
        $refundEmailContentTemplate = <<< EOT
            <div style="width:800px">
            Dear $orderInfo->delivery_name:<br/>
            <br/>
            Thank you very much for your order on <a href="$siteUrl" >$siteName</a>.We are working hard to offer the best lingerie products to you.
            But as you know, this is a busy season for sales, and we regret to inform you that some of items in your order are out of stock.<br/>
            <br/>
            These items are listed below:<br/>
            $stockoutItemInfo
            We have two recommendations on how to resolve this issue:<br/><br/>
            1. Fulfill and ship the items we have in stock once we receive your confirmation. Of course, we would refund the fees for 
               the out of stock items back to your account with related shipping fees.
               For your case, we would refund $productRefundAmount plus the extra shipping fees to you. Please refer to your PayPal account.<br/>
            2. You can cancel this order and we will refund the full amount to you.<br/>
            <br/>
            If we do not receive a response from you in two working days, we would:<br/>
             - Use Option 1 (fulfill and ship the rest items) by default.<br/>
             - Use Option 2 (cancel order) if more than 1/3 of the items are out of stock.<br/>
            <br/>
            We sincerely apologize that these items are out of stock, and are looking forward to receiving your response as soon as possible
            to let us know how to proceed. We are refreshing our stocks bi-weekly, and now building a larger inventory to meet your needs. Welcome to visit us again, your satisfaction is the
            greatest motivation to help us improve our service.<br/>
            <br/> 
            <div style="color:gray;font-size:small">
            Best Regards<br/>
            $siteName customer support team<br/>
            </div>
            <br/>
            ===========================  $orderInfo->itemno Details =================================<br/>
            $orderItemInfo
            </div>
EOT;
        require_once LIBPATH . '/mail.php';
        $mailInstance = Mail_Model::getInstance();
        if (!$mailInstance->sendMail($orderInfo->username, $refundEmailTitleTemplate, $refundEmailContentTemplate, true, '', 'lingeriemore.com')) {
            return "error: send email failed.";   
        }
        return "success";
    }

    private function generate_order_items_table($orderItemList, $coin, $isStockoutItemList = false) {
        $orderItemInfo = '';
        if (!empty($orderItemList)) {
            $orderItemInfo = '<table cellpadding="0" cellspacing="0" style="font-size:12px;">';
            $orderItemInfo .= '<th width="100">Image</th><th width="300">Desc</th><th width="100">Quantity<th width="100">Price</th><th width="100">Subtotal</th>';
            foreach ($orderItemList as $orderItem) {
                $itemDataInfo = '';
                if (isset($orderItem->data) && $orderItem->data != '') {
                    foreach ($orderItem->data as $k1 => $v1) {
                        $itemDataInfo .= "<br/><strong style='text-transform:capitalize;'>" . $k1 . ":</strong><span>&nbsp;</span>&nbsp;&nbsp;<span style='text-transform:capitalize;'>" . $v1 . "</span>";
                    }
                }
                $itemUrl = $orderItem->url;
                $itemImageUrl = $orderItem->image_source;
                $itemQty = $isStockoutItemList ? $orderItem->lack_qty : $orderItem->qty;
                $itemPrice = ($orderItem->price);
                $itemTotalAmount = $isStockoutItemList ? ($orderItem->price * $itemQty) : ($orderItem->total_amount);
                $itemInfo = <<< ITEM
                            <tr>
                                <td align="center" style="border-bottom: 1px solid #ddd;">
                                    <a href="$itemUrl"><img style="border: medium none;padding:5px 10px 5px 0;" src="$itemImageUrl"/></a>
                                </td>
                                <td align="left" style="border-bottom: 1px solid #dddddd;">
                                    <a href="$itemUrl">$orderItem->name;</a>
                                    $itemDataInfo
                                </td>
                                <td align="center" style="border-bottom: 1px solid #ccc;"><span>$itemQty</span></td>
                                <td align="center" style="border-bottom: 1px solid #ccc;"><span>$coin$itemPrice</span></td>
                                <td align="center" style="border-bottom: 1px solid #ccc;"><span>$coin$itemTotalAmount</span></td>
                                </tr>
ITEM;
                $orderItemInfo .= $itemInfo;
            }
            $orderItemInfo .= "</table><br/>";
        }
        return $orderItemInfo;
    }
    
    private function getOrderTrackingWebsite($shippingNo, $shippingMethod) {
        $shippingMethod = strtolower($shippingMethod);
        switch ($shippingMethod) {
            case "dhl":
                return "http://www.dhl.com/en/express/tracking.html?AWB=$shippingNo&brand=DHL";
            case "ups":
                return "http://wwwapps.ups.com/WebTracking/track?trackNums=$shippingNo&track.x=Track";
            case "epacket":
                return "https://tools.usps.com/go/TrackConfirmAction.action?tRef=fullpage&tLc=1&text28777=&tLabels=$shippingNo";
            case "中邮小包":
                return "http://intmail.11185.cn";
            case "tnt":
                return "http://www.tnt.com/webtracker/tracking.do?cons=$shippingNo&requesttype=GEN&searchType=CON";
            case "日本专线":
                return "http://www.zce-exp.com/home/jp/index.asp";
            case "南非专线":
                return "http://www.tollgroup.com/tollglobalexpressasia";
            case "dpd":
                return "http://www.dpd.co.uk";
            case "aramex":
                return "http://www.aramex.com";
            case "顺丰":
                return "http://www.sf-express.com/cn/sc";
            case "fedex":
                return "http://www.fedex.com/cn_english";
            case "ems":
                return "http://www.ems.com.cn/english.html";
            default:
                return "";
        }
    }
    
    public function updateOrderAddress($orderNumber, $newData) {
        $address = $newData['delivery_first_name'] . '卐'
            . $newData['delivery_address'] . '卐'
            . $newData['delivery_postcode'] . '卐'
            . $newData['delivery_city'] . '卐'
            . $newData['delivery_province'] . '卐'
            . $newData['delivery_country'] . '卐'
            . (empty($newData['delivery_mobile']) ? $newData['delivery_phone'] : $newData['delivery_mobile']) . '卐'
            . $newData['delivery_email'] . '卐'
            . $newData['delivery_last_name'] . '卐卐卐卐卐卐卐卐卐卐卐';
        
        $this->_siteDB->update('order', array('address' => $address), array('itemno' => $orderNumber));
        return (int)$this->_siteDB->affected();
    }
}