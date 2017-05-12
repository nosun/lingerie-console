<?php
class User_Controller extends MD_Controller
{
  public function loginAction()
  {
    global $user;
    $sid = $user->sid;
    $userInstance = User_Model::getInstance();
    if ($userInstance->logged()) {
      if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]){
        header("Location: ".$_SERVER["HTTP_REFERER"]);
      }else{
        gotoUrl('');
      }
    }
    if ($this->isPost()) {
      if (!isset($_POST['username']) || strlen(trim($_POST['username'])) < 3) {
        //setMessage(t('Username must be at least 3 characters long.'), 'error');
        //gotoUrl('user/login');
        //TODO use ajax for the call.
        
      }
      //test whether the given username is an email address.
      $isEmail = $userInstance->isValidEmail($_POST['email']);
      if (!$uid = $userInstance->validate(trim($_POST['email']), $_POST['passwd'], $isEmail)) {
        //setMessage(t('Username or Password is invalid'), 'error');
        //gotoUrl('/');
        //TODO use ajax for the call.
        
      } else {
        $user = $userInstance->getUserInfo($uid);
        //gotoUrl('product');
        if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]){
        	header("Location: ".$_SERVER["HTTP_REFERER"]);
        }else{
        	gotoUrl('');
        }
      }
    } else {
      //store the referrer url in a session variable.
      $this->view->render('index.tpl');
    }
  }

  public function logoutAction()
  {
    $userInstance = User_Model::getInstance();
    if ($userInstance->logged()) {
      $userInstance->setLogout();
    }
    gotoUrl('');
  }
  
  public function wholesaleAction($page = 1)
  {
  	$userInstance = User_Model::getInstance();
  	$userCount = $userInstance->getWholesaleUserCount();
  	$rowCount = 30;
  	
  	$wholesaleUserList = $userInstance->getWholesaleUserList(null, $page);
  	$this->view->assign('pagination', pagination('user/wholesale/%d', ceil($userCount / $rowCount), $page));
  	$this->view->render('user/wholesale_user.tpl', array('pageLabel' => 'wholesale_user',
  			'wholesaleUserList' => $wholesaleUserList));
  }
  
  public function syncWholesaleUserAction() {
  	$sites = Site_Model::getInstance()->getAllSites();
  	$userInstance = User_Model::getInstance();
  	$maxWholesaleUserId = $userInstance->getMaxWholesaleUserIdBySite();
  	$communicationInstance = Communication_Model::getInstance();
  	foreach($sites as $index => $site){
  		if (!in_array($site->name, array('lingeriemore.com', 'cheap-lingerie.com', 'lingerieinterest.com'))) {
  			continue;
  		}
  		$startId = 0;  		
  		if (isset($maxWholesaleUserId) && isset($maxWholesaleUserId[$site->id])) {
  			$startId = $maxWholesaleUserId[$site->id]->remoteid;
  		}
  		$wholesaleUserList = $communicationInstance->syncWholesaleUsers($startId, $site);
  		foreach ($wholesaleUserList as $wholesaleUser) {
  			$wholesaleUser->sid = $site->id;
  			$wholesaleUser->remoteid = $wholesaleUser->id;
  			unset($wholesaleUser->id);
  			$userInstance->insertWholesaleUser($wholesaleUser);
  		}
  	}
  	gotoUrl('user/wholesale');
  }
}