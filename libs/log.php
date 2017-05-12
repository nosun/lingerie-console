<?php
class log
{
  public static function save($type, $message = null, $data = null)
  {
    global $db, $user;
    if (!MD_Config::get('log', true)) {
      return;
    }
    $set = array(
      'type' => $type,
      'uid' => isset($user) ? $user->uid : 0,
      'message' => $message,
      'data' => isset($data) ? (is_array($data) ? serialize($data) : $data) : '',
      'ip' => ipAddress(),
      'uri' => '/' . CURRENTURI,
      'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
      'timestamp' => time(),
    );
    $db->insert('log', $set);
  }
}
