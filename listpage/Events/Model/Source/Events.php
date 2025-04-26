<?php
/**
 * INSEAD Events Source Model
 *
 * @category  Insead
 * @package   Insead\Events
 */
namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;

class Events implements OptionSourceInterface
{
    /**
     * @var EventCollectionFactory
     */
    protected $eventCollectionFactory;
    
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @param EventCollectionFactory $eventCollectionFactory
     */
    public function __construct(
        EventCollectionFactory $eventCollectionFactory
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
    }
    
    /**
     * Get options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            
            $collection = $this->eventCollectionFactory->create();
            // Add active filter if needed
            // $collection->addFieldToFilter('status', 1);
            
            foreach ($collection as $event) {
                $this->options[] = [
                    'value' => $event->getId(),
                    'label' => $event->getEventTitle()
                ];
            }
        }
        
        return $this->options;
    }
}