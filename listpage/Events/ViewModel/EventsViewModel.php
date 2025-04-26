<?php
namespace Insead\Events\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory;
use Insead\Events\Helper\Data as EventHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Insead\Events\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Insead\Events\Model\ResourceModel\Campus\CollectionFactory as CampusCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;

class EventsViewModel implements ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private $eventCollectionFactory;
    
    /**
     * @var EventHelper
     */
    private $eventHelper;
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    
    /**
     * @var CampusCollectionFactory
     */
    private $campusCollectionFactory;
    
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var EventFactory
     */
    private $eventFactory;
    
    /**
     * @var EventResource
     */
    private $eventResource;

    /**
     * @param CollectionFactory $eventCollectionFactory
     * @param EventHelper $eventHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CampusCollectionFactory $campusCollectionFactory
     * @param TimezoneInterface $timezone
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     */
    public function __construct(
        CollectionFactory $eventCollectionFactory,
        EventHelper $eventHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        CategoryCollectionFactory $categoryCollectionFactory,
        CampusCollectionFactory $campusCollectionFactory,
        TimezoneInterface $timezone,
        EventFactory $eventFactory,
        EventResource $eventResource
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->eventHelper = $eventHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->campusCollectionFactory = $campusCollectionFactory;
        $this->timezone = $timezone;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
    }

    /**
     * Get hero section configuration
     *
     * @return array
     */
    public function getHeroConfig()
    {
        return [
            'title' => $this->getConfigValue('insead_events/general/hero_title') ?: 'INSEAD Events & Programmes',
            'subtitle' => $this->getConfigValue('insead_events/general/hero_subtitle') ?: 'Connect, Learn, and Grow with the INSEAD Community',
            'show_search' => (bool)$this->getConfigValue('insead_events/general/show_hero_search'),
            'background_image' => $this->getConfigValue('insead_events/general/hero_background')
        ];
    }
    
    /**
     * Get config value
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get request parameter
     *
     * @param string $paramName
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getRequestParam($paramName, $defaultValue = null)
    {
        return $this->request->getParam($paramName, $defaultValue);
    }
    
    /**
     * Get all event categories
     *
     * @return array
     */
    public function getCategories()
    {
        $result = [];
        $categories = $this->categoryCollectionFactory->create()
            ->setOrder('sort_order', 'ASC');
        
        foreach ($categories as $category) {
            $result[$category->getId()] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'code' => $category->getCode(),
                'icon' => $category->getIconClass() ?: 'event',
                'count' => $this->getEventCountByCategory($category->getId())
            ];
        }
        
        return $result;
    }
    
    /**
     * Get all campuses
     *
     * @return array
     */
    public function getCampuses()
    {
        $result = [];
        $campuses = $this->campusCollectionFactory->create()
            ->setOrder('sort_order', 'ASC');
        
        foreach ($campuses as $campus) {
            $result[$campus->getId()] = [
                'id' => $campus->getId(),
                'name' => $campus->getName(),
                'code' => $campus->getCode(),
                'image' => $campus->getImage(),
                'description' => $campus->getDescription(),
                'count' => $this->getEventCountByCampus($campus->getId())
            ];
        }
        
        return $result;
    }
    
    /**
     * Get filtered events collection
     *
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getEvents()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId]);
        
        // Apply filters from request
        $category = $this->request->getParam('category');
        $campus = $this->request->getParam('campus');
        $date = $this->request->getParam('date');
        
        // Apply category filter
        if ($category && $category !== 'all') {
            $collection->addFieldToFilter('category_id', $category);
        }
        
        // Apply campus filter
        if ($campus && $campus !== 'all') {
            $collection->addFieldToFilter('campus_id', $campus);
        }
        
        // Apply date filter
        $today = date('Y-m-d');
        if ($date) {
            switch ($date) {
                case 'today':
                    $collection->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00'])
                        ->addFieldToFilter('start_date', ['lteq' => $today . ' 23:59:59']);
                    break;
                case 'this-week':
                    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                    $collection->addFieldToFilter('start_date', ['gteq' => $startOfWeek . ' 00:00:00'])
                        ->addFieldToFilter('start_date', ['lteq' => $endOfWeek . ' 23:59:59']);
                    break;
                case 'this-month':
                    $startOfMonth = date('Y-m-01');
                    $endOfMonth = date('Y-m-t');
                    $collection->addFieldToFilter('start_date', ['gteq' => $startOfMonth . ' 00:00:00'])
                        ->addFieldToFilter('start_date', ['lteq' => $endOfMonth . ' 23:59:59']);
                    break;
                case 'next-month':
                    $startOfNextMonth = date('Y-m-01', strtotime('first day of next month'));
                    $endOfNextMonth = date('Y-m-t', strtotime('last day of next month'));
                    $collection->addFieldToFilter('start_date', ['gteq' => $startOfNextMonth . ' 00:00:00'])
                        ->addFieldToFilter('start_date', ['lteq' => $endOfNextMonth . ' 23:59:59']);
                    break;
                case 'custom':
                    $from = $this->request->getParam('from');
                    $to = $this->request->getParam('to');
                    if ($from) {
                        $collection->addFieldToFilter('start_date', ['gteq' => $from . ' 00:00:00']);
                    }
                    if ($to) {
                        $collection->addFieldToFilter('start_date', ['lteq' => $to . ' 23:59:59']);
                    }
                    break;
                default:
                    // Show upcoming events by default
                    $collection->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00']);
            }
        } else {
            // Show upcoming events by default
            $collection->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00']);
        }
        
        // Default sort by start date
        $collection->setOrder('start_date', 'ASC');
        
        return $collection;
    }
    
    /**
     * Get featured events
     *
     * @param int $limit
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getFeaturedEvents($limit = 3)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('is_featured', 1)
            ->setOrder('start_date', 'ASC')
            ->setPageSize($limit);
        
        return $collection;
    }
    
    /**
     * Get upcoming events (excluding featured)
     *
     * @param int $limit
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getUpcomingEvents($limit = 6)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $today = date('Y-m-d');
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00']);
        
        // Exclude featured events
        $featuredEvents = $this->getFeaturedEvents();
        if ($featuredEvents->getSize() > 0) {
            $featuredIds = [];
            foreach ($featuredEvents as $event) {
                $featuredIds[] = $event->getId();
            }
            if (!empty($featuredIds)) {
                $collection->addFieldToFilter('event_id', ['nin' => $featuredIds]);
            }
        }
        
        $collection->setOrder('start_date', 'ASC')
            ->setPageSize($limit);
        
        return $collection;
    }
    
    /**
     * Get today's events
     *
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getTodayEvents()
    {
        $today = date('Y-m-d');
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00'])
            ->addFieldToFilter('start_date', ['lteq' => $today . ' 23:59:59'])
            ->setOrder('start_date', 'ASC');
        
        return $collection;
    }
    
    /**
     * Get calendar data
     *
     * @param int|null $month
     * @param int|null $year
     * @return array
     */
    public function getCalendarData($month = null, $year = null)
    {
        if ($month === null) {
            $month = date('m');
        }
        
        if ($year === null) {
            $year = date('Y');
        }
        
        // Generate calendar data using a method similar to Block class
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $startWeekday = date('N', $firstDay); // 1 (Mon) to 7 (Sun)
        
        // Get previous month's days that appear in the calendar
        $prevDays = [];
        if ($startWeekday > 1) {
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            $daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
            
            for ($i = $startWeekday - 1; $i > 0; $i--) {
                $day = $daysInPrevMonth - $i + 1;
                $date = "$prevYear-" . sprintf('%02d', $prevMonth) . "-" . sprintf('%02d', $day);
                $prevDays[] = [
                    'day' => $day,
                    'date' => $date,
                    'current' => false,
                    'events' => $this->getEventCountByDate($date)
                ];
            }
        }
        
        // Current month days
        $currentDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = "$year-" . sprintf('%02d', $month) . "-" . sprintf('%02d', $day);
            $isToday = $date == date('Y-m-d');
            
            $currentDays[] = [
                'day' => $day,
                'date' => $date,
                'current' => true,
                'isToday' => $isToday,
                'events' => $this->getEventCountByDate($date)
            ];
        }
        
        // Next month days to complete the calendar grid
        $nextDays = [];
        $totalDays = count($prevDays) + count($currentDays);
        $remainingDays = 42 - $totalDays; // 6 rows × 7 days
        
        if ($remainingDays > 0) {
            $nextMonth = $month == 12 ? 1 : $month + 1;
            $nextYear = $month == 12 ? $year + 1 : $year;
            
            for ($day = 1; $day <= $remainingDays; $day++) {
                $date = "$nextYear-" . sprintf('%02d', $nextMonth) . "-" . sprintf('%02d', $day);
                $nextDays[] = [
                    'day' => $day,
                    'date' => $date,
                    'current' => false,
                    'events' => $this->getEventCountByDate($date)
                ];
            }
        }
        
        // Build weeks
        $weeks = [];
        $allDays = array_merge($prevDays, $currentDays, $nextDays);
        $weekChunks = array_chunk($allDays, 7);
        
        foreach ($weekChunks as $weekDays) {
            $weeks[] = $weekDays;
        }
        
        return [
            'year' => $year,
            'month' => $month,
            'monthName' => date('F', $firstDay),
            'weeks' => $weeks
        ];
    }
    
    /**
     * Get events for a specific date
     *
     * @param string $date Format: Y-m-d
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getEventsByDate($date)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('start_date', ['gteq' => $date . ' 00:00:00'])
            ->addFieldToFilter('start_date', ['lteq' => $date . ' 23:59:59'])
            ->setOrder('start_date', 'ASC');
        
        return $collection;
    }
    
    /**
     * Get event count for a specific date
     *
     * @param string $date Format: Y-m-d
     * @return int
     */
    protected function getEventCountByDate($date)
    {
        $collection = $this->getEventsByDate($date);
        return $collection->getSize();
    }
    
    /**
     * Get event count by category
     *
     * @param int $categoryId
     * @return int
     */
    protected function getEventCountByCategory($categoryId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('category_id', $categoryId)
            ->addFieldToFilter('start_date', ['gteq' => date('Y-m-d') . ' 00:00:00']);
        
        return $collection->getSize();
    }
    
    /**
     * Get event count by campus
     *
     * @param int $campusId
     * @return int
     */
    protected function getEventCountByCampus($campusId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('campus_id', $campusId)
            ->addFieldToFilter('start_date', ['gteq' => date('Y-m-d') . ' 00:00:00']);
        
        return $collection->getSize();
    }
    
    /**
     * Get events for a specific campus
     *
     * @param int $campusId
     * @param int $pageSize
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getEventsByCampus($campusId, $pageSize = 4)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', \Insead\Events\Model\Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('campus_id', $campusId)
            ->addFieldToFilter('start_date', ['gteq' => date('Y-m-d') . ' 00:00:00'])
            ->setOrder('start_date', 'ASC')
            ->setPageSize($pageSize);
            
        return $collection;
    }
    
    /**
     * Format event time
     *
     * @param string $date
     * @return string
     */
    public function formatEventTime($date)
    {
        return $this->eventHelper->formatTime($date);
    }
    
    /**
     * Format date
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDate($date, $format = 'F j, Y')
    {
        return $this->eventHelper->formatDate($date, $format);
    }
    
    /**
     * Get event URL
     *
     * @param \Insead\Events\Model\Event $event
     * @return string
     */
    public function getEventUrl($event)
    {
        return $this->eventHelper->getEventUrl($event->getUrlKey());
    }
    
    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->eventHelper->isModuleEnabled();
    }
    
    /**
     * Get current view mode
     *
     * @return string
     */
    public function getCurrentViewMode()
    {
        $viewMode = $this->request->getParam('view');
        if (!$viewMode) {
            $viewMode = $this->scopeConfig->getValue(
                'insead_events/display/default_view',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) ?: 'grid';
        }
        
        return in_array($viewMode, ['grid', 'list', 'calendar']) ? $viewMode : 'grid';
    }
    
    /**
     * Check if filtering is active
     *
     * @return bool
     */
    public function isFiltering()
    {
        return $this->request->getParam('category') !== null 
            || $this->request->getParam('campus') !== null 
            || $this->request->getParam('date') !== null;
    }
    
    /**
     * Check if sections should be shown based on config
     *
     * @param string $section
     * @return bool
     */
    public function shouldShowSection($section)
    {
        $configPath = 'insead_events/display/show_' . $section;
        return (bool)$this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get media URL
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }
    
    /**
     * Get view file URL
     *
     * @param string $fileId
     * @return string
     */
    public function getViewFileUrl($fileId)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_STATIC
        ) . 'frontend/' . $fileId;
    }
    
    /**
     * Get event image URL
     * 
     * @param \Insead\Events\Model\Event $event
     * @param string $defaultImage
     * @return string
     */
    public function getEventImageUrl($event, $defaultImage = null)
    {
        if ($event->getImage()) {
            return $this->getMediaUrl() . 'insead_events/' . $event->getImage();
        }
        
        // Try to extract image from content
        if ($event->getContent() && preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $event->getContent(), $matches)) {
            return $matches[1];
        }
        
        // Get category-specific default image
        if ($event->getCategoryId()) {
            $categories = $this->getCategories();
            foreach ($categories as $categoryId => $category) {
                if ($categoryId == $event->getCategoryId()) {
                    $categoryCode = $category['code'];
                    $categoryImage = 'Insead_Events::images/event-' . $categoryCode . '.jpg';
                    return $this->getViewFileUrl($categoryImage);
                }
            }
        }
        
        // Fall back to provided default or generic default
        if ($defaultImage) {
            return $this->getViewFileUrl('Insead_Events::images/' . $defaultImage);
        }
        
        return $this->getViewFileUrl('Insead_Events::images/event-default.jpg');
    }
}