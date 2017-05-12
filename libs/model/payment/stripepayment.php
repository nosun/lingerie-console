<?php
class Payment_Stripepayment_Model extends Payment_API_Model
{	
	/*
	private static $_SECRET_KEY = "sk_live_kxZTXDfvIxg10W0JPfEmKg78 ";
	private static $_PUBLISHIBLE_KEY = "pk_live_BQJqxncorUZCrjWxA3bQJtKC";
	*/
	
	private static $_SECRET_KEY = "sk_test_BYBYAkZMiXTamqlJJdds7pSl";
	private static $_PUBLISHIBLE_KEY = "pk_test_ID8u2MTiyF8uux439cNNlech";

   /**
   * @return StripePayment_Model
   */
	public static function getInstance()
	{
		return parent::getInstance(__CLASS__);
	}
	
  public function __construct()
  {
    $this->init();
  }

  public function init(){
  	//stripe need private key and publick key.
  	MD_Core::loadLibrary('thirdparty/stripe/Stripe');
	Stripe::setApiKey(Payment_Stripepayment_Model::$_SECRET_KEY);
  }

  public function charge_refund($oid, $refund_type="fully", $refund_val = 0){
  	$orderInstance = Order_Model::getInstance();
  	$orderInfo = $orderInstance->getOrderById($oid);
  	$extra_data = unserialize($orderInfo->data);
  	$result = array('status'=>'failed', 'refunded_amount'=>0, 'message'=>'Charge Not Found!');
  	if(key_exists('charge_id', $extra_data)){
  		try{
  		$ch = Stripe_Charge::retrieve($extra_data['charge_id']);
  		if($refund_type == 'partial'){
  			$response = $ch->refund(array('amount'=>intval($refund_val * 100)));
  		}else{
  			$response = $ch->refund();
  		}
  		if(isset($response->failure_code)){
  			$result['message'] = $response->failure_message;
  		}else{
  			$result['status'] = 'success';
  			$result['message'] = 'Refund successfully!';
  			$result['refunded_amount'] = $response->amount_refunded / 100;
  		}
  		}catch (Exception $e){
  			$result['message'] = $e->getMessage();
  			return $result;
  		}
  	}
  	return $result;
  }
}