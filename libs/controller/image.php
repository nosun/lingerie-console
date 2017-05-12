<?php
class Image_Controller extends MD_Controller  {
  private $_imageInstance;

  public function init()
  {
    $this->_imageInstance = Image_Model::getInstance();
  }

  public function uploadAction(){
  	//firstly only admin can call this function.
  	//Then get the refer url
    global $user;
  	if(!$user->uid){
  		gotoUrl('');
  	}
  	if(key_exists('sn', $_POST) && $_POST['sn'] != ''){
  		$this->_imageInstance->uploadImage($_POST['sn']);
  	}else if(key_exists('pid', $_POST)){
  		$productModel = Product_Model::getInstance();
  		$product = $productModel->getProduct(null, array('id'=>$_POST['pid']));
  		if($product){
  			$this->_imageInstance->uploadImage($product->sn);
  		}else{
  			//no product exists, still not saved.
  			$this->_imageInstance->uploadImage($_POST['pid']);
  		}
  	}
  }
}