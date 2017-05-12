<?php
class Communication_Model extends Common_Model
{
  /**
   * @return Communication_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	public function syncToSite($productInfo, $productSiteDetails, $categoryInfo, $isSyncImg){
		//send information to site.
		$this->changeProductType($productSiteDetails->sid, $productInfo);
		$dbContactor = Site_API_Model::getInstance('Site_'.$productSiteDetails->codebase .'_Model', $productSiteDetails->site_name, $productSiteDetails->url);
		return $dbContactor->syncToSite($productInfo, $productSiteDetails, $categoryInfo, $isSyncImg);
	}
	
	public function updateProductOnshelfState($sn, $shelfState, $siteInfo){
		$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
		return $dbContactor->updateProduct(array('status'=>$shelfState), $sn);
	}
	
	public function updateOrder($onumber, $updateData, $siteInfo){
		$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
		return $dbContactor->updateOrder($updateData, $onumber);
	}

	public function updateOrderAddress($onumber, $newData, $siteInfo) {
	    $dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
	    return $dbContactor->updateOrderAddress($onumber, $newData);
	}
	
	public function communicateOrder($onumber, $stockItemList, $siteInfo) {
	    $dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
        return $dbContactor->communicateOrder($onumber, $stockItemList);
	}
	
	public function syncWholesaleUsers($startId, $siteInfo) {
		$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
		return $dbContactor->getWholesaleUser($startId);
	}
	
	public function getRefundAmount($onumber, $stockItemList, $siteInfo) {
        $dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
        return $dbContactor->getRefundAmount($onumber, $stockItemList);
	}
	
	public function syncTypes(){
		global $db;
		//get site info.
		$result = $db->get('sites');
		$sitesInfo = $result->all();
		
		foreach($sitesInfo as $k=>$siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$types = $dbContactor->getProductTypes();
			if(isset($types)){
				foreach ($types as $k2=> $type){
					$db->select('*');
					$db->where('type', $type->type);
					$result = $db->get('types');
					$data = $result->row();
					if(!$data){
						//insert into table.
						$set = array();
						$set['type'] = $type->type; 
						$db->insert('types', $set);
					}
				}
			}
		}
	}
	public function synProductField($fieldName){
		global $db;
		//get site info.
		$result = $db->get('sites');
		$sitesInfo = $result->all();
		foreach($sitesInfo as $k=>$siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$typeInstance = Type_Model::getInstance();
			$types = $typeInstance->getAllTypes();
			foreach($types as $key=>$type){
				$fields = $dbContactor->getProductFields($fieldName, $type->type);
				if(isset($fields)){
					foreach ($fields['options'] as $key2 => $field){
						$db->select('*');
						$db->where('value', $field);
						$result = $db->get($fieldName);
						$data = $result->row();
						if(!$data){
							$set = array();
							$set['value'] = $field;
							$db->insert($fieldName, $set);
						}
					}
				}
			}
		}
	}
	public function syncCategories(){
		global $db;
		//get site info.
		$result = $db->get('sites');
		$sitesInfo = $result->all();
		
		foreach($sitesInfo as $k=>$siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$categories = $dbContactor->getCategories();
			foreach ($categories as $k2=> $category){
				$db->select('*');
				$db->where('sid', $siteInfo->id);
				$db->where('site_cid', $category->site_cid);
				$result = $db->get('category');
				$data = $result->row();
				if(!$data){
					//insert into table.
					$set = array();
					$set['sid'] = $siteInfo->id; 
					$set['site_cid'] = $category->site_cid;
					$set['name'] = $category->name;
					$set['site_pcid'] = $category->site_pcid;
					$db->insert('category', $set);
				}
			}
		}
	}
	public function syncTypesBySite($sid){
		global $db;
		//get site info.
		$db->where('id', $sid);
		$result = $db->get('sites');
		$siteInfo = $result->row();

		$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
		$types = $dbContactor->getProductTypes();
		if(isset($types)){
			foreach ($types as $k2=> $type){
				$db->select('*');
				$db->where('type', $type->type);
				$result = $db->get('types');
				$data = $result->row();
				if(!$data){
					//insert into table.
					$set = array();
					$set['type'] = $type->type; 
					$db->insert('types', $set);
				}
			}
		}
	}

	public function syncProductsBySite($sid){
		global $db;
		//get site info.
		$db->where('id', $sid);
		$result = $db->get('sites');
		$siteInfo = $result->row();
		if($siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$remote_products = $dbContactor->syncProductsFromSite();
			if($remote_products){
				$productInstance = Product_Model::getInstance();
				$productSiteInstance = ProductSite_Model::getInstance();
				foreach($remote_products as $k=>$remote_product){
					$product = $productInstance->getProductBySn($remote_product->p_sn);
					if($product){
						//try to insert into product site.
						//   * return name, p_sn, created, price, listprice, site_cid, shelfstate.
						$categoryInstance = Category_Model::getInstance();
						$category = $categoryInstance->getCategoryBySiteCid($remote_product->site_cid);

						$productSiteInfo = $productSiteInstance->getProductSiteInfo($remote_product->p_sn, $siteInfo->id);
						if($productSiteInfo){
							$set = array();
							$set['cid'] = $category->id;
							$set['listprice'] = $remote_product->listprice;
							$set['price'] = $remote_product->price;
							$set['site_pname'] = $remote_product->name;
							$set['shelfstate'] = $remote_product->shelfstate;
							$set['site_purl'] = $remote_product->site_purl;
							$productSiteInstance->updateProductSiteInfo($set, $remote_product->p_sn, $siteInfo->id);
						}else{
							$productSiteInstance->insertProductSite($remote_product->p_sn, $siteInfo->id, $category->id, $remote_product->listprice, 
																	$remote_product->price, $remote_product->name, $remote_product->shelfstate, $remote_product->site_purl);
						}
					}
				}
			}
		}
	}
	
	
	public function syncProductFieldBySite($sid, $fieldName){
		global $db;
		//get site info.
		$db->where('id', $sid);
		$result = $db->get('sites');
		$siteInfo = $result->row();
		
		if($siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$typeInstance = Type_Model::getInstance();
			$types = $typeInstance->getAllTypes();
			foreach($types as $key=>$type){
				$fields = $dbContactor->getProductFields($fieldName, $type->type);
				if(isset($fields)){
					foreach ($fields['options'] as $key2 => $field){
						$db->select('*');
						$db->where('value', $field);
						$result = $db->get($fieldName);
						$data = $result->row();
						if(!$data){
							$set = array();
							$set['value'] = $field;
							$db->insert($fieldName, $set);
						}
					}
				}
			}
		}
	}
	public function syncCategoriesBySite($sid){
		global $db;
		//get site info.
		$db->where('id', $sid);
		$result = $db->get('sites');
		$siteInfo = $result->row();
		
		if($siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);
			$categories = $dbContactor->getCategories();
			foreach ($categories as $k2=> $category){
				$db->select('*');
				$db->where('sid', $siteInfo->id);
				$db->where('site_cid', $category->site_cid);
				$result = $db->get('category');
				$data = $result->row();
				if(!$data){
					//insert into table.
					$set = array();
					$set['sid'] = $siteInfo->id;
					$set['site_cid'] = $category->site_cid;
					$set['name'] = $category->name;
					$set['site_pcid'] = $category->site_pcid;
					$db->insert('category', $set);
				}
			}
		}
	}
	
	public function syncOrders($sid, $startTime, $endTime){
		//不需要同步订单的网站直接返回空
		if (!$this->needSyncOrders($sid)) {
			return array();
		}
		global $db;
		//get site info.
		$db->where('id', $sid);
		$result = $db->get('sites');
		$siteInfo = $result->row();
		$orderInstance = Order_Model::getInstance();
		
		if($siteInfo){
			$dbContactor = Site_API_Model::getInstance('Site_'.$siteInfo->codebase .'_Model', $siteInfo->name, $siteInfo->url);    
			$target_orders = $dbContactor->getOrders($startTime, $endTime);
            $orders_with_no_order_items = array();
			foreach($target_orders as $index=>$target_order){
				//if no such an order.
				$order = $orderInstance->getOrder($sid, $target_order->number);
				if($order){
					//if such order already exists	and: 1 not finished 2 updated < remote updated.				
					if($order->finished == 0 && $order->updated < $target_order->updated){
						$order_props = get_object_vars($target_order);
						if($order->status_shipping != '1' && $order_props['status_shipping'] == '1'){
							$order_props['finished'] = $order_props['updated'];
						}else{
							$order_props['finished'] = null;
						}
						if (isset($order_props['total_amount'])) {
						    unset($order_props['total_amount']);
						}
						if (isset($order_props['pay_amount'])) {
						    unset($order_props['pay_amount']);
						}
						$orderInstance->updateOrder($sid, $order->number, $order_props);
					}
				}else{
					//there is no such an order currently
					//@TODO assume that order items can not be deleted currently.
					$order_props = get_object_vars($target_order);
					if( $order_props['status_shipping'] == '1'){
						$order_props['finished'] = $order_props['updated'];
					}else{
						$order_props['finished'] = null;
					}
					//then insert order items.
					//$orderId = $orderInstance->insertOrder($sid, $target_order->number, $order_props);
					//$orders_with_no_order_items[$target_order->number] = $orderId;
					$orders_with_no_order_items[$target_order->number] = $order_props;
				}
			}
			// batch get order items
            $orderItems = $dbContactor->batchGetOrderItems(array_keys($orders_with_no_order_items));
            $orderTotalAmountList = array();
            $orderItemList = array();
            foreach($orderItems as $k => $v){
                if (!key_exists($v->order_number, $orderTotalAmountList)) {
                    $orderTotalAmountList[$v->order_number] = 0.0;
                }
                $orderTotalAmountList[$v->order_number] += $v->total_amount;
                $orderItemList[$v->order_number][] = $v;
            }
            foreach ($orders_with_no_order_items as $orderNumber => $orderProps) {
                if (!key_exists($orderNumber, $orderTotalAmountList)) {
                    continue;
                }
                $orderProps['total_amount'] = $orderTotalAmountList[$orderNumber];
                $orderProps['pay_amount'] = $orderProps['fee_amount'] + $orderProps['total_amount'];
                $orderId = $orderInstance->insertOrder($sid, $orderNumber, $orderProps);
                foreach ($orderItemList[$orderNumber] as $orderItem) {
                    $orderInstance->insertOrderItem($orderId, $orderItem->sn, $sid, $orderItem->order_number, $orderItem->qty, $orderItem->total_amount,$orderItem->image_source, $orderItem->data);
                }
            }
		}
	}
	
	public function needSyncOrders($sid) {
		return !in_array($sid, array('18'));
	}
	
	public function changeProductType($sid, $productInfo) {
		if (in_array($sid, array('18'))) {
			$productInfo->type = "lingerie";
		}
	}
}
