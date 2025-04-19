<?php

namespace Insead\Stripe\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Insead\Stripe\Logger\CustomLogger;

/**
 * Class Email
 *
 * @package Insead\Stripe\Helper
 */

class Email extends AbstractHelper
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;    
    protected $scopeConfig;
    protected $storeManager;
    protected $customLogger;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CustomLogger $customLogger
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig; 
        $this->storeManager = $storeManager;
        $this->customLogger = $customLogger;
    }
    
    public function SendEmail($subcriptionData)
    {
        try {
            $this->inlineTranslation->suspend();
            $sender_name = $this->scopeConfig->getValue('general/store_information/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sender_email = $this->scopeConfig->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sender = [
                'name' => $this->escaper->escapeHtml($sender_name),
                'email' => $this->escaper->escapeHtml($sender_email),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('stripe_subscription_complete')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($subcriptionData)
                ->setFrom($sender)
                ->addTo($subcriptionData['customer_name'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->customLogger->error('=== Email send successsfully === ');
            return true;
        } catch (\Exception $e) {            
            $this->customLogger->error('Email have issues: ' .$e->getMessage());
            return false;
        }
    }
}