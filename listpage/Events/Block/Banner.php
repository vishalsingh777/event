<?php
/**
 * INSEAD Events Banner Block
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);
namespace Insead\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Insead\Events\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Insead\Events\Model\Banner as BannerModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Banner extends Template
{
    /**
     * @var BannerCollectionFactory
     */
    protected $bannerCollectionFactory;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param Context $context
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        BannerCollectionFactory $bannerCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    /**
     * Get banners for listing page
     *
     * @return \Insead\Events\Model\ResourceModel\Banner\Collection
     */
    public function getListingBanners()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->bannerCollectionFactory->create();
        $collection->addFieldToFilter('status', BannerModel::STATUS_ENABLED)
            ->addFieldToFilter('banner_type', BannerModel::TYPE_LISTING)
          //  ->addFilter('store_ids', ['finset' => $storeId])
            ->setOrder('position', 'ASC');
        return $collection;
    }
    
    /**
     * Get banners for event detail page
     *
     * @param int $eventId
     * @return \Insead\Events\Model\ResourceModel\Banner\Collection
     */
    public function getEventBanners($eventId = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->bannerCollectionFactory->create();
        $collection->addFieldToFilter('status', BannerModel::STATUS_ENABLED)
            ->addFieldToFilter('banner_type', BannerModel::TYPE_EVENT)
            ->addFilter('store_ids', ['finset' => $storeId])
            ->setOrder('position', 'ASC');
        
        if ($eventId) {
            $collection->addFilter('event_ids', ['finset' => $eventId]);
        }
        
        return $collection;
    }
    
    /**
     * Get media URL for banner image
     *
     * @param string $imagePath
     * @return string
     */
    public function getBannerImageUrl($imagePath)
    {
        if (!$imagePath) {
            return '';
        }
        
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'insead/events/banner/' . ltrim($imagePath, '/');
    }
    
    /**
     * Check if banner content is a video
     *
     * @param string $content
     * @return bool
     */
    public function isVideo($content)
{
    if (empty($content)) {
        return false;
    }
    
    $videoExtensions = ['mp4', 'webm', 'ogg'];
    $extension = pathinfo($content, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $videoExtensions);
}
    
    /**
     * Get video URL for banner
     *
     * @param string $videoPath
     * @return string
     */
    public function getBannerVideoUrl($videoPath)
    {
        if (!$videoPath) {
            return '';
        }
        
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'insead/events/banner/' . ltrim($videoPath, '/');
    }

    /**
     * Get hero configuration from system config
     * 
     * @return array
     */
    public function getHeroConfig()
    {
        return [
            'title' => $this->getConfigValue('insead_events/general/hero_title') ?: 'INSEAD Events & Programmes',
            'subtitle' => $this->getConfigValue('insead_events/general/hero_subtitle') ?: 'Connect, Learn, and Grow with the INSEAD Community',
            'show_search' => (bool)$this->getConfigValue('insead_events/display/show_hero_search'),
            'background_image' => $this->getConfigValue('insead_events/general/hero_background')
        ];
    }

    /**
     * Get config value
     *
     * @param string $path
     * @return mixed
     */
    protected function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if hero banner should be shown
     *
     * @return bool
     */
    public function shouldShowHero()
    {
        return (bool)$this->getConfigValue('insead_events/display/show_hero');
    }

    /**
     * Get hero background image URL
     *
     * @return string|null
     */
    public function getHeroBackgroundUrl()
    {
        $image = $this->getConfigValue('insead_events/general/hero_background');
        if ($image) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . 'insead_events/hero/' . $image;
        }
        return null;
    }
}