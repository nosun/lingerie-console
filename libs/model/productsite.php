<?php
class ProductSite_Model extends Common_Model
{
  /**
   * @return ProductSite_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}

	function insertProductSite($sn, $sid, $cid, $listprice, $price, $site_pname = '', $shelfstate = '1', $site_purl = null){
		global $db;
	  	$productSite = array(
	  	'p_sn' => $sn,
	  	'site_pname' => $site_pname,
	  	'sid' => $sid,
	  	'cid' => $cid,
	  	'shelfstate' => $shelfstate,
	  	'listprice' => $listprice,
	  	'price' => $price,
	  	'image_dir' => '',
	  	);
	  	
	  	if(isset($site_purl)){
	  		$productSite['site_purl'] = $site_purl;
	  	}else{
	  		$productSite['site_purl'] = $this->getProductPathAlias($sn, $sid, $site_pname, $cid);
	  	}
	  	
	  	$db->insert('product_site', $productSite, true);
	  	return $db->lastInsertId();
	}
	
	function addCategoriesForProductSite($categories, $sn, $sid){
		global $db;
		//$categoryData = serialize($categories);
		//$set['alt_cids'] = $categoryData;
		$filter = array();
		$filter['p_sn'] = $sn;
		$filter['sid'] = $sid;
		$db->where('p_sn', $sn);
		$db->where('sid', $sid);
		$result = $db->get('product_site');
		$productSiteInfo = $result->row();
		$alt_cids = $productSiteInfo->alt_cids;
		if($alt_cids){
			$newCategories = unserialize($alt_cids);
			$nextId = count($newCategories)+ 1;
		}else{
			$newCategories = array();
			$nextId = 1;
		}
		foreach($categories as $k=>$v){
			if(!in_array($v, $newCategories) && !($v == $productSiteInfo->cid)){
				$newCategories['v'.(string)$nextId] = $v;
				$nextId++;
			}
		}
		$set['alt_cids'] = serialize($newCategories);
		$db->update('product_site', $set, $filter);
		//return $db->affected();
		return $newCategories;
	}
	
	function getAltCategoriesFromProductSite($sn, $sid){
		$db->where('p_sn', $sn);
		$db->where('sid', $sid);
		$result = $db->get('product_site');
		$data = $result->row();
		if($data){
			return unserialize($data->alt_cids);
		}
		return array();
	}
	
	function getNextAltCategoryId($sn, $sid){
		$nextId = 0;
		$db->select('alt_cids');
		$db->where('p_sn', $sn);
		$db->where('sid', $sid);
		$result = $db->get('product_site');
		$data = $result->one();
		if($data){
			$other_categories = unserialize($data);
			foreach($other_categories as $k=>$v){
				$number = intval(substr($k, 1));
				if($nextId < $number){
					$nextId = $number;
				}
			}
		}
		$nextId += 1;
		return $nextId;
	}


	function updateProductSiteStatus($status, $sn, $sid){
		global $db;
		$productSiteInfo = $this->getProductSiteInfo($sn, $sid);
		//need also update product site path_alias.
		$site_purl = $this->getProductPathAlias($sn, $sid, $productSiteInfo->site_pname, $productSiteInfo->cid);

		$set = array();
		$set['is_sync'] = $status;
		$set['site_purl'] = $site_purl;
		$filter = array();
		$filter['p_sn'] = $sn;
		$filter['sid'] = $sid;
		
		$db->update('product_site', $set, $filter);
		return $db->affected();
	}
	
	
	function updateProductSiteUrl($sn, $sid){
		global $db;
		$productSiteInfo = $this->getProductSiteInfo($sn, $sid);
		//need also update product site path_alias.
		$site_purl = $this->getProductPathAlias($sn, $sid, $productSiteInfo->site_pname, $productSiteInfo->cid);
		$set = array();
		$set['site_purl'] = $site_purl;
		$filter = array();
		$filter['p_sn'] = $sn;
		$filter['sid'] = $sid;
		$db->update('product_site', $set, $filter);
		return $db->affected();
	}
	
	
	
	function updateProductSiteInfo($set, $sn, $sid){
		global $db;
		$filter = array();
		$filter['p_sn'] = $sn;
		$filter['sid'] = $sid;
		$db->update('product_site', $set, $filter);
		return $db->affected();
	}
	
	
	
	function getProductSiteInfo($sn, $sid){
		global $db;
		$db->where('p_sn', $sn);
		$db->where('sid', $sid);
		$result = $db->get('product_site');
		$data = $result->row();
		
		if($data->site_purl == '' && $data->is_sync == '1'){
			$data->site_purl = $this->getProductPathAlias($sn, $sid, $data->site_pname, $data->cid);
		}
		
		return $data;
	}
	
	function getProductSiteDetails($sn, $sid){
		global $db;
		$db->select('product.sn, product.type, product.wt, product.colors, product.sizes, product_site.cid, product_site.alt_cids, product_site.image_dir, product_site.shelfstate, product_site.listprice, product_site.site_pname, product_site.price, product_site.site_purl, product_site.is_sync, sites.url,sites.id as sid, sites.codebase, sites.readonly, sites.image_dir_base, sites.name as site_name');
		$db->from('product');
		$db->join('product_site', 'product.sn = product_site.p_sn');
		$db->join('sites', 'product_site.sid = sites.id');
		$db->where('product.sn', $sn);
		$db->where('sites.id', $sid);
		$result = $db->get();
		$data = $result->row();
		if ($data) {
			if($data->site_purl == '' && $data->is_sync == '1'){
				$data->site_purl = $this->getProductPathAlias($sn, $sid, $data->site_pname, $data->cid);
			}
		}
		return $data;
	}
	
	function updateProductSite($sn, $sid, $set){
		global $db;
		$db->update('product_site', $set, array('p_sn' => $sn,'sid' => $sid ));
		return (boolean)$db->affected();
	}
	
	function getProductPathAlias($sn, $sid, $site_pname, $cid){
		$siteInstance = Site_Model::getInstance();
		$siteInfo = $siteInstance->getSite($sid);
		
		if($site_pname == ''){
			return '';
		}
		
		$path_alias = str_replace(array(' ','/'), '-', $site_pname) .'-p'.$sn.'.html';
		
		if($siteInfo->name =='iwantlingerie.com'){
			$categoryInstance = Category_Model::getInstance();
			$categoryInfo = $categoryInstance->getCategoryByCid($cid);
			$path_alias = str_replace(array(' ','/'), '-', $site_pname);
			$path_alias = str_replace(array(' ','/'), '-', strtolower($categoryInfo->name)). '/'.$path_alias .'.html';
		}
		return $path_alias;
	}
	
}