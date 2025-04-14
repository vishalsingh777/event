<?php
namespace Vishal\Events\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vishal\Events\Model\EventRepository;

class Timeslots extends Action
{
    protected $resultJsonFactory;
    protected $eventRepository;
    
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventRepository $eventRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventRepository = $eventRepository;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $eventId = $this->getRequest()->getParam('event_id');
            $selectedDate = $this->getRequest()->getParam('selected_date');
            
            if (!$eventId || !$selectedDate) {
                throw new \Exception('Missing required parameters');
            }
            
            $event = $this->eventRepository->getById($eventId);
            
            // Get time slots for the event
            $timeSlots = $this->getFormattedTimeSlotsForDate($event, $selectedDate);
            
            return $result->setData([
                'success' => true,
                'slots' => $timeSlots
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get formatted time slots for a specific date
     *
     * @param Event $event
     * @param string $selectedDate
     * @return array
     */
    private function getFormattedTimeSlotsForDate($event, $selectedDate)
    {
        // This is where you'd implement your logic to get time slots
        // specific to the selected date
        
        // Example implementation:
        $rawTimeSlots = $event->getTimeSlots(); // Your method to get raw time slots
        $timezone = $event->getEventTimezone() ?: 'UTC';
        $formattedDate = date('M d, Y', strtotime($selectedDate));
        
        $formattedSlots = [];
        foreach ($rawTimeSlots as $index => $slot) {
            if (isset($slot['time_start']) && isset($slot['time_end'])) {
                $startTime = $this->formatTime($slot['time_start']);
                $endTime = $this->formatTime($slot['time_end']);
                
                $formattedSlots[] = [
                    'index' => $index,
                    'time_start' => $slot['time_start'],
                    'time_end' => $slot['time_end'],
                    'formatted' => $formattedDate . ' · ' . $startTime . ' - ' . $endTime . ' ' . $timezone,
                    'date' => $selectedDate
                ];
            }
        }
        
        return $formattedSlots;
    }
    
    /**
     * Format time in 12-hour format
     *
     * @param string $time
     * @return string
     */
    private function formatTime($time)
    {
        if (!$time) return '';
        
        // Parse time components
        $parts = explode(':', $time);
        $hours = (int)$parts[0];
        $minutes = $parts[1] ?? '00';
        $ampm = $hours >= 12 ? 'PM' : 'AM';
        
        // Convert to 12-hour format
        $hours = $hours % 12;
        $hours = $hours ? $hours : 12; // Handle midnight (0:00)
        
        return $hours . ':' . $minutes . ' ' . $ampm;
    }
}