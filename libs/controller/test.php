<?php
class Test_Controller extends MD_Controller  {

  public function init()
  {

  }

  public function sendImageAction(){
    $pid = 3611;
    $sid = 5;
    $product = Product_Model::getInstance()->getProduct(null, array('id'=>$pid));
    $details = ProductSite_Model::getInstance()->getProductSiteDetails($product->sn, $sid);
    $communicationInstance = Communication_Model::getInstance();
    $sites = Site_Model::getInstance()->getAllSites();
    $siteInfo = $sites[$sid];
    $site_pid   = 2729;
    $fileDir    = 'files/'.$details->sn;
    $target_url = $details->url.'/imgbatchuploader/upload/';
    $targetDir  = $details->sn.'/';
    $communicationInstance->uploadImage($site_pid, $fileDir, $target_url, $targetDir, $siteInfo);
  }
}