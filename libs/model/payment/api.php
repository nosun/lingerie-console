<?php 
abstract class Payment_API_Model extends MD_Model
{
  public static function getInstance($className)
  {
    static $_instances = array();
    if (!isset($_instances[$className])) {
      $_instances[$className] = new $className();
    }
    return $_instances[$className];
  }

  public abstract function charge_refund($oid, $refund_type="fully", $refund_val = 0.0);
}