<?php
final class Error_Controller extends MD_Controller
{
  public function errorAction($ex = null)
  {
    if (isset($ex) && $ex instanceof Exception) {
      if ($ex instanceof MD_404_Exception) {
        $this->_404($ex);
      } else if ($ex instanceof MD_403_Exception) {
        $this->_403($ex);
      } else if ($ex instanceof MD_Db_Exception) {
        $this->_db($ex);
      } else {
        $this->_general($ex);
      }
    } else {
      $this->_404(new MD_404_Exception('Argument is invalid.'));
    }
  }

  private function _404(MD_404_Exception $ex)
  {
    callFunction('error_404', $this);
    $this->view->render('error/404.tpl', array(
      'ex' => $ex,
      'debug' => MD_Config::get('debug', false),
      'timer' => timer(),
    ));
  }

  private function _403(MD_403_Exception $ex)
  {
    callFunction('error_403', $this);
    $this->view->render('error/403.tpl', array(
      'ex' => $ex,
      'debug' => MD_Config::get('debug', false),
      'timer' => timer(),
    ));
  }

  private function _db(MD_Db_Exception $ex)
  {
    callFunction('error_db', $this);
    $this->view->render('error/db.tpl', array(
      'ex' => $ex,
      'debug' => MD_Config::get('debug', false),
      'timer' => timer(),
    ));
  }

  private function _general(Exception $ex)
  {
    callFunction('error_general', $this);
    $this->view->render('error/general.tpl', array(
      'ex' => $ex,
      'debug' => MD_Config::get('debug', false),
      'timer' => timer(),
    ));
  }
}
