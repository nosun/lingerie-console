<?php
final class MD_General_Exception extends Exception
{
  public function __construct($message, $code = 0)
  {
    parent::__construct($message, $code);
    if (MD_Config::get('log.error', true)) {
      log::save('error', $message, $this);
    }
  }
}

final class MD_403_Exception extends Exception
{
  public function __construct($message, $code = 0)
  {
    header('HTTP/1.1 403 Forbidden');
    parent::__construct($message, $code);
    if (MD_Config::get('log.error', true)) {
      log::save('403', $message, $this);
    }
  }
}

final class MD_404_Exception extends Exception
{
  private $_uri;
  private $_router;

  public function __construct($message, $code = 0)
  {
    header('HTTP/1.1 404 Not Found');
    parent::__construct($message, $code);
    $this->_uri = MD_Core::getUri();
    $this->_router = MD_Core::getRouter();
    if (class_exists('log') && MD_Config::get('log.404', true)) {
      log::save('404', $message, $this);
    }
  }

  public function getUri()
  {
    return $this->_uri;
  }

  public function getRouter()
  {
    return $this->_router;
  }
}

function goto403($message = 'Access Denied.')
{
  throw new MD_403_Exception($message);
}

function goto404($message = '')
{
  throw new MD_404_Exception($message);
}

function gotoUrl($path, $httpCode = 302)
{
  if (strcasecmp('http://', $path) != 0 && strcasecmp('https://', $path) != 0) {
    $path = url($path, false);
  }
  header('Location: ' . $path, true, $httpCode);
  exit;
}

function getUrlKeyByName($name){
	$name = str_ireplace('&', '', $name);
	$name = str_ireplace("'", '', $name);
	$pattern = '/\s+/';
	$name = preg_replace($pattern, '-', $name);
	return $name;
}

function makedir($path, $permission = null)
{
	if(!isset($permission)){
		$permission = 0705;
	}
	if(!is_dir($path)){
		mkdir($path, $permission, true);
	}
}

/**
 * 
 * @param unknown_type $defaultPath
 * @param unknown_type $httpCode
 */
function gotoBack($defaultPath = '', $httpCode = 302)
{
  if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
    header('Location: ' . $_SERVER['HTTP_REFERER'], true, $httpCode);
    exit;
  } else {
    gotoUrl($defaultPath, $httpCode);
  }
}

function callFunction($func)
{
  $args = func_get_args();
  array_shift($args);
  if (function_exists('hook_' . $func)) {
    return call_user_func_array('hook_' . $func, $args);
  } else if (function_exists($func)) {
    return call_user_func_array($func, $args);
  }
}

/**
 * 
 * @param unknown_type $returnLong
 */
function ipAddress($returnLong = false)
{
  static $ipAddress = null;
  static $ipAddressLong;
  if (!isset($ipAddress)) {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
  }
  if ($returnLong) {
    if (!isset($ipAddressLong)) {
      $ipAddressLong = ip2long($ipAddress);
    }
    return $ipAddressLong;
  } else {
    return $ipAddress;
  }
}

/**
 * 
 * @param unknown_type $ip
 * @param unknown_type $cidr
 * @throws Bl_403_Exception
 * @throws Bl_404_Exception
 */
function ipCheck($ip, $cidr)
{
  static $list = array();
  if (!isset($list[$ip])) {
    $list[$ip] = ip2long($ip);
  }
  $parts = explode('/', $cidr);
  if (isset($parts[1])) {
    $parts[0] .= $parts[1] > 16 ? '.0' : '.0.0';
    $ipMask = ~((1 << (32 - $parts[1])) - 1);
    return (($list[$ip] & $ipMask) == ip2long($parts[0]));
  } else {
    return !strcmp($ip, $parts[0]);
  }
}

function build_site_map(){
	global $db;
	$siteMapFile = DOCROOT . "/sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'w') or die("can't open file");
	$pageInfoInstance = PageInfo_Model::getInstance();
	$pageInfos = $pageInfoInstance->getAllPageInfos();
	
	//add detailed pages and categories
	foreach($pageInfos as $k=>$v){
		fwrite($siteMapFileHandle, DOMAIN_BASE_PATH.$v->urlkey.$v->urlsuffix."\n");
	}
	
	//add tag urls
	$tagInstance = Tag_Model::getInstance();
	$tags = $tagInstance->getAllTags();
	foreach($tags as $k=>$v){
		fwrite($siteMapFileHandle, $stringData);
	}
	fclose($siteMapFileHandle);
}

function build_site_map_from_db(){
	global $db;
	$siteMapFile = DOCROOT . "/sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'w') or die("can't open file");
	$db->select('urlkey, urlsuffix');
	$db->from('pageinfo');
	$result = $db->get();
	$urlInfos = $result->all();
	foreach($urlInfos as $k=>$v){
		fwrite($siteMapFileHandle, url($v->urlkey.$v->urlsuffix)."\n");
	}
	fclose($siteMapFileHandle);
}


function add_to_site_map($url){
	$siteMapFile = DOCROOT . "/sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'a') or die("can't open file");
	fwrite($siteMapFileHandle, $url."\n");
	fclose($siteMapFileHandle);
}

function update_site_map($urls){
	$siteMapFile = DOCROOT . "/sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'a') or die("can't open file");
	foreach($urls as $k=>$v){
		fwrite($siteMapFileHandle, $v."\n");
	}
	fclose($siteMapFileHandle);
}


