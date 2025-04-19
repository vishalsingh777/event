<?php

namespace Insead\Stripe\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const XML_PATH_STRIPE_MODE = 'payment/stripe_payments_basic/stripe_mode';

    const XML_PATH_STRIPE_API_KEY_LIVE = 'payment/stripe_payments_basic/stripe_live_sk';

    const XML_PATH_STRIPE_API_KEY_TEST = 'payment/stripe_payments_basic/stripe_test_sk';
    
    const XML_PATH_STRIPE_WEBHOOK_SECRET = 'payment/stripe_payments/subscriptions/signing_secret_key';

    const XML_PATH_CANCEL_SUBSCRIPTION_DAYS = 'payment/stripe_payments/subscriptions/cancel_subscription_days';

    protected $scopeConfig;
	protected $encryptor;

    public function __construct(
    ScopeConfigInterface $scopeConfig,
    EncryptorInterface $encryptor
	) {
	    $this->scopeConfig = $scopeConfig;
	    $this->encryptor = $encryptor;
	}

	public function getScopeConfigValue($filedName,$storeCode = null) {
		return $this->scopeConfig->getValue($filedName, ScopeInterface::SCOPE_STORE,
            $storeCode);
	}

	public function getStripeMode($storeCode = null)
    {
    	return $this->getScopeConfigValue(self::XML_PATH_STRIPE_MODE,$storeCode);
    }

    public function getStripeLiveSecretKey($storeCode = null)
    {
        $encryptedValue = $this->getScopeConfigValue(self::XML_PATH_STRIPE_API_KEY_LIVE,$storeCode);
        return $this->getDecryptConfigValues($encryptedValue);
    }

    public function getStripeTestSecretKey($storeCode = null)
    {
        $encryptedValue = $this->getScopeConfigValue(self::XML_PATH_STRIPE_API_KEY_TEST,$storeCode);
        return $this->getDecryptConfigValues($encryptedValue);
    }

    public function getDecryptConfigValues($encryptedValue) 
    {
		$decryptedValue = $this->encryptor->decrypt($encryptedValue);
	    return $decryptedValue;
	}

    public function getWebhookSecret($storeCode = null)
    {
       return $this->getScopeConfigValue(self::XML_PATH_STRIPE_WEBHOOK_SECRET,$storeCode);
    }

    public function getCancelSubscriptionDays($storeCode = null)
    {
      return $this->getScopeConfigValue(self::XML_PATH_CANCEL_SUBSCRIPTION_DAYS,$storeCode);
    }
}
