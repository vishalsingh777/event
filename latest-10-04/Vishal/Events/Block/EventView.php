<?php
namespace Vishal\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Vishal\Events\Helper\Data as EventHelper;

class EventView extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @param Context $context
     * @param Registry $registry
     * @param EventHelper $eventHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EventHelper $eventHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eventHelper = $eventHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('current_event');
    }
    
    /**
     * Format date
     *
     * @param string|null $date
     * @param int $format
     * @param bool $showTime
     * @param string|null $timezone
     * @return string
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false, $timezone = null)
    {
        if ($date !== null) {
            return $this->eventHelper->formatDate($date);
        }
        return parent::formatDate($date, $format, $showTime, $timezone);
    }
    
    /**
     * Format time
     *
     * @param string $date
     * @return string
     */
    public function formatEventTime($date)
    {
        return $this->eventHelper->formatTime($date);
    }
    
    /**
     * Get add to cart URL
     *
     * @param int $productSku
     * @return string
     */
    public function getAddToCartUrl($productSku)
    {
        return $this->getUrl('events/product/addtocart', [
            'product_sku' => $productSku
        ]);
    }
    
    /**
     * Process content HTML
     *
     * @param string $content
     * @return string
     */
    public function getContentHtml($content)
    {
        // This method allows WYSIWYG editor content to be properly displayed
        // You may want to implement this in your EventHelper class if you have more complex HTML processing
        $allowedTags = ['div', 'p', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 
                        'ul', 'ol', 'li', 'a', 'img', 'br', 'strong', 'b', 'em', 'i'];
        
        // If you have PageBuilder or other WYSIWYG editors, you may need to process the content differently
        return $content;
    }
}