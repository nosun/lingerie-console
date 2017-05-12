<?php
class Product_Model extends Common_Model
{
	/**
	 * @return Product_Model
	 * Enter description here ...
	 */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function insertProduct($sn, $type, $wt, $suppliers, $attributes=null, $suppliers_sn=null){
		global $db;
	  	$product = array(
	  	'sn' => $sn,
	  	'type' => $type,
	  	'wt' => $wt,
	  	//'colors' => $colors,
	  	//'sizes' => $sizes,
	  	//'stock' => $stock,
	  	'suppliers' => $suppliers,
	  	);
	  	if (isset($suppliers_sn)) {
	  	    $product['suppliers_sn'] = $suppliers_sn;
	  	}
	  	$db->insert('product', $product);
	  	$pid = $db->lastInsertId();
	  	
	  	//first get attributes set of the product by product type
	  	if(isset($type)){
	  		$db->select('*');
	  		$db->where('type', $type);
	  		$result = $db->get('attribute');
	  		$data = $result->all();
	  		foreach ($data as $k=>$v){
	  			if(isset($attributes) && key_exists($v->name, $attributes)){
	  				$this->insertProductAttribute($sn, $v->name, $attributes[$v->name]);
	  			}
	  			else{
	  				$this->insertProductAttribute($sn, $v->name, '');
	  			}
	  		}
	  	}
	  	return $pid;
	  	
	}
	
	function insertProductAttribute($sn, $aname, $avalue){
		global $db;
		$attribute = array(
			'p_sn' => $sn,
			'aname' => $aname,
			'avalue' => $avalue
		);
		$db->insert('product_attribute', $attribute);
		$aid = $db->lastInsertId();
	}
	
	function updateProductBySn($sn, $set){
		global $db;
		
		//update the properties.
		if(isset($set['attributes'])){
			$attributes = $set['attributes'];
			foreach($attributes as $aName => $aValue){
				$db->update('product_attribute', array('avalue'=>$aValue), array('p_sn'=>$sn, 'aname'=>$aName));
			}
			unset($set['attributes']);
		}
		if(count($set) > 0){
			$db->update('product', $set, array('sn'=>$sn));
		}
		return (boolean)$db->affected();
	}
	
	function getProductSnById($id){
		global $db;
		$db->select('sn');
		$db->where('id', $id);
		$result = $db->get('product');
		$data = $result->one();
		return $data;
	}
	
	function updateProduct($pid, $set){
		global $db;
		//update the properties.
		if(isset($set['attributes'])){
			$sn = $this->getProductSnById($pid);
			if($sn){
				$attributes = $set['attributes'];
				$db->where('p_sn', $sn);
				$result = $db->get('product_attribute');
				$oldAttributes = $result->allWithKey('aname');
				foreach($attributes as $aName => $aValue){
					//maybe this attribute is not existed.
					if(!isset($oldAttributes[$aName])){
						$db->insert('product_attribute', array('p_sn'=>$sn, 'aname'=>$aName,'avalue'=>$aValue));
					}else{
						$db->update('product_attribute', array('avalue'=>$aValue), array('p_sn'=>$sn, 'aname'=>$aName));
					}
				}
				unset($set['attributes']);
			}
		}
		$db->update('product', $set, array('id'=>$pid));
		return (boolean)$db->affected();
	}
	
	function getProductBySn($sn){
		global $db;
		$db->where('sn', $sn);
		$result = $db->get('product');
		$data = $result->row();
		
		if($data){
			//get product attributes.
			$db->where('p_sn', $data->sn);
			$result2 = $db->get('product_attribute');
			$attributes = $result2->all();
			
			if($attributes){
				foreach ($attributes as $k2 => $v2){
					if(!isset($data->attributes)){
						$data->attributes = array();
					}
					$data->attributes[$v2->aname] = $v2->avalue; 
				}
			}
		}
		return $data;
	}
	
