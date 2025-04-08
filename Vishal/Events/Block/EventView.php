<?php
/**
 * EventView.php
 * Path: app/code/Vishal/Events/Block/EventView.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Helper\Data as EventHelper;

class EventView extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EventHelper $eventHelper
     * @param StoreManagerInterface $storeManager
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EventHelper $eventHelper,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eventHelper = $eventHelper;
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Get event helper
     *
     * @return EventHelper
     */
    public function getEventHelper(): EventHelper
    {
        return $this->eventHelper;
    }

    /**
     * Get current event
     *
     * @return \Vishal\Events\Model\Event|null
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('current_event');
    }

    /**
     * Check if event has tickets
     *
     * @return bool
     */
    public function hasTickets()
    {
        $ticketsBlock = $this->getChildBlock('event.tickets');
        if ($ticketsBlock) {
            return $ticketsBlock->hasTickets();
        }
        
        return false;
    }

    /**
     * Get CMS filtered content
     *
     * @param string $content
     * @return string
     */
    public function getCmsFilteredContent($content)
    {
        if ($content) {
            return $this->filterProvider->getPageFilter()->filter($content);
        }
        
        return '';
    }

    /**
     * Get events page URL
     *
     * @return string
     */
    public function getEventsUrl()
    {
        return $this->getUrl($this->eventHelper->getUrlPrefix());
    }
}