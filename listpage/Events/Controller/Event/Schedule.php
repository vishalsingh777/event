<?php
namespace Insead\Events\Controller\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Insead\Events\Api\EventRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Schedule implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param EventRepositoryInterface $eventRepository
     * @param DateTime $dateTime
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        EventRepositoryInterface $eventRepository,
        DateTime $dateTime
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->eventRepository = $eventRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * Execute action to provide event schedule data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $eventId = $this->request->getParam('event_id');
        
        try {
            $event = $this->eventRepository->getById($eventId);
            
            $scheduleData = $this->getScheduleData($event);
            
            return $resultJson->setData([
                'success' => true,
                'data' => $scheduleData
            ]);
        } catch (NoSuchEntityException $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Event not found.')
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred while loading the schedule.')
            ]);
        }
    }

    /**
     * Get schedule data for the event
     *
     * @param \Insead\Events\Api\Data\EventInterface $event
     * @return array
     */
    private function getScheduleData($event)
    {
        $data = [
            'availableDays' => $this->formatAvailableDays($event->getAvailableDays()),
            'timeSlots' => $event->getTimeSlots(),
            'blockDates' => $event->getBlockDates(),
            'isRecurring' => (bool)$event->getRecurring(),
            'recurrenceType' => $event->getRepeat(),
            'repeatEvery' => (int)$event->getRepeatEvery()
        ];
        
        // If recurring, generate dates based on recurrence pattern
        if ($event->getRecurring()) {
            $data['generatedDates'] = $this->generateRecurringDates($event);
        }
        
        return $data;
    }

    /**
     * Format available days to map to JavaScript object
     *
     * @param array $availableDays
     * @return array
     */
    private function formatAvailableDays($availableDays)
    {
        $daysMap = [
            '0' => 'sunday',
            '1' => 'monday',
            '2' => 'tuesday',
            '3' => 'wednesday',
            '4' => 'thursday',
            '5' => 'friday',
            '6' => 'saturday'
        ];
        
        $formattedDays = [];
        foreach ($daysMap as $dayNumber => $dayName) {
            $formattedDays[$dayName] = in_array($dayNumber, $availableDays);
        }
        
        return $formattedDays;
    }

    /**
     * Generate recurring dates based on event recurrence settings
     *
     * @param \Insead\Events\Api\Data\EventInterface $event
     * @return array
     */
    private function generateRecurringDates($event)
    {
        $startDate = new \DateTime($event->getStartDate());
        $endDate = null;
        
        if ($event->getEndDate()) {
            $endDate = new \DateTime($event->getEndDate());
        } else {
            // If no end date, generate dates for 3 months
            $endDate = clone $startDate;
            $endDate->modify('+3 months');
        }
        
        $dates = [];
        $currentDate = clone $startDate;
        $repeatType = $event->getRepeat();
        $repeatEvery = max(1, (int)$event->getRepeatEvery());
        $availableDays = $event->getAvailableDays();
        
        while ($currentDate <= $endDate) {
            // Skip if date is in blocked dates
            $dateStr = $currentDate->format('Y-m-d');
            if (in_array($dateStr, $event->getBlockDates())) {
                $this->advanceDate($currentDate, $repeatType, $repeatEvery);
                continue;
            }
            
            // For weekly recurrence, check if the day of week is available
            if ($repeatType === 'weekly') {
                $dayOfWeek = $currentDate->format('w'); // 0 (Sunday) to 6 (Saturday)
                if (!in_array($dayOfWeek, $availableDays)) {
                    $currentDate->modify('+1 day');
                    continue;
                }
            }
            
            $dates[] = $dateStr;
            
            // Advance to next recurrence date
            $this->advanceDate($currentDate, $repeatType, $repeatEvery);
        }
        
        return $dates;
    }

    /**
     * Advance date based on recurrence type
     *
     * @param \DateTime $date
     * @param string $repeatType
     * @param int $repeatEvery
     */
    private function advanceDate(\DateTime $date, $repeatType, $repeatEvery)
    {
        switch ($repeatType) {
            case 'daily':
                $date->modify("+{$repeatEvery} day");
                break;
            case 'weekly':
                $date->modify("+1 day");
                break;
            case 'monthly':
                $date->modify("+{$repeatEvery} month");
                break;
            case 'yearly':
                $date->modify("+{$repeatEvery} year");
                break;
            default:
                $date->modify('+1 day');
        }
    }
}