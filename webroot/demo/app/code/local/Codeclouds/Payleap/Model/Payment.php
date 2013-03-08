<?php
/**
 * Payleap Payment Model
 *
 * @category    Codeclouds
 * @package     Codeclouds_Payleap
 * @author      Somnath Sinha <som@codeclouds.com>
 */
class Codeclouds_Payleap_Model_Payment extends Mage_Payment_Model_Method_Cc
{
    protected $_code  = 'payleap';
	
	const PAYLEAP_API_URL = 'https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard';
	const PAYLEAP_TRANS_TYPE_SALE = 'Sale';
	const PAYLEAP_TRANS_TYPE_AUTH = 'Auth';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc				= false;
	protected $_isInitializeNeeded      = false;
	
	protected $_allowCurrencyCode = array('USD');
	
	protected $_formBlockType = 'payleap/form_payform';
	
	private $errorDetails;
	
	/**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_allowCurrencyCode)) {
            return false;
        }
        return true;
    }
	
	private function getApiUrl()
    {
        if (!$this->getConfigData('gateway_url')) {
            return self::PAYLEAP_API_URL;
        }
        return $this->getConfigData('gateway_url');
    }
	private function getHeaders()
    {
        return array("MIME-Version: 1.0", "Content-type: application/x-www-form-urlencoded", "Contenttransfer-encoding: text");
    }
	private function getNVPArray(Varien_Object $payment, $method)
    {
		$billing = $payment->getOrder()->getBillingAddress();
		$args = array();
		$args['UserName'] = Mage::helper('core')->decrypt($this->getConfigData('user_name'));
		$args['Password'] = Mage::helper('core')->decrypt($this->getConfigData('password'));
		$args['TransType'] = $method;
		$args['CardNum'] = $payment->getCcNumber();
		$args['ExpDate'] = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));
		$args['MagData'] = '';
		$args['NameOnCard'] = $payment->getCcOwner();
		$args['Amount'] = $payment->getAmount();
		$args['InvNum'] = '';
		$args['PNRef'] = '';
		$args['Zip'] = $billing->getPostcode();
		$args['Street'] = $billing->getStreet(1);
		$args['CVNum'] = $payment->getCcCid();
		$args['ExtData'] = '<TrainingMode>F</TrainingMode>';
		
        return $args;
    }
	private function getNVPRequest(Varien_Object $payment, $method)
    {
		$nvpArray = $this->getNVPArray($payment, $method);
		$nvp = '';
		foreach ($nvpArray as $k => $v) {
			$nvp .= $k . '=' . urlencode($v) . '&';
		}
		$nvp = rtrim($nvp, '&');
        return $nvp;
    }
	
	private function processPayment($npvStr) {
		$header = $this->getHeaders();
		/*Mage::throwException($this->getApiUrl());
		die;*/
		$http = new Varien_Http_Adapter_Curl();
        $config = array('timeout' => 30);
		/*if ($this->getUseProxy()) {
            $config['proxy'] = $this->getProxyHost(). ':' . $this->getProxyPort();
        }*/
        $http->setConfig($config);
        $http->write(Zend_Http_Client::POST, $this->getApiUrl(), '1.1', $header, $npvStr);
        $response = $http->read();

        if ($http->getErrno()) {
            $http->close();
            $this->errorDetails = array(
				'type' => 'CURL',
				'code' => $http->getErrno(),
				'message' => $http->getError()
			);
			return false;
        }
        $http->close();
		
		$response = preg_split('/^\r?$/m', $response, 2);
		$response = trim($response[1]);
		
		return($response);
	}
	
	/**
     * this method is called if we are just authorising
     * a transaction
     */
    public function authorize (Varien_Object $payment, $amount)
    {
		$error = false;
		$payment->setAmount($amount);
		$nvpStr = $this->getNVPRequest($payment, self::PAYLEAP_TRANS_TYPE_AUTH);
		$response = $this->processPayment($nvpStr);
		
		if (!$response) {
			$error = Mage::helper('payleap')->__('Gateway request error: %s', $this->errorDetails['message']);
		}
		else {
			//$error = $response . $this->getApiUrl();
			//Mage::throwException($error);
			//exit;
			$result = @simplexml_load_string($response);
			
			if (!$result) {
				$error = Mage::helper('payleap')->__('Cannot process your payment. Please try again.');
			}
			elseif ($result->{'Result'} != '0') {
				$error = Mage::helper('payleap')->__('Payment Error: %s', $result->{'Message'});
			}
		}
		if ($error !== false) {
            Mage::throwException($error);
        }
		else {
			$payment->setCcApproval($result->{'AuthCode'})
                ->setLastTransId($result->{'PNRef'})
                ->setCcTransId($result->{'PNRef'})
                ->setCcAvsStatus($result->{'GetAVSResult'})
                ->setCcCidStatus($result->{'AuthCode'})
				->setStatus(self::STATUS_APPROVED);
		}
		
		return $this;
    }

    /**
     * this method is called if we are authorising AND
     * capturing a transaction
     */
    public function capture (Varien_Object $payment, $amount)
    {
		$error = false;
		$payment->setAmount($amount);
		$nvpStr = $this->getNVPRequest($payment, self::PAYLEAP_TRANS_TYPE_SALE);
		
		$response = $this->processPayment($nvpStr);
		
		if (!$response) {
			$error = Mage::helper('payleap')->__('Gateway request error: %s', $this->errorDetails['message']);
		}
		else {
			$result = @simplexml_load_string($response);
			
			if (!$result) {
				$error = Mage::helper('payleap')->__('Cannot process your payment. Please try again.');
			}
			elseif ($result->{'Result'} != '0') {
				$error = Mage::helper('payleap')->__("Payment Error: %s", $result->{'RespMSG'}, $response);
			}
		}
		if ($error !== false) {
            Mage::throwException($error);
        }
		else {
			$payment->setStatus(self::STATUS_APPROVED);
			$payment->setLastTransId($result->{'PNRef'});
		}
		
		return $this;
    }

    /**
     * called if refunding
     */
    public function refund (Varien_Object $payment, $amount)
    {
		Mage::throwException("Refund not Supported.");
		return $this;
    }

    /**
     * called if voiding a payment
     */
    public function void (Varien_Object $payment)
    {
		Mage::throwException("Not Supported.");
		return $this;
    }
}