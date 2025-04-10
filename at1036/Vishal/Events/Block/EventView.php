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
     * Get recurrence text description
     *
     * @param \Vishal\Events\Model\Event $event
     * @return string
     */
    public function getRecurrenceText($event)
    {
        if (!$event->getRecurring()) {
            return '';
        }
        
        $repeatType = $event->getRepeat();
        $repeatEvery = (int)$event->getRepeatEvery() ?: 1;
        
        switch ($repeatType) {
            case 'daily':
                return $repeatEvery === 1 
                    ? __('Every day') 
                    : __('Every %1 days', $repeatEvery);
                
            case 'weekly':
                $availableDays = $event->getAvailableDays();
                $dayNames = $this->getDayNames($availableDays);
                
                if (count($dayNames) === 7) {
                    return $repeatEvery === 1 
                        ? __('Every week') 
                        : __('Every %1 weeks', $repeatEvery);
                } else {
                    $daysText = implode(', ', $dayNames);
                    return $repeatEvery === 1 
                        ? __('Weekly on %1', $daysText) 
                        : __('Every %1 weeks on %2', $repeatEvery, $daysText);
                }
                
            case 'monthly':
                return $repeatEvery === 1 
                    ? __('Every month') 
                    : __('Every %1 months', $repeatEvery);
                
            case 'yearly':
                return $repeatEvery === 1 
                    ? __('Every year') 
                    : __('Every %1 years', $repeatEvery);
                
            default:
                return __('Recurring');
        }
    }
    
    /**
     * Get day names from day numbers
     *
     * @param array $dayNumbers
     * @return array
     */
    private function getDayNames($dayNumbers)
    {
        $dayMap = [
            '0' => __('Sunday'),
            '1' => __('Monday'),
            '2' => __('Tuesday'),
            '3' => __('Wednesday'),
            '4' => __('Thursday'),
            '5' => __('Friday'),
            '6' => __('Saturday')
        ];
        
        $names = [];
        foreach ($dayNumbers as $dayNumber) {
            if (isset($dayMap[$dayNumber])) {
                $names[] = $dayMap[$dayNumber];
            }
        }
        
        return $names;
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