<?php
namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\EventRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class EventsList implements OptionSourceInterface
{
    /**
     * @var EventRepository
     */
    private $eventRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array
     */
    private $options;

    /**
     * @param EventRepository $eventRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventRepository $eventRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            
            try {
                $searchCriteria = $this->searchCriteriaBuilder->create();
                $eventsResult = $this->eventRepository->getList($searchCriteria);
                
                if (method_exists($eventsResult, 'getItems')) {
                    $events = $eventsResult->getItems();
                } else {
                    $events = $eventsResult;
                }
                
                foreach ($events as $event) {
                    $this->options[] = [
                        'value' => $event->getId(),
                        'label' => $event->getData('event_title')
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->error('Error in EventsList: ' . $e->getMessage());
            }
        }
        
        return $this->options;
    }
}