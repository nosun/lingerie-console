<?php
class cache
{
  private static function getPath()
  {
    static $path = null;
    if (!isset($path)) {
    	//to avoid conflict for many running copies.
      $path = MD_Config::get('cache.path', DOCROOT. '/cache/' . SITE_NAME .'/');
      if (!is_writable($path)) {
        makedir($path);
      }
      if ($path) {
        strtr($path, '\\', '/');
        if ($path[strlen($path) - 1] != '/') {
          $path .= '/';
        }
        if (!is_writable($path)) {
          $path = false;
        }
      } else {
        $path = false;
      }
    }
    return $path;
  }

  private static function getFile($cacheId)
  {
    return self::getPath() . $cacheId . '.cache';
  }

  public static function get($cacheId)
  {
  	if(MD_Config::get('cache_enable', false) == false){
  		return false;
  	}
    $file = self::getFile($cacheId);
    if (!is_file($file) || !($content = file_get_contents($file))) {
      return false;
    }
    $time = intval(substr($content, 0, 10));
    if (TIMESTAMP > $time) {
      return false;
    }
    $cache = new stdClass();
    $cache->time = $time;
    $cache->data = unserialize(substr($content, 11));
    return $cache;
  }

  public static function save($cacheId, $data, $lifetime = null)
  {
    if(MD_Config::get('cache_enable', false) == false){
  		return false;
  	}
  	//make time stamp as 1 day.
    $lifetime = isset($lifetime) ? intval($lifetime) : MD_Config::get('cache.lifetime', 86400);
    $path = self::getPath();
    if (!$path) {
      return false;
    }
    file_put_contents(self::getFile($cacheId), strval(TIMESTAMP + $lifetime) . ';' . serialize($data));
  }

  public static function remove($cacheId)
  {
    if(MD_Config::get('cache_enable', false) == false){
  		return false;
  	}
    $path = self::getPath();
    if (!$path) {
      return false;
    }
    $file = self::getFile($cacheId);
    if (is_file($file)) {
      unlink($file);
    }
  }

  public static function clean()
  {
    if(MD_Config::get('cache_enable', false) == false){
  		return false;
  	}
    $path = self::getPath();
    if ($path && $dh = opendir($path)) {
      while (false !== ($file = readdir($dh))) {
        if (substr($file, -6) == '.cache') {
          unlink($path . '/' . $file);
        }
      }
    }
  }
}
