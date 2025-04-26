<?php
namespace Insead\Events\Controller\Calendar;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Insead\Events\ViewModel\EventsViewModel;

class Navigate extends Action
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
     * @var EventsViewModel
     */
    protected $eventsViewModel;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param EventsViewModel $eventsViewModel
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        EventsViewModel $eventsViewModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->eventsViewModel = $eventsViewModel;
        parent::__construct($context);
    }

    /**
     * Navigate calendar to different month (AJAX request)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        $action = $this->getRequest()->getParam('action');
        $current = $this->getRequest()->getParam('current'); // Format: "April 2025"
        
        // Parse current month and year
        $parts = explode(' ', $current);
        if (count($parts) !== 2) {
            return $result->setData(['success' => false, 'message' => 'Invalid month format']);
        }
        
        $monthName = $parts[0];
        $year = (int)$parts[1];
        
        // Convert month name to number
        $monthNumber = date('m', strtotime($monthName . ' 1, ' . $year));
        
        // Calculate new month and year based on action
        if ($action === 'prev-month') {
            if ($monthNumber == 1) {
                $monthNumber = 12;
                $year--;
            } else {
                $monthNumber--;
            }
        } elseif ($action === 'next-month') {
            if ($monthNumber == 12) {
                $monthNumber = 1;
                $year++;
            } else {
                $monthNumber++;
            }
        }
        
        // Get calendar data for the new month
        $calendarData = $this->eventsViewModel->getCalendarData($monthNumber, $year);
        
        // Generate calendar HTML
        $calendarHtml = '';
        foreach ($calendarData['weeks'] as $week) {
            foreach ($week as $day) {
                $classes = [];
                if (!$day['current']) {
                    $classes[] = 'disabled';
                }
                if (isset($day['isToday']) && $day['isToday']) {
                    $classes[] = 'active';
                }
                if (isset($day['events']) && $day['events'] > 0) {
                    $classes[] = 'has-event';
                }
                $cellClass = !empty($classes) ? implode(' ', $classes) : '';
                
                $calendarHtml .= '<div class="calendar-cell ' . $cellClass . '" data-date="' . $day['date'] . '">';
                $calendarHtml .= $day['day'];
                
                if (isset($day['events']) && $day['events'] > 0) {
                    $calendarHtml .= '<div class="event-indicator">';
                    $calendarHtml .= '<span class="event-count">' . $day['events'] . '</span>';
                    $calendarHtml .= '</div>';
                }
                
                $calendarHtml .= '</div>';
            }
        }
        
        return $result->setData([
            'success' => true,
            'monthYear' => $calendarData['monthName'] . ' ' . $calendarData['year'],
            'month' => $calendarData['month'],
            'year' => $calendarData['year'],
            'calendarHtml' => $calendarHtml
        ]);
    }
}