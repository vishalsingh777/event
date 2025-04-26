<?php
namespace Insead\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Insead\Events\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Insead\Events\Model\ResourceModel\Campus\CollectionFactory as CampusCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;

class EventSearch extends Template
{
    /**
     * @var EventCollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;
    
    /**
     * @var CampusCollectionFactory
     */
    protected $campusCollectionFactory;
    
    /**
     * @var RequestInterface
     */
    protected $request;
    
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
     * @param EventCollectionFactory $eventCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CampusCollectionFactory $campusCollectionFactory
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        EventCollectionFactory $eventCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CampusCollectionFactory $campusCollectionFactory,
        RequestInterface $request,
        array $data = []
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->campusCollectionFactory = $campusCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Get search query
     *
     * @return string
     */
    public function getSearchQuery()
    {
        return trim($this->request->getParam('q', ''));
    }

    /**
     * Get search results
     *
     * @return \Insead\Events\Model\ResourceModel\Event\Collection
     */
    public function getSearchResults()
    {
        $query = $this->getSearchQuery();
        
        if (empty($query)) {
            return null;
        }
        
        $collection = $this->eventCollectionFactory->create();
        $collection->addActiveFilter();
        
        // Search title and content
        $collection->addFieldToFilter(
            ['title', 'content'],
            [
                ['like' => "%{$query}%"],
                ['like' => "%{$query}%"]
            ]
        );
        
        // Set default sort order
        $collection->setOrder('start_date', 'ASC');
        
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
                    'name' => $category->getName(),
                    'code' => $category->getCode(),
                    'icon' => $category->getIconClass()
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
                    'name' => $campus->getName(),
                    'code' => $campus->getCode()
                ];
            }
        }
        
        return $this->campusesData;
    }
    
    /**
     * Get URL for an event
     *
     * @param \Insead\Events\Model\Event $event
     * @return string
     */
    public function getEventUrl($event)
    {
        return $this->getUrl('events/view/index', ['id' => $event->getId()]);
    }
    
    /**
     * Get category data by ID
     *
     * @param int $categoryId
     * @return array|null
     */
    public function getCategoryById($categoryId)
    {
        $categories = $this->getCategories();
        return isset($categories[$categoryId]) ? $categories[$categoryId] : null;
    }
    
    /**
     * Get campus data by ID
     *
     * @param int $campusId
     * @return array|null
     */
    public function getCampusById($campusId)
    {
        $campuses = $this->getCampuses();
        return isset($campuses[$campusId]) ? $campuses[$campusId] : null;
    }
    
    /**
     * Highlight search term in text
     *
     * @param string $text
     * @param int $maxLength
     * @return string
     */
    public function highlightSearchTerm($text, $maxLength = 200)
    {
        $query = $this->getSearchQuery();
        
        if (empty($query) || empty($text)) {
            // If no query or text is empty, return the truncated text
            return mb_strlen($text) > $maxLength 
                ? mb_substr($text, 0, $maxLength) . '...'
                : $text;
        }
        
        // Strip tags and ensure we have plain text
        $plainText = strip_tags($text);
        
        // Find position of the search term
        $pos = mb_stripos($plainText, $query);
        
        if ($pos === false) {
            // If term not found, return the start of the text
            return mb_strlen($plainText) > $maxLength 
                ? mb_substr($plainText, 0, $maxLength) . '...'
                : $plainText;
        }
        
        // Calculate excerpt start position to give context around the match
        $startPos = max(0, $pos - 60);
        
        // Extract excerpt
        $excerpt = mb_substr($plainText, $startPos, $maxLength);
        
        // Add ellipsis if we're not starting from the beginning
        if ($startPos > 0) {
            $excerpt = '...' . $excerpt;
        }
        
        // Add ellipsis if we're not ending at the end
        if ($startPos + $maxLength < mb_strlen($plainText)) {
            $excerpt .= '...';
        }
        
        // Highlight the search term
        $pattern = '/(' . preg_quote($query, '/') . ')/i';
        $excerpt = preg_replace($pattern, '<mark>$1</mark>', $excerpt);
        
        return $excerpt;
    }
}