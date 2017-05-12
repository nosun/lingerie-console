<?php
class Index_Controller extends MD_Controller  {
  private $_indexInstance;

  public function init()
  {
    $this->_indexInstance = Index_Model::getInstance();
    $this->view->assign('pageLabel', 'index');
  }
  
  public function showAction(){
  	parent::assignPageVariables();
  	$this->assignVariables();
    $this->view->render('index.tpl');
  }
  /**
   * 
   * Handling assign variables for different page type.
   * @param string $pageType
   * @param string $url_key
   * @param pointer $variables
   */
  function assignVariables(){
 
  }
}