<?php
class User_Model extends MD_Model
{

  /**
   * @return User_Model
   */
  public static function getInstance()
  {
    return parent::getInstance(__CLASS__);
  }

  /**
   * 获取用户信息
   * @param int $uid 用户ID
   * @return object
   */
  public function getUserInfo($uid)
  {
    global $db;
    if( !$uid )return false;
    $result = $db->query('SELECT * FROM users WHERE uid = ' . $db->escape($uid));
    $userInfo = $result->row();
    return $userInfo;
  }


  /**
   * 检查用户名格式是否有效
   * @param string $name 用户名
   * @return boolean
   */
  public function checkNameIsValid($name)
  {
    return (boolean)(preg_match('/^\w{3,20}$/i', $name) || preg_match('/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i', $name));
  }
  
  /**
   * 检查注册邮箱是否存在
   * @param string $email
   * @return boolean $uid
   */
  public function isValidEmail($email)
  {
  	global $db;
  	$result = $db->query('SELECT uid FROM users WHERE email = "' . $db->escape($email) . '"');
    $uid = $result->one();
    return $uid;
  }

  /**
   * 产生用户散列密码
   * @param string $string 密码明文
   * @return string 散列密码
   */
  private function generatePassword($string)
  {
    return md5($string);
  }

  /**
   * 验证用户名密码
   * @param string $username 用户名
   * @param string $password 密码
   * @param boole  $is_email 是否为邮箱
   * @return int 用户ID
   */
  public function validate($name, $passwd, $is_email = FALSE)
  {
    global $db;
    $field_name = $is_email ? 'email' : 'name';
    $result = $db->query('SELECT uid FROM `users` WHERE `uid` > 0 AND `'. $field_name .'` = "' . $db->escape($name) .
      '" AND `passwd` = "' . $db->escape($this->generatePassword($passwd)) . '"');
    return $result->one();
  }

  /**
   * 检查用户是否已登录
   * @return boolean
   */
  public function logged()
  {
    global $user;
    return (boolean) $user->uid;
  }

  /**
   * 设置用户登出
   */
  public function setLogout()
  {
    global $user;
    $uid = $user->uid;
    if ($uid) {
      //per requirements, not delete the shopping cart information when a user logout.
      $user->uid = 0;
      session_destroy();
      callFunction('logout', $uid);
    }
  }
  
  public function getWholesaleUserList($filter = null, $page = 1, $rowNumber = 30)
  {
  	global $db;
  	
  	$db->select('wholesale_user.*, sites.name as site_name');
  	$db->join('sites', 'wholesale_user.sid = sites.id');
  	$db->orderby('wholesale_user.created desc');
  	$db->limitPage($rowNumber, $page);
  	$ret = $db->get('wholesale_user');
  	
  	return $ret->all();
  }
  
  public function getWholesaleUserCount() {
  	global $db;
  	 
  	$db->select('count(sid)');
  	$ret = $db->get('wholesale_user');
  	 
  	return $ret->one();
  }
  public function getMaxWholesaleUserIdBySite() {
  	global $db;
  	$db->select('sid, max(remoteid) as remoteid');
  	$db->groupby('sid');
  	$result = $db->get('wholesale_user');
  	if ($result) {
  		return $result->allWithKey('sid');
  	}
  	return false;
  }
  
  public function insertWholesaleUser($wholesaleUser){
  	global $db;
  	$db->insert('wholesale_user', $wholesaleUser);
  }
}