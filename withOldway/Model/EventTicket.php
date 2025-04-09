<?php
namespace Vishal\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;

class EventTicket extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'vishal_event_ticket';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vishal_event_ticket';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EventTicketResource::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), Event::CACHE_TAG . '_' . $this->getEventId()];
    }
}