function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * 
 * get whole url by specify the relative path
 * @param string $relativePath
 */
function url($relativePath){
  if(startsWith($relativePath, 'http://')){
  	return $relativePath;
  }
  return DOMAIN_BASE_PATH . $relativePath;
}

function base_relative_path(){
	$paths = explode("//", DOMAIN_BASE_PATH);
	return substr($paths[1], strpos($paths[1], "/"));
}

function plain($string)
{
  return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function firstSentence($str){
	$str_count = strlen($str);
	$temps = explode('.', $str);
	$str = $temps[0];
	$temps = explode('?', $str);
	$str = $temps[0];
	$temps = explode('!', $str);
	$str = $temps[0];
	$temps = explode(':', $str);
	$str = $temps[0];
	$str_new_count = strlen($str);
	if($str_count == $str_new_count){
		$temps = explode(',', $str);
		$str = $temps[0];
	}
	return $str;
}

/**
 * 
 * Get the last element of an array but still keeps it's
 * Inner pointer unchanged.(Made a pointer copy for the array).
 * @param array $array
 */
function endc($array){
  return end( $array );
}

/**
 * 
 * Shuffer the array but still keeps the key.
 * The default shuffer function will not keep the key.
 * @param array $array
 */
function kshuffle(&$array) {
    if(!is_array($array) || empty($array)) {
        return false;
    }
    $tmp = array();
    foreach($array as $key => $value) {
        $tmp[] = array('k' => $key, 'v' => $value);
    }
    shuffle($tmp);
    $array = array();
    foreach($tmp as $entry) {
        $array[$entry['k']] = $entry['v'];
    }
    return true;
}

function timer()
{
  static $timer = null;
  if (!isset($timer)) {
    $timer = microtime(true);
    return 0;
  } else {
    $startTimer = $timer;
    $timer = microtime(true);
    return $timer - $startTimer;
  }
}
function timezone_date($format, $timestamp = 'current'){
	date_default_timezone_set('America/New_York');
	if($timestamp == 'current'){
		return date($format);
	}
	return date($format, $timestamp);
}

function get_thumbnail($image_path){
	return substr_replace($image_path, '/thumbnail/', strrpos($image_path, '/'), 1);
}

/**
 * 
 * @param unknown_type $sid
 * @throws Bl_403_Exception
 * @throws Bl_404_Exception
 */
function anonymousUser($sid)
{
  $user = new stdClass();
  $userInstance = User_Model::getInstance();
  $user->uid = 0;
  $user->name = 'Anonymous';
  $user->sid = $sid;
  return $user;
}



function pagination($urlPage, $pageCount, $page, $firstPath = null){
  $postPage = str_replace('%d', '', $urlPage);
  $output ='<div class="gotopage"><form method="post" action="'.url($postPage).'"><input type="text" name="page" class="" value=""/><input type="submit" class="button" value="go"/> </a></form></div>';
  
  $output .= '<ul>';
  if ($page > 1){
    $previousUrl = str_replace('%d', ''.($page - 1), $urlPage);
    $output .= '<li class="pageControl previous"><a title="page '.($page - 1).'" href="'
    .url($previousUrl).'"><span>Prev&nbsp;</span></a></li>';
  }
  $output .= '<li class="pageIndex">';
  if($page - 1< 3){
    for($i=1; $i<$page; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    $firstPageUrl = str_replace('%d', '1', $urlPage);
    $output .= '<a title="page 1" href="'.url($firstPageUrl). '">1</a>';
    $output .= '<span>...</span>';
    for($i=$page-2; $i<$page; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }
  $output .= '<strong>'.$page.'</strong>';
  //echo after page.
  if($pageCount - $page < 3){
    for($i=$page + 1; $i <= $pageCount; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    for($i=$page + 1; $i<$page + 3; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
    $output .= '<span>...</span>';
    $lastPageUrl = str_replace('%d', ''.$pageCount, $urlPage);
    $output .= '<a title="page '.$pageCount.'" href="'.url($lastPageUrl). '">'.$pageCount.'</a>';
  }
  if ($page < $pageCount){
    $output .= '</li>';
    $nextUrl = str_replace('%d', ''.($page + 1), $urlPage);
    $output .= '<li class="pageControl next"><a title="page '.($page + 1).'" href="'.url($nextUrl).'"><span>Next&nbsp;</span></a></li>';
  }
  $output .= '</ul>';
  return $output;
}

function translate($key){
	if(strtolower($key) == 'color') return '颜色';
	if(strtolower($key) == 'size') return '大小';
	if(strtolower($key) == 'length') return '长度';
}

function getArrayCombinations($a) {
  $result = array(array());
  foreach ($a as $key=>$list) {
    $_tmp = array();
    foreach ($result as $result_item) {
      foreach ($list as $list_item) {
        $_tmp[] = array_merge($result_item, array($key=>$list_item));
      }
    }
    $result = $_tmp;
  }
  return $result;
}

function outputCSV($data) {
    $outputBuffer = fopen("php://output", 'w');
    foreach($data as $val) {
    	fputcsv($outputBuffer, $val);
    }
    fclose($outputBuffer);
}

function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

function startsWithNumber($string) {
    return strlen($string) > 0 && ctype_digit(substr($string, 0, 1));
}

function debug_to_console( $data ) {

    if ( is_array( $data ) )
        $output = 'Debug Objects: ' . implode( ',', $data) . '<br>';
    else
        $output = 'Debug Objects: ' . $data . '<br>';

    echo $output;
}