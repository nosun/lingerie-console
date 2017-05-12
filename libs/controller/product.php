<?php
class Product_Controller extends MD_Controller  {
  private $_productInstance;
  private $_siteInstance;
  private $_productSiteInstance;

  public function init()
  {
    $this->_productInstance = Product_Model::getInstance();
    $this->_productSiteInstance = ProductSite_Model::getInstance();
    $this->_siteInstance = Site_Model::getInstance();
    $this->view->assign('pageLabel', 'product');
  }
  public function showAction($page = 1){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		if(isset($post['page'])){
  			$page = $post['page'];
  			gotoUrl('product/show/'.$page);
  		}
  	}
  	
  	parent::assignPageVariables();
  	$categoryModel = Category_Model::getInstance();
  	//getProducts($order = null, $limit = null, $filter=null, $offset = null)
  	$itemPerPage = 10;
  	
  	$itemCount = $this->_productInstance->getProductsCount();
  	$products = $this->_productInstance->getProducts('id desc', 10, null, $itemPerPage * ($page - 1));
  	$sites = $this->_siteInstance->getAllSites();
  	$fields = Field_Model::getInstance();
  	foreach ($sites as $index=>$siteInfo){
  		$categoryModel = Category_Model::getInstance();
  		$siteInfo->site_categories = $categoryModel->getCategoriesStructureBySid($siteInfo->id);
  	}
  	
  	foreach ($products as $k=>$v){
  		//get product image source location.
  		$v->imageSources = glob("files/". $v->sn ."/*.*");
  		$uselessImage = glob("files/". $v->sn ."/blob*");
  		$v->imageSources = array_diff($v->imageSources, $uselessImage);
  		//if product don't have attributes.
  		if(!isset($v->attributes)){
  			$attributes = array();
	  	  	$type = $v->type;
	  		$attributeInstance = Attribute_Model::getInstance();
	  		$typeAttrs = $attributeInstance->getAttributes($type);
	  		foreach($typeAttrs as $attrs){
	  			$attributes[$attrs->name] = '';
	  		}
	  		$v->attributes = $attributes;
  		}
  		//get product stock properties.
  		$v->stocks = $this->getProductStockData($v);
  		
  		//get product site specific properties.
  		$v->site_details = array();
  		foreach($sites as $k2=>$v2){
  			$details = $this->_productSiteInstance->getProductSiteDetails($v->sn, $v2->id);
  			if(!$details){
  				//@TODO need to change
  				$this->_productSiteInstance->insertProductSite($v->sn, $v2->id, '', '0.00', '0.00');
  				$details = $this->_productSiteInstance->getProductSiteDetails($v->sn, $v2->id);
  				//add category info for details
  				$details->site_category = $categoryModel->getCategoriesStructureBySid($v2->id);
  			}
  			if($details){
  				$categories = $v2->site_categories;
  				if(key_exists($details->cid, $categories)){
  					$details->category= $categories[$details->cid];
  				}else{
  					$details->category = null;
  				}
  				//add category info for details
  				$details->site_category = $categoryModel->getCategoriesStructureBySid($details->sid);
  				$alt_categories = array();
  				if($details->alt_cids){
  					$alt_cid_array = unserialize($details->alt_cids);
	  				foreach($alt_cid_array as $cid_key=>$cid_value){
	  					$alt_categories[$cid_key] = $categories[$cid_value];
	  				}
  				}
  				$details->alt_categories = $alt_categories;
  				$v->site_details[] = $details;
  			}
  		}
  	}
  	$supplierInstance = Supplier_Model::getInstance();
  	$suppliers = $supplierInstance->getSuppliers();
  	
  	$this->view->assign('suppliers', $suppliers);
  	
  	$this->view->assign('sites', $sites);
  	
  	$typeInstance = Type_Model::getInstance();
  	$this->view->assign('type_options', $typeInstance->getAllTypes());
  	//$this->view->assign('fields_colors', $fields->getFields('colors'));
  	//$this->view->assign('fields_sizes', $fields->getFields('sizes'));
  	
  	$this->view->assign('products', $products);
  	$this->view->assign('pagination', pagination('product/show/%d', ceil($itemCount/$itemPerPage), $page));
    $this->view->render('product/product.tpl');
  }

  public function update1Action(){
      global $db;

      $file = file_get_contents('/srv/www/6.txt');
      $_arr = explode(PHP_EOL,$file);
      //var_dump($_arr);die;
      foreach($_arr as $sn){
        //echo $sn;die;
        $sql1 = 'update product_site ps INNER JOIN product_copy pc on ps.p_sn = pc.itemno  set ps.site_pname = pc.name where pc.itemno ="'.$sn.'" and ps.site_pname ="0.00"';
        $sql2 = 'update product_site ps INNER JOIN product_copy pc on ps.p_sn = pc.itemno  set ps.listprice = pc.price2 where pc.itemno ="'.$sn.'" and ps.listprice =0;';

        $db->query($sql1);
        #$db->query($sql2);


      }
  
      echo 'finished'; 
  }

  public function postAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}

  	if($this->isPost()){
  		$post = $_POST;
  		$result = array();
  		if(key_exists('pid', $post)){
  			if(key_exists('sid', $post)){
  				$set= array();
  				$productBasic = $this->_productInstance->getProduct(null,array('id'=>$post['pid']));
  				if($productBasic == false){
  					//new product, can not apply before the product is created.
		  				//this is an old sn, remind.
		  				$result['status'] = 'error';
		  				$result['data'] = 'Please apply the product settings first.';
		  				$result['error_code'] = 2;
		  				echo json_encode($result);
		  				return;
  				}
  				$set['cid'] = $post['values']['site_category'];

  				$set['listprice'] = $post['values']['site_listprice'];
  				$set['price'] = $post['values']['site_price'];
  				$set['site_pname'] = $post['values']['site_pname'];
  				//if is created.
  				$siteRelated = $this->_productSiteInstance->getProductSiteInfo($productBasic->sn, $post['sid']);
  				if($siteRelated == false){
  					$this->_productSiteInstance->insertProductSite($productBasic->sn, $post['sid'], $set['cid'], $set['listprice'], $set['price'], $set['site_pname']);
  				}else{
  					$this->_productSiteInstance->updateProductSite($productBasic->sn, $post['sid'],$set);
  				}
  				$result['data'] = $this->_productSiteInstance->getProductSiteInfo($productBasic->sn, $post['sid']);
  				$result['status'] = 'success';
  			}else{
		  		$set = array();
		  		$set['sn'] = $post['values']['p-serial'];
		  		$set['type'] = $post['values']['p-type'];
		  		$set['wt'] = $post['values']['p-weight'];

		  		//$set['stock'] = $post['values']['p-stock'];
		  		$set['suppliers'] = $post['values']['p-suppliers'];
		  		$set['attributes'] = array();
		  		// get attributes from post.
		  		if(isset($post['values']['attributes'])){
		  			foreach ($post['values']['attributes'] as $k=>$v){
		  				$parts = explode('-', $k);
		  				$attrName = trim($parts[1]);
		  				$set['attributes'][$attrName] = $v;
		  			}
		  		}
		  		//if the supplier is not exist.
		  		$supplierInstance = Supplier_Model::getInstance();
		  		
		  		$suppliers = explode(',', $set['suppliers']);
		  		foreach ($suppliers as $index=>$supplier){
		  			$supplier = trim($supplier);
		  			if($supplier !== ''){
			  			$oldSupplier = $supplierInstance->getSupplier($supplier);
			  			if(!$oldSupplier){
			  				//not exist, insert into db.
			  				$supplierInstance->insertSupplier($supplier);
			  			}
		  			}
		  		}
		  		
		  		$data = $this->_productInstance->getProduct(null,array('id'=>$post['pid']));
		  		if($data == false){
		  			//no product before, this is a newly inserted product.
		  			//first find whether the sn is changed.
		  			$data2 = $this->_productInstance->getProduct(null, array('sn'=>$set['sn']));
		  			if($data2 == false){
		  				//this is a new SN. Insert into DB.
		  				//$set['colors'], $set['sizes'],
		  				$insertId = $this->_productInstance->insertProduct($set['sn'], $set['type'], $set['wt'], $set['suppliers'], $set['attributes']);
		  				
		  				$product = $this->_productInstance->getProduct(null, array('id'=>$insertId));
		  				$product->stocks = $this->getProductStockData($product);
		  				
		  				$result['data'] = $product;
		  				$result['status'] = 'success';
		  			}else{
		  				//this is an old sn, remind.
		  				$result['status'] = 'error';
		  				$result['data'] = 'The SN you provided is already existed!';
		  				$result['error_code'] = 1;
		  			}
		  		}else{
		  			$this->_productInstance->updateProduct($post['pid'], $set);
		  			$product = $this->_productInstance->getProduct(null, array('id'=>$post['pid']));
		  			$product->stocks = $this->getProductStockData($product);
		  			$result['data'] = $product;
		  			$result['status'] = 'success';
		  		}
  			}
  		}
  		 echo json_encode($result);
  	}
  }

  public function addnewAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		$type = $post['type'];
  		$attributeInstance = Attribute_Model::getInstance();
  		$attributes = $attributeInstance->getAttributes($type);
	  	//$lastProducts = $this->_productInstance->getProducts('id desc',1);
	  	$lastProduct = $this->_productInstance->getProduct('id desc', array('type'=>$post['type']));
	  	
	  	$oldId = '';
	  	if(!$lastProduct){
	  		$newProduct = new stdClass();
	  		$newProduct->id = $this->_productInstance->getNextProductId('product');
	  		$newProduct->sn = '';
	  		$newProduct->type = $post['type'];
	  		$newProduct->wt = '';

	  		$newProduct->attributes = array();
	  		foreach ($attributes as $attribute){
	  			$newProduct->attributes[$attribute->name] = '';
	  		}
	  		$newProduct->stocks = false;
	  		$newProduct->suppliers = '';
	  	}else{
	  		$newProduct = $lastProduct;
	  		$newProduct->id = $this->_productInstance->getNextProductId('product');
	  		
	  	  	  	//if product don't have attributes.
	  		if(!isset($newProduct->attributes)){
	  			$newProduct->attributes = array();
	  			foreach ($attributes as $attribute){
	  				$newProduct->attributes[$attribute->name] = '';
	  			}
	  		}
	  	}
	  	$sites = $this->_siteInstance->getAllSites();
	  	$newProduct->imageSources = array();
	  	$newProduct->site_details = array();
	  	
	  	$typeModel = Type_Model::getInstance();
	  	
	  	$categoryModel = Category_Model::getInstance();
	  	
	    foreach ($sites as $index=>$siteInfo){
	  		$siteInfo->site_categories = $categoryModel->getCategoriesStructureBySid($siteInfo->id);
	  	}
	  	foreach($sites as $k=>$v){
	  		$details = $this->_productSiteInstance->getProductSiteDetails($newProduct->sn, $v->id);
	  		/*
	  		 if($details){
	  			//already existed.
		  		$newProduct->site_details[] = $details;
	  		}
	  		*/
	    	if(!$details){
	  			$this->_productSiteInstance->insertProductSite($newProduct->sn, $v->id, '', '', '0.00', '0.00');
	  			$details = $this->_productSiteInstance->getProductSiteDetails($newProduct->sn, $v->id);
	  			//add category info for details
	  			if(!$details){
	  				//still no details, then the product is not existed.
	  				$details = new stdClass();
	  				$details->cid = $categoryModel->getDefaultCategoryId($v->id);
	  				$details->sid = $v->id;
	  				$details->url = $v->url;
	  				$details->image_dir = '';
	  				$details->site_pname = '';
	  				$details->listprice = 0.00;
	  				$details->price = 0.00;
	  			}
	  			$details->site_category = $categoryModel->getCategoriesStructureBySid($v->id);
	  		}
	  		if($details){
	  			$categories = $v->site_categories;
	  			$details->category= $categories[$details->cid];
	  			//add category info for details
	  			$details->site_category = $categoryModel->getCategoriesStructureBySid($details->sid);
	  			$newProduct->site_details[] = $details;
	  		}
	  	}
	  	$this->view->assign('type_attributes', $attributes);
	  	
	  	$supplierInstance = Supplier_Model::getInstance();
	  	$suppliers = $supplierInstance->getSuppliers();
	  	
	  	$name_suppliers= array();
	  	foreach($suppliers as $supplier){
	  		$name_suppliers[] = $supplier->name;
	  	}
	  	$this->view->assign('name_suppliers', implode(',', $name_suppliers));
	  	
	  	$this->view->assign('type_options', $typeModel->getAllTypes());
	  	$this->view->assign('nextProduct', $newProduct);
	    $html = $this->view->render('product/new_product.tpl');
  	}
  }
  
  public function syncinfoAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$communicationInstance = Communication_Model::getInstance();
  	$categoryModel = Category_Model::getInstance();

  	//then fill all the category fields.
    if($this->isPost() && isset($_POST['sid'])){
  		$communicationInstance->syncCategoriesBySite($_POST['sid']);
  		$communicationInstance->syncProductsBySite($_POST['sid']);
  		$communicationInstance->syncTypesBySite($_POST['sid']);
  		$communicationInstance->syncProductFieldBySite('colors', $_POST['sid']);
  		$communicationInstance->syncProductFieldBySite('sizes', $_POST['sid']);
  	}else{
	  	$communicationInstance->syncCategories();
	  	$communicationInstance->syncProductsBySite($_POST['sid']);
	  	$communicationInstance->syncTypes();
	  	$communicationInstance->synProductField('colors');
	  	$communicationInstance->synProductField('sizes');
  	}
  	$sitesInfo = $this->_siteInstance->getAllSites();
  	$categories = array();
  	foreach($sitesInfo as $k=>$siteInfo){
  		$categories[$siteInfo->id] = $categoryModel->getCategoriesStructureBySid($siteInfo->id);
  	}
  	echo json_encode($categories);
  }
  
  public function changeshelfstateAction(){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$p_sn = $this->_productInstance->getProductSnById($_POST['pid']);
  	$productSiteInfo = $this->_productSiteInstance->getProductSiteInfo($p_sn, $_POST['sid']);
  	$siteInfo = $this->_siteInstance->getSite($_POST['sid']);
  	
  	$result = array();
  	//then change the product site info.
  	$newShelfState =($productSiteInfo->shelfstate == '0')?'1':'0';
    $communicationInstance = Communication_Model::getInstance();

    //update remote
    $response = $communicationInstance->updateProductOnshelfState($p_sn, $newShelfState, $siteInfo);
    if($response instanceof PHPRPC_Error){
  		$result['status'] = 'error';
  		$result['data'] = $response->toString();
    }
    else{
    	$this->_productSiteInstance->updateProductSiteInfo(array('shelfstate'=>$newShelfState), $p_sn, $_POST['sid']);
    	$result['status'] = 'success';
    	$result['data'] = $newShelfState;
  	}
  	echo json_encode($result);
  }

  public function getcategoryAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$post = $_POST;
  	$categoryModel = Category_Model::getInstance();
  	$category_options = $categoryModel->getCategoriesStructureBySid($post['sid']);
  	$this->view->assign('category_options', $category_options);
  	if(key_exists('current', $post)){
  		$this->view->assign('current_full_name', $post['current']);
  	}
    $this->view->render('category_select.tpl');
  }
  
  public function addcategoryAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$post = $_POST;
  	$categoryModel = Category_Model::getInstance();
  	$category_options = $categoryModel->getCategoriesStructureBySid($post['sid']);
  	
  	/*$product = $this->_productInstance->getProduct(null, array('id'=>$post['pid']));
  	$sn = $product->sn;
  	$nextId = $this->_productSiteInstance->getNextAltCategoryId($sn, $post['sid']);
  	*/
  	$this->view->assign('category_options', $category_options);
    $this->view->render('category_add.tpl');
  }
  
  public function updatecategoryAction(){
  	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	$post = $_POST;
  	$new_categories = explode(',', $post['added_categories']);
  	$product = $this->_productInstance->getProduct(null, array('id'=>$post['pid']));
  	
  	if(!$product){
  		echo '<div class="message_info">error, the product is not exist. apply the common settings first.</div>';
  		exit();
  	}
  	$sn = $product->sn;

  	$categoryModel = Category_Model::getInstance();
  	$categories = $categoryModel->getCategoriesStructureBySid($post['sid']);
  	$newCategorie_cids = $this->_productSiteInstance->addCategoriesForProductSite($new_categories, $sn, $post['sid']);
  	$newCategorieNames = array();
  	foreach($newCategorie_cids as $k=>$v){
  		$newCategoryNames[$k] = $categories[$v];
  	}
  	
  	$this->view->assign('newCategories', $newCategoryNames);
  	$this->view->render('alt_category_update.tpl');
  }
  
  public function gettypeAction(){
  	$post = $_POST;
  	$typeModel = Type_Model::getInstance();
  	$type_options = $typeModel->getAllTypes();
  	$this->view->assign('type_options', $type_options);
  	if(key_exists('current', $post)){
  		$this->view->assign('current_type', $post['current']);
  	}
    $this->view->render('type_select.tpl');
  }
  
  public function productfilterAction($page = 1){
   	global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
    $this->view->assign('pageLabel', 'productfilter');

  	if($this->isPost()){
  		$post = $_POST;
  		
  		unset($_SESSION['filters']);
  	  	if(isset($post['page'])){
  			$page = $post['page'];
  			gotoUrl('product/productfilter/'.$page);
  		}
  		$filters = array();
  		//if(isset($post['name_filter']) && $post['name_filter'] != ''){$filters['name'] = $post['name_filter'];}
	  	if(isset($post['sn_filter']) && $post['sn_filter'] != '' && $post['sn_filter'] != 'SN'){$filters['sn'] = $post['sn_filter'];}
	  	if(isset($post['type_filter']) && $post['type_filter'] != '' && $post['type_filter'] != 'TYPE'){$filters['type'] = $post['type_filter'];}
	  	if(isset($post['weight_filter']) && $post['weight_filter'] != '' && $post['weight_filter'] != 'WEIGHT'){$filters['wt'] = $post['weight_filter'];}
	  	if(isset($post['color_filter']) && $post['color_filter'] != '' && $post['color_filter'] != 'COLOR'){$filters['colors'] = $post['color_filter'];}
	  	if(isset($post['size_filter']) && $post['size_filter'] != '' && $post['size_filter'] != 'SIZE'){$filters['sizes'] = $post['size_filter'];}
  		$_SESSION['filters'] = $filters;
  	}

  	parent::assignPageVariables();
  	
  	$filters = $this->getFilter();
  	$categoryModel = Category_Model::getInstance();

  	//getProducts($order = null, $limit = null, $filter=null, $offset = null)
  	$itemPerPage = 10;
  	
  	$itemCount = $this->_productInstance->getProductsCountByFilter('id DESC', $filters);
  	$products = $this->_productInstance->getProductsLike('id DESC', 10, $filters, $itemPerPage * ($page - 1));
  	$sites = $this->_siteInstance->getAllSites();
  	$fields = Field_Model::getInstance();
  	foreach ($sites as $index=>$siteInfo){
  		$categoryModel = Category_Model::getInstance();
  		$siteInfo->site_categories = $categoryModel->getCategoriesStructureBySid($siteInfo->id);
  	}
  	
  	foreach ($products as $k=>$v){
  		$v->imageSources = glob("files/". $v->sn ."/*.*");
  		$uselessImage = glob("files/". $v->sn ."/blob*");
  		$v->imageSources = array_diff($v->imageSources, $uselessImage);
  	  	//if product don't have attributes.
  		if(!isset($v->attributes)){
  			$attributes = array();
	  	  	$type = $v->type;
	  		$attributeInstance = Attribute_Model::getInstance();
	  		$typeAttrs = $attributeInstance->getAttributes($type);
	  		foreach($typeAttrs as $attrs){
	  			$attributes[$attrs->name] = '';
	  		}
	  		$v->attributes = $attributes;
  		}
  		
  		$v->stocks = $this->getProductStockData($v);
  		
  		$v->site_details = array();
  		foreach($sites as $k2=>$v2){
  			$details = $this->_productSiteInstance->getProductSiteDetails($v->sn, $v2->id);
  			if(!$details){
  				$this->_productSiteInstance->insertProductSite($v->sn, $v2->id, '', '', '0.00', '0.00');
  				$details = $this->_productSiteInstance->getProductSiteDetails($v->sn, $v2->id);
  				//add category info for details
  				$details->site_category = $categoryModel->getCategoriesStructureBySid($v2->id);
  			}
  			if($details){
  				$categories = $v2->site_categories;
  				if(key_exists($details->cid, $categories)){
  					$details->category= $categories[$details->cid];
  				}else{
  					$details->category = null;
  				}
  				//add category info for details
  				$details->site_category = $categoryModel->getCategoriesStructureBySid($details->sid);
  				
  				$alt_categories = array();
  				if($details->alt_cids){
  					$alt_cid_array = unserialize($details->alt_cids);
	  				foreach($alt_cid_array as $cid_key=>$cid_value){
	  					$alt_categories[$cid_key] = $categories[$cid_value];
	  				}
  				}
  				$details->alt_categories = $alt_categories;
  				$v->site_details[] = $details;
  			}
  		}
  	}
  	
  	$supplierInstance = Supplier_Model::getInstance();
  	$suppliers = $supplierInstance->getSuppliers();
  	
  	$this->view->assign('suppliers', $suppliers);
  	$this->view->assign('sites', $sites);
  	
  	$typeInstance = Type_Model::getInstance();
  	$this->view->assign('type_options', $typeInstance->getAllTypes());
  	
  	
  	//$this->view->assign('fields_colors', $fields->getFields('colors'));
  	//$this->view->assign('fields_sizes', $fields->getFields('sizes'));
  	
  	$this->view->assign('products', $products);
  	$this->view->assign('pagination', pagination('product/productfilter/%d', ceil($itemCount/$itemPerPage), $page));
    $this->view->assign('productfilters', $filters);
  	$this->view->render('product/filter_product.tpl');

  }
  
  private function getFilter(){
  	if(isset($_SESSION['filters'])){
  		$filter = $_SESSION['filters'];
  	}
  	else{
  		$filter = array();
  	}
  	return $filter;
  }
  
  public function synctositeAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
    }
   
  	$result = 'Please try again.';
  	$post = $_POST;
  	$pid = $post['pid'];
  	$sid = $post['sid'];
  	$isSyncImg = $post['sync_img'];
  	$product = $this->_productInstance->getProduct(null, array('id'=>$pid));

    $details = $this->_productSiteInstance->getProductSiteDetails($product->sn, $sid);
  	if($details){
  		$categoryModel = Category_Model::getInstance();
  		$categoryInfo = $categoryModel->getCategoryByCid($details->cid);
	  	$communicationInstance = Communication_Model::getInstance();
	  	$returnCode =  $communicationInstance->syncToSite($product, $details, $categoryInfo, $isSyncImg);
	  	if($returnCode == 1){
	  		//successfully synced. change the status.
	  		$this->_productSiteInstance->updateProductSiteStatus(1, $product->sn, $sid);
	  		$result = 'success';
	  	}else if($returnCode == 2){
	  		$this->_productSiteInstance->updateProductSiteUrl($product->sn, $sid);
	  		$result = 'success';
	  	}else{
	  		$result = 'Error when uploading, please sync again!';
	  	}
  	}else{
  		//no details for this product. so can not perform sync.
  		$result = 'No related product information.';
  	}
  	echo $result;
  }

  public function synctositeBatchAction(){
         global $user;
         if(!$user->uid){
             gotoUrl('');
          }
          $result = 'Please try again.';
          $file = file_get_contents('/srv/www/6.txt');
          $_arr = explode(PHP_EOL,$file);
          $str ='';
          foreach($_arr as $row){
             if(!empty($row)) $str.='"'.$row.'",';
          }

          $str = substr($str,0,-1);
          //var_dump($str);die;
          $sid = $_GET['sid']; 
          global $db;
          $sql = 'select p.id as pid,pp.sid from product p inner join product_site pp on p.sn = pp.p_sn where p.sn in ('. $str .') and pp.sid ='.$sid;
          $res = $db->query($sql)->all();
          //var_dump($res);die;
          if(empty($res)){
            echo 'nothing';
            exit();
          }
          //var_dump($res);die;
          foreach($res as $row){
             $pid = $row->pid;
             $sid = $sid;
             $isSyncImg = false;
             $product = $this->_productInstance->getProduct(null, array('id'=>$pid));
             $details = $this->_productSiteInstance->getProductSiteDetails($product->sn, $sid);
             if($details){
               $categoryModel = Category_Model::getInstance();
               $categoryInfo = $categoryModel->getCategoryByCid($details->cid);
               $communicationInstance = Communication_Model::getInstance();
               $returnCode =  $communicationInstance->syncToSite($product, $details, $categoryInfo, $isSyncImg);
               if($returnCode == 1){
                 $this->_productSiteInstance->updateProductSiteStatus(1, $product->sn, $sid);
                 $result = $pid.'success';
               }else if($returnCode == 2){
                  $this->_productSiteInstance->updateProductSiteUrl($product->sn, $sid);
                 $result = $pid.'success';
               }else{
                 $result = 'Error when uploading, please sync again!';
               }        
             }else{
                $result = 'No related product information.';
             }
              file_put_contents('/tmp/'.$sid.'sync.log',$result.PHP_EOL,FILE_APPEND);
         }
  }


  public function fetchautoremindAction(){
      global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
	  	$post = $_POST;
	  	$pid = $post['pid'];
	  	$pType = $post['type'];
	  	
	  	$returnArray = array();
	  	if(isset($post['attrNames'])){
	  		$attributeInstance = Attribute_Model::getInstance();
	  		foreach($post['attrNames'] as $index=>$attrName){
	  		  	if(startsWith($attrName, 'attributes.')){
			  		$short_attrName = substr($attrName, 11);
			  		$returnArray[$attrName] = explode(',', $attributeInstance->getAttributeValue($pType, $short_attrName));
			  	}else if($attrName == 'suppliers'){
			  		$supplierInstance = Supplier_Model::getInstance();
			  		$data = $supplierInstance->getSuppliers();
			  		$suppliers = array();
			  		foreach($data as $k=>$v){
			  			$suppliers[] = $v->name;
			  		}
			  		$returnArray[$attrName] = $suppliers;
			  	}
	  		}
	  		echo json_encode($returnArray);
	  	}
  	}
  }
  
  
  public function completestockeditAction(){
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if($this->isPost()){
  		$post = $_POST;
  		$stocks = $post['stocks'];
  		$stockInstance = Stock_Model::getInstance();
  		$productInstance = Product_Model::getInstance();
  		foreach($stocks as $v){
  			$stockInstance->updateStockByStockId($v['stock_id'], array('stock_qty'=>$v['stock_qty'], 'bought_price'=>$v['bought_price']));
  		    if (!empty($v['suppliers_sn'])) {
  		        $supplier_sn = $v['suppliers_sn'];
  		        $stock = $stockInstance->getStockById($v['stock_id']);
  		        $productInstance->updateProductBySn($stock->p_sn, array('suppliers_sn' => $supplier_sn));
  		    }
  		}
  	}
  }
  
  private function constructStockObject($stock_id, $p_sn, $parameters, $stock_qty="0", 
  											$bought_price="0.00", $sell_price_delta="0.00"){
  	$newStock = new stdClass();
  	$newStock->stock_id = $stock_id;
  	$newStock->p_sn = $p_sn;
  	$newStock->parameters = $parameters;
  	$newStock->stock_qty = $stock_qty;
  	$newStock->bought_price = $bought_price;
  	$newStock->sell_price_delta = $sell_price_delta;
  	return $newStock;
  }
  
  private function getProductStockData($product){
  	//get product stock properties.
  	$stockInstance = Stock_Model::getInstance();
  	$existingStocks = $stockInstance->getStock($product->sn);
  	
  	$showingStocks = array();
  	$attributes = array();
  	if(isset($product->attributes)){
	  	foreach($product->attributes as $aName=>$aValue){
	  		$tempAttrs = explode(',', $aValue);
	  		foreach($tempAttrs as $index=>$val){
	  			$val = trim($val);
	  			if($val != ''){
	  				$attributes[$aName][] = $val;
	  			}
	  		}
	  	}
  	}
  	$arrayComb = getArrayCombinations($attributes);
  	//$needObtainAgain = false;
  	foreach($arrayComb as $row){
  		$founded = false;
  		foreach($existingStocks as $stock){
  			if($this->_productInstance->isPropertiesEqual($row, $stock->parameters)){
  				$founded = true;
  				$showingStocks[] = $this->constructStockObject($stock->stock_id, $stock->p_sn, $stock->parameters, $stock->stock_qty, 
  																$stock->bought_price, $stock->sell_price_delta);
  				break;
  			}
  		}
  		if(!$founded){
  		  	//insert into stock. default data is 0.
  			//if(!$needObtainAgain){$needObtainAgain = true;}
  			$set = array();
  			$insertedId = $stockInstance->insertStock($product->sn, $row, '0', '0.00', '0.00');
  			$showingStocks[] = $this->constructStockObject($insertedId, $product->sn, $row);
  		}
  	}
  	/**
  	if($needObtainAgain){
  		if(!$needObtainAgain){$needObtainAgain = true;}
  		$existingStocks = $stockInstance->getStock($product->sn);
  	}
  	**/
  	return $showingStocks;
  }
  
}
