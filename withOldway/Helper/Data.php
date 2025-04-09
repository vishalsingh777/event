<?php
namespace Vishal\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get event URL
     *
     * @param string $urlKey
     * @return string
     */
    public function getEventUrl($urlKey)
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'events/' . $urlKey;
    }

    /**
     * Format date
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDate($date, $format = 'M d, Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Format time
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatTime($date, $format = 'h:i A')
    {
        return date($format, strtotime($date));
    }
}