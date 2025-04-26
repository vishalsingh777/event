<?php
namespace Insead\Events\Controller\Calendar;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory;
use Insead\Events\Helper\Data as EventHelper;
use Insead\Events\ViewModel\EventsViewModel;

class DayEvents extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;
    
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @var EventsViewModel
     */
    protected $eventsViewModel;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $eventCollectionFactory
     * @param EventHelper $eventHelper
     * @param EventsViewModel $eventsViewModel
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        CollectionFactory $eventCollectionFactory,
        EventHelper $eventHelper,
        EventsViewModel $eventsViewModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->eventHelper = $eventHelper;
        $this->eventsViewModel = $eventsViewModel;
        parent::__construct($context);
    }

    /**
     * Get events for a specific day (AJAX request)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        $date = $this->getRequest()->getParam('date');
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Get events for the specified date
        $events = $this->eventsViewModel->getEventsByDate($date);
        
        // Format date for display
        $formattedDate = $this->eventHelper->formatDate($date, 'F d, Y');
        
        // Generate HTML for events
        $html = '';
        
        if ($events && $events->getSize() > 0) {
            foreach ($events as $event) {
                $startTime = $this->eventHelper->formatTime($event->getStartDate());
                
                // Get category info
                $categoryClass = 'default';
                $categories = $this->eventsViewModel->getCategories();
                if ($event->getCategoryId() && isset($categories[$event->getCategoryId()])) {
                    $categoryClass = $categories[$event->getCategoryId()]['code'];
                }
                
                // Location info
                $locationIcon = $event->getEventVenue() ? 'location_on' : 'videocam';
                $locationText = $event->getEventVenue() ?: 'Online Event';
                
                // Generate HTML for a single event
                $html .= '<div class="calendar-event-item">';
                $html .= '<span class="event-time">' . $startTime . '</span>';
                $html .= '<div class="event-info">';
                $html .= '<h5 class="event-name"><a href="' . $this->eventsViewModel->getEventUrl($event) . '">' . 
                    $event->getEventTitle() . '</a></h5>';
                $html .= '<span class="event-location"><i class="material-icons">' . $locationIcon . '</i> ' . 
                    $locationText . '</span>';
                $html .= '</div>';
                $html .= '<span class="event-category-indicator ' . $categoryClass . '"></span>';
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="no-events-message"><p>No events scheduled for this day.</p></div>';
        }
        
        return $result->setData([
            'success' => true,
            'date' => $date,
            'formattedDate' => $formattedDate,
            'html' => $html,
            'count' => $events ? $events->getSize() : 0
        ]);
    }
}