	function getProduct($order = null, $filter=null, $offset = null){
		global $db;
		if(isset($order)){
			$db->orderby($order);
		}
		//@TODO filter don't contains the attributes currently.
		if(isset($filter)){
			foreach ($filter as $key => $value) {
				$db->where($key, $value);
      		}
		}
		$result = $db->get('product', 1, $offset);
		$data = $result->row();
		if($data){
			//get product attributes.
			$db->where('p_sn', $data->sn);
			$result2 = $db->get('product_attribute');
			$attributes = $result2->all();
			if($attributes){
				foreach ($attributes as $k2 => $v2){
					if(!isset($data->attributes)){
						$data->attributes = array();
					}
					$data->attributes[$v2->aname] = $v2->avalue; 
				}
			}
		}
		return $data;
	}
	
	function getProducts($order = null, $limit = null, $filter=null, $offset = null){
		global $db;
		if(isset($order)){
			$db->orderby($order);
		}
		if(isset($filter)){
			foreach ($filter as $key => $value) {
				$db->where($key, $value);
      		}
		}
		$result = $db->get('product', $limit, $offset);
		$data = $result->all();
		if($data){
			foreach ($data as $k=>$v){
				$db->where('p_sn', $v->sn);
				$result2 = $db->get('product_attribute');
				$attributes = $result2->all();

				if($attributes){
					foreach ($attributes as $k2 => $v2){
						if(!isset($v->attributes)){
							$v->attributes = array();
						}
						$v->attributes[$v2->aname] = $v2->avalue; 
					}
				}
			}
		}
		return $data;
	}
	
	function getNextProductId(){
		global $db;
		return $db->nextId('product');
	}
	function getProductsCountByFilter($order = null, $filter=null){
		global $db;
		$db->select('count(*) as count');
		if(isset($order)){
			$db->orderby($order);
		}
		if(isset($filter)){
			foreach ($filter as $key => $value) {
				$db->where($key.' like ', '%'. $value .'%');
      		}
		}
		$result = $db->get('product');
		$data = $result->row();
		return $data->count;
	}
	
	function getProductsLike($order = null, $limit = null, $filter=null, $offset = null){
		global $db;
		if(isset($order)){
			$db->orderby($order);
		}
		if(isset($filter)){
			foreach ($filter as $key => $value) {
				$db->where($key.' like ', '%'. $value .'%');
      		}
		}
		$result = $db->get('product', $limit, $offset);
		$data = $result->all();
		
		if($data){
			foreach ($data as $k=>$v){
				$db->where('p_sn', $v->sn);
				$result2 = $db->get('product_attribute');
				$attributes = $result2->all();

				if($attributes){
					foreach ($attributes as $k2 => $v2){
						if(!isset($v->attributes)){
							$v->attributes = array();
						}
						$v->attributes[$v2->aname] = $v2->avalue; 
					}
				}
			}
		}
		
		return $data;
	}
	
	
	function getProductsCount(){
		global $db;
		$db->select('count(*) as count');
		$result = $db->get('product');
		$data = $result->row();
		return $data->count;
	}
	
	/**
	 * Given 2 array of properties, test whether they are equal.
	 * Enter description here ...
	 * @param unknown_type $props1
	 * @param unknown_type $props2
	 */
	function isPropertiesEqual($props1, $props2){
		$isSame = true;
		if($props1 == false && $props2 == false){
			return true;
		}
		if($props1 == false || $props2 == false){
			return false;
		}
  		foreach($props1 as $pName => $pValue){
  			if($pName{0} === strtoupper($pName{0})){
  				//$pName is uppercase.
  				if((!key_exists($pName, $props2) || strtolower($props2[$pName]) != strtolower($pValue))
  				 && (!key_exists(strtolower($pName), $props2) || strtolower($props2[strtolower($pName)]) != strtolower($pValue))){
  				//属性不相等
	  				$isSame = false;
	  				break;
  				 }
  			}else{
  				//$pName{0} is lower case.
  			  	if((!key_exists($pName, $props2) || strtolower($props2[$pName]) != strtolower($pValue))
  			  	 && (!key_exists(ucwords($pName), $props2) || strtolower($props2[ucwords($pName)]) != strtolower($pValue))){
  				//属性不相等
	  				$isSame = false;
	  				break;
  				}
  			}
  		}
  		//属性全都相等才能判定为相等。
  		return $isSame;
  	}

}