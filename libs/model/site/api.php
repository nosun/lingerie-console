<?php
/**
 * 
 * This is the API Class to the site
 * @author Peng
 *
 */
abstract class Site_API_Model extends MD_Model
{
  public static function getInstance($className, $siteName, $siteUrl)
  {
    static $_instances = array();
    if (!isset($_instances[$className.'-'.$siteName])) {
      $_instances[$className.'-'.$siteName] = new $className($siteName, $siteUrl);
    }
    return $_instances[$className.'-'.$siteName];
  }

  public abstract function getCategories();
  public abstract function getProductFields($field_name, $type);
  public abstract function getProductTypes();
  public abstract function syncProductsFromSite();
  public abstract function syncToSite($productInfo, $product_siteInfo, $categoryInfo,$isSyncImg = true);
  public abstract function uploadImage($site_pid, $fileDir, $target_url, $targetDir);

  public abstract function insertProduct($productInfo, $product_siteInfo, $categoryInfo);
  public abstract function updateProduct($updateData, $sn);
  public abstract function updateOrderAddress($orderNumber, $newData);
  //for order management
  public abstract function getOrders($startTime, $endTime);
  public abstract function getOrderItems($orderNumber);
  
  /**
   * 
   * 批量获取订单的Item（一次获取500个订单）
   * @param array $orderList 订单的number集合
   * @param int $orderCountPerFetch 每次获取多少个订单的订单Item default 500;
   * @return 订单Item的集合
   */
  public abstract function batchGetOrderItems($orderList, $orderCountPerFetch=500);
  public abstract function updateOrder($updateData, $orderNumber);
  
  /**
   * 订单沟通
   * @param string $orderNumber 沟通的订单编号
   * @param array $stockItemList 库存可以满足的订单array(sn=>qty)
   */
  public abstract function communicateOrder($orderNumber, $stockItemList);
  
  public abstract function getRefundAmount($orderNumber, $stockItemList);
  
  public abstract function getWholesaleUser($startId);
}