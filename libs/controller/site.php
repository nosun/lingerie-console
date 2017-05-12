<?php
class Site_Controller extends MD_Controller  {
  private $_categoryInstance;

  public function init()
  {
    $this->_categoryInstance = Category_Model::getInstance();
  }

  public function showAction($url_key = null){
  	parent::assignPageVariables();
  	if(!isset($url_key) || $url_key == ""){
  		//show all categories.
  		$this->view->assign('all_categories', $this->_categoryInstance->getSortedSubCategories(0));
  		$this->view->assign('alpha_categories', $this->_categoryInstance->getSubCategories(0));
	    $breadcrumbs = array(
	    	'Home' => DOMAIN_BASE_PATH,
	    	'All Categories' => "All Categories",
	    );
	    $this->view->assign('breadcrumbs', $breadcrumbs);
      		$this->view->render('all_category.tpl');
  		return;
  	}
    $this->assignVariables($url_key);
    $this->view->render('category.tpl');
  }
 
  /**
   * Handling assign variables for different page type.
   * @param string $pageType
   * @param string $url_key
   * @param pointer $variables
   */
  function assignVariables($url_key){
    //TODO add variables.
    $this->view->assign('all_categories', $this->_categoryInstance->getSortedSubCategories(0));
    
    $categoryInfo = $this->_categoryInstance->getCategoryInfoByUrlKey($url_key);
    
    $categoryStructure = $this->_categoryInstance->getCategoryStructure($categoryInfo->cid);
    
    $breadcrumbs = array(
    	'Home' => DOMAIN_BASE_PATH,
    );
    
    while(isset($categoryStructure->activeChild)){
    	$breadcrumbs[$categoryStructure->title] = url( $categoryStructure->urlkey . $categoryStructure->urlsuffix);
    	$categoryStructure = $categoryStructure->activeChild;
    };
    $breadcrumbs[$categoryStructure->title] = url($categoryStructure->urlkey.$categoryStructure->urlsuffix);
    
    $this->view->assign('baseVariables', $categoryInfo);
    $this->view->assign('url_key', $url_key);
    $this->view->assign('breadcrumbs', $breadcrumbs);
    
    //first show the sub categories for this category.
    //then show the single article for category.
    $categoryArticles = $this->_categoryInstance->getCategoryArticles($categoryInfo->cid);
    $subCategories = $this->_categoryInstance->getSubCategories($categoryInfo->cid);
    
    foreach($subCategories as $k => $subCategoryInfo){
    	if($this->_categoryInstance->isLeafCategory($subCategoryInfo)){
    		$subCategorieInfo->sub_details = $this->_categoryInstance->getCategoryArticles($subCategories->cid);
    	}else{
    		$subCategorieInfo->sub_details = $this->_categoryInstance->getSubCategories($subCategories->cid);
    	}
    }
    
    $this->view->assign('categoryArticles', $categoryArticles);
    $this->view->assign('subCategories', $subCategories);
    
    //assign relative ads code variables.
    $this->view->assign('ads_codes', $this->_categoryInstance->getAdsCodes($url_key));
    
    $config_category = MD_Config::get('category');
    if(key_exists('modules', $config_category)){
    	$modules = explode(',', $config_category['modules']);
        foreach($modules as $k=>&$v){
    		$v = trim($v);
    	}
    	if(in_array('latest', $modules)){
    		$articleInstance = Article_Model::getInstance();
			$new_topics = $articleInstance->getLatestArticles(MD_Config::get('latest_count', 10));
			$this->view->assign('newArticles', $new_topics);
			$this->view->assign('module_latest', true);
    	}if(in_array('catheader', $modules)){
    		$this->view->assign('module_cat_header', true);
    	}
    }
  }
}