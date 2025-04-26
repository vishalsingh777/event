<?php
namespace Insead\Events\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Insead\Events\ViewModel\EventsViewModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * AJAX Controller for Events Module
 */
class Calendar implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var EventsViewModel
     */
    protected $eventsViewModel;
    
    /**
     * @var Json
     */
    protected $serializer;
    
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;
    
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;
    
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventsViewModel $eventsViewModel
     * @param Json $serializer
     * @param RequestInterface $request
     * @param DecoderInterface $urlDecoder
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventsViewModel $eventsViewModel,
        Json $serializer,
        RequestInterface $request,
        DecoderInterface $urlDecoder,
        FormKeyValidator $formKeyValidator
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventsViewModel = $eventsViewModel;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->urlDecoder = $urlDecoder;
        $this->formKeyValidator = $formKeyValidator;
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $format = $this->request->getParam('format', 'events');
            
            if ($format === 'calendar') {
                // Get calendar data for a specific month
                $month = (int)$this->request->getParam('month', date('m'));
                $year = (int)$this->request->getParam('year', date('Y'));
                
                $calendarData = $this->eventsViewModel->getCalendarData($month, $year);
                
                // Add category information to days with events
                foreach ($calendarData['weeks'] as &$week) {
                    foreach ($week as &$day) {
                        if ($day['events'] > 0) {
                            $dayEvents = $this->eventsViewModel->getEventsByDate($day['date']);
                            $categories = [];
                            
                            foreach ($dayEvents as $event) {
                                if ($event->getCategoryId() && !in_array($event->getCategoryId(), array_column($categories, 'id'))) {
                                    $allCategories = $this->eventsViewModel->getCategories();
                                    if (isset($allCategories[$event->getCategoryId()])) {
                                        $categories[] = [
                                            'id' => $event->getCategoryId(),
                                            'code' => $allCategories[$event->getCategoryId()]['code']
                                        ];
                                    }
                                }
                            }
                            
                            $day['eventCategories'] = array_slice($categories, 0, 3); // Limit to 3 category dots
                        }
                    }
                }
                
                return $result->setData([
                    'success' => true,
                    'calendar' => $calendarData
                ]);
            } else {
                // Get events for a specific date
                $date = $this->request->getParam('date', date('Y-m-d'));
                $events = $this->eventsViewModel->getEventsByDate($date);
                
                $eventData = [];
                foreach ($events as $event) {
                    $startDate = new \DateTime($event->getStartDate());
                    
                    // Get category data
                    $categoryId = $event->getCategoryId();
                    $categories = $this->eventsViewModel->getCategories();
                    $categoryClass = isset($categories[$categoryId]) ? $categories[$categoryId]['code'] : 'default';
                    
                    // Campus/venue handling
                    $locationIcon = $event->getEventVenue() ? 'location_on' : 'videocam';
                    $locationText = $event->getEventVenue() ?: 'Online Event';
                    
                    $eventData[] = [
                        'id' => $event->getId(),
                        'title' => $event->getEventTitle(),
                        'time' => $this->eventsViewModel->formatEventTime($event->getStartDate()) . 
                            ($event->getEndDate() ? ' - ' . $this->eventsViewModel->formatEventTime($event->getEndDate()) : ''),
                        'location' => $locationText,
                        'location_icon' => $locationIcon,
                        'category_id' => $categoryId,
                        'category_class' => $categoryClass,
                        'url' => $this->eventsViewModel->getEventUrl($event)
                    ];
                }
                
                return $result->setData([
                    'success' => true,
                    'date' => $date,
                    'events' => $eventData
                ]);
            }
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}