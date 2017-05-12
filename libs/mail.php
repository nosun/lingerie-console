<?php
class Mail_Model
{
	private $_mail;

  /**
   * @return Mail_Model
   */
  public static function getInstance() {
    static $_instances = array();
    if (empty($_instances)) {
        $_instances[] = new Mail_Model();
    }
    return $_instances[0];
  }
    
  public function __construct() {
  	  require_once LIBPATH.'/thirdparty/class.phpmailer.php';
	  $this->_mail = new PHPMailer();
	  $this->_mail->IsSMTP();
	  $this->_mail->CharSet = "utf-8";
	  $this->_mail->SMTPDebug = 0;
      $this->_mail->SMTPAuth = true;
      $this->_mail->SMTPSecure = "ssl";
  }

  public function sendMail($address, $subject, $content, $isHtml , $userName = '', $senderKey) {
      $mailConfig = MD_Config::get('sitesemail');
      if (isset($mailConfig) && isset($mailConfig[$senderKey])) {
          $configItem = $mailConfig[$senderKey];
          $this->_mail->Host = $configItem['server'];
          $this->_mail->Port = $configItem['port'];
          $this->_mail->Username = $configItem['user'];
          $this->_mail->Password = $configItem['password'];
          $this->_mail->SetFrom($configItem['from'], $configItem['nickname']);
          $this->_mail->AddCC($configItem['reply'], $configItem['nickname']);
          $this->_mail->AddReplyTo($configItem['reply'], $configItem['nickname']);
      } else {
          return false;
      }
      $this->_mail->Subject = $subject;
      if ($isHtml == 'html') {
          $this->_mail->MsgHTML($content);
      } else {
    	  $this->_mail->Body = $content;
      }
      $this->_mail->ClearAddresses();
      $this->_mail->ClearBCCs();
      if (is_array($address)) {
          if (isset($address[0]))
              $this->_mail->AddAddress($address[0], $userName);
          if (isset($address[1]))
              $this->_mail->AddBCC($address[1], $userName);
      } else {
          $this->_mail->AddAddress($address, $userName);
      }
      if(!$this->_mail->Send()) {
          return false;
      } else {
          return true;
      }
  }
}
