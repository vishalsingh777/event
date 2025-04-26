<?php
namespace Insead\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory;
use Insead\Events\Model\Event;
use Magento\Store\Model\StoreManagerInterface;
use Insead\Events\Helper\Data as EventHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Insead\Events\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Insead\Events\Model\ResourceModel\Campus\CollectionFactory as CampusCollectionFactory;
use Insead\Events\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;

class EventList extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;
    
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var BannerCollectionFactory
     */
    protected $bannerCollectionFactory;
    
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;
    
    /**
     * @var CampusCollectionFactory
     */
    protected $campusCollectionFactory;
    
    /**
     * @var array|null
     */
    protected $categoriesData = null;
    
    /**
     * @var array|null
     */
    protected $campusesData = null;

    /**
     * @param Context $context
     * @param CollectionFactory $eventCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param EventHelper $eventHelper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param RequestInterface $request
     * @param Registry $registry
     * @param TimezoneInterface $timezone
     * @param ScopeConfigInterface $scopeConfig
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CampusCollectionFactory $campusCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $eventCollectionFactory,
        StoreManagerInterface $storeManager,
        EventHelper $eventHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        RequestInterface $request,
        Registry $registry,
        TimezoneInterface $timezone,
        ScopeConfigInterface $scopeConfig,
        BannerCollectionFactory $bannerCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CampusCollectionFactory $campusCollectionFactory,
        array $data = []
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->storeManager = $storeManager;
        $this->eventHelper = $eventHelper;
        $this->filterProvider = $filterProvider;
        $this->request = $request;
        $this->registry = $registry;
        $this->timezone = $timezone;
        $this->scopeConfig = $scopeConfig;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->campusCollectionFactory = $campusCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get events collection with applied filters
     *
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getEvents()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId]);
        
        // Apply filters from request
        $category = $this->request->getParam('category', 'all');
        $campus = $this->request->getParam('campus', 'all');
        $date = $this->request->getParam('date', 'all');
        
        // Apply category filter
        if ($category !== 'all') {
            $collection->addFieldToFilter('category_id', $category);
        }
        
        // Apply campus filter
        if ($campus !== 'all') {
            $collection->addFieldToFilter('campus_id', $campus);
        }
        
        // Apply date filter
        $today = date('Y-m-d');
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
                // Default to show all upcoming events
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
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            // Assuming you have a is_featured field in your events table
            // If not, you can use another criteria to select featured events
            ->addFieldToFilter('is_featured', 1)
            ->setOrder('start_date', 'ASC')
            ->setPageSize($limit);
        
        return $collection;
    }
    
    /**
     * Get upcoming events excluding featured
     *
     * @param int $limit
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getUpcomingEvents($limit = 6)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $today = date('Y-m-d');
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('start_date', ['gteq' => $today . ' 00:00:00']);
        
        // Exclude featured events if is_featured field exists
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
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        if ($this->categoriesData === null) {
            $this->categoriesData = [];
            $categories = $this->categoryCollectionFactory->create();
            $categories->setOrder('sort_order', 'ASC');
            
            foreach ($categories as $category) {
                $this->categoriesData[$category->getId()] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'code' => $category->getCode(),
                    'icon' => $category->getIconClass(),
                    'count' => $this->getEventCountByCategory($category->getId())
                ];
            }
        }
        
        return $this->categoriesData;
    }
    
    /**
     * Get campuses
     *
     * @return array
     */
    public function getCampuses()
    {
        if ($this->campusesData === null) {
            $this->campusesData = [];
            $campuses = $this->campusCollectionFactory->create();
            $campuses->setOrder('sort_order', 'ASC');
            
            foreach ($campuses as $campus) {
                $this->campusesData[$campus->getId()] = [
                    'id' => $campus->getId(),
                    'name' => $campus->getName(),
                    'code' => $campus->getCode(),
                    'image' => $campus->getImage(),
                    'description' => $campus->getDescription(),
                    'count' => $this->getEventCountByCampus($campus->getId())
                ];
            }
        }
        
        return $this->campusesData;
    }
    
    /**
     * Get filter provider
     *
     * @return \Magento\Cms\Model\Template\FilterProvider
     */
    public function getFilterProvider()
    {
        return $this->filterProvider;
    }

    /**
     * Get event URL (using your existing method)
     *
     * @param Event $event
     * @return string
     */
    public function getEventUrl($event)
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'events/' . $event->getUrlKey();
    }

    /**
     * Get event URL with query params
     *
     * @param Event $event
     * @param array $params
     * @return string
     */
    public function getEventUrlWithParams($event, $params = [])
    {
        $url = $this->getEventUrl($event);
        if (!empty($params)) {
            $queryString = http_build_query($params);
            $url .= (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $queryString;
        }
        return $url;
    }

    /**
     * Format date (using your existing method)
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
     * Format time (using your existing method)
     *
     * @param string $date
     * @return string
     */
    public function formatEventTime($date)
    {
        return $this->eventHelper->formatTime($date);
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
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('start_date', ['gteq' => $date . ' 00:00:00'])
            ->addFieldToFilter('start_date', ['lteq' => $date . ' 23:59:59'])
            ->setOrder('start_date', 'ASC');
        
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
        return $this->getEventsByDate($today);
    }
    
    /**
     * Get current month calendar data
     *
     * @return array
     */
    public function getCalendarData()
    {
        $year = date('Y');
        $month = date('m');
        
        return $this->generateCalendarData($year, $month);
    }
    
    /**
     * Generate calendar data for specific month
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    protected function generateCalendarData($year, $month)
    {
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
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
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
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId])
            ->addFieldToFilter('campus_id', $campusId)
            ->addFieldToFilter('start_date', ['gteq' => date('Y-m-d') . ' 00:00:00']);
        
        return $collection->getSize();
    }
    
    /**
     * Get banners for listing page
     *
     * @return \Insead\Events\Model\ResourceModel\Banner\Collection
     */
    public function getListingBanners()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->bannerCollectionFactory->create();
        $collection->addFieldToFilter('status', 1) // Active banners
            ->addFieldToFilter('banner_type', 1) // Type for listing page
            ->addFilter('store_ids', ['finset' => $storeId])
            ->setOrder('position', 'ASC');
        
        return $collection;
    }
    
    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get page title from config
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->scopeConfig->getValue(
            'insead_events/general/page_title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: 'INSEAD Events & Programmes';
    }
    
    /**
     * Check if hero banner should be shown
     *
     * @return bool
     */
    public function shouldShowHero()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/display/show_hero',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Check if categories section should be shown
     *
     * @return bool
     */
    public function shouldShowCategories()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/display/show_categories',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Check if campus section should be shown
     *
     * @return bool
     */
    public function shouldShowCampus()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/display/show_campus',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Check if calendar should be shown
     *
     * @return bool
     */
    public function shouldShowCalendar()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/display/show_calendar',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Check if newsletter signup should be shown
     *
     * @return bool
     */
    public function shouldShowNewsletter()
    {
        return (bool)$this->scopeConfig->getValue(
            'insead_events/display/show_newsletter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
}