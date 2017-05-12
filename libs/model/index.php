<?php
class Index_Model extends Common_Model  {
	
  /**
   * @return Index_Model
   */
  public static function getInstance()
  {
    return parent::getInstance(__CLASS__);
  }
}