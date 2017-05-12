<?php

class Globebill {
    private $merNo = "10619";
    private $gatewayNo = "10619001";
    private $signKey = "6t82R60P";

    private function sendRequest($url, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $tmpInfo;
    }

    private function getOrderInfo($orderNumber) {
        $url = 'https://check.globebill.com/servlet/NormalCustomerCheck';
        $signInfo = hash('sha256', $this->merNo . $this->gatewayNo . $this->signKey);
        $postData = array(
                        "merNo=$this->merNo",
                        "gatewayNo=$this->gatewayNo",
                        "orderNo=$orderNumber",
                        "signInfo=$signInfo");
        $xmlResult = simplexml_load_string($this->sendRequest($url, implode($postData, '&')))->tradeinfo;
        $propList = get_object_vars($xmlResult);
        $orderInfo = new stdClass();
        foreach ($propList as $prop => $value) {
            $orderInfo->{$prop} = (string)$value;
        }
        return $orderInfo;
    }
    
    private function getTrackingWebsite($shippingMethod) {
        $shippingMethod = strtolower($shippingMethod);
        switch ($shippingMethod) {
        	case "dhl":
        	    return "www.dhl.com";
        	case "ups":
        	    return "wwwapps.ups.com";
        	case "epacket":
        	    return "tools.usps.com";
        	case "中邮小包":
        	    return "intmail.11185.cn";
        	case "tnt":
        	    return "www.tnt.com";
        	case "日本专线":
        	    return "www.zce-exp.com";
        	case "南非专线":
      	        return "www.tollgroup.com";
            case "dpd":
              	return "www.dpd.co.uk";
      	    case "aramex":
        	    return "www.aramex.com";
      	    case "顺丰":
        	    return "www.sf-express.com";
      	    case "fedex":
        	    return "www.fedex.com";
      	    case "ems":
              	return "www.ems.com.cn";
            default:
              	return "";
        }
    }

    private function uploadTrackingNumberToGlobebill($tradeNumber, $trackingNumber, $shippingMethod, &$error) {
        $trackingWebsite = $this->getTrackingWebsite($shippingMethod);
        $url = 'https://check.globebill.com/servlet/UploadTrackingNo';
        $signInfo = hash('sha256', $this->merNo . $this->gatewayNo . $tradeNumber . $this->signKey);
        $postData = array(
                        "merNo=$this->merNo",
                        "gatewayNo=$this->gatewayNo",
                        "tradeNo=$tradeNumber",
                        "signInfo=$signInfo",
                        "trackingNo=$trackingNumber",
                        "trackingWeb=$trackingWebsite",
                        "handler=heliangdong",
        );
    
        $xmlResult = simplexml_load_string($this->sendRequest($url, implode($postData, '&')))->queryRefund;
        if ($xmlResult->code == "01") {
            return true;
        }
        $error = (string)$xmlResult->description;
        return false;
    }

    public function uploadTrackingNumber($OrderNumber, $trackingNumber, $shippingMethod, &$error) {
        $orderInfo = $this->getOrderInfo($OrderNumber);
        $error = '';
        if (empty($orderInfo->tradeNo) || $this->uploadTrackingNumberToGlobebill($orderInfo->tradeNo, $trackingNumber, $shippingMethod, $error)) {
            return true;
        } else {
            return false;
        }
    }
}