<?php
/**
 * Tab/TicketsJs.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Tab/TicketsJs.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Vishal\Events\Model\EventTicketRepository;

class TicketsJs extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Vishal_Events::event/tab/tickets.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EventTicketRepository $eventTicketRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EventTicketRepository $eventTicketRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eventTicketRepository = $eventTicketRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('vishal_event');
    }

    /**
     * Get tickets
     *
     * @return \Vishal\Events\Model\ResourceModel\EventTicket\Collection
     */
    public function getTickets()
    {
        $eventId = $this->getEvent()->getId();
        if ($eventId) {
            return $this->eventTicketRepository->getTicketsForEvent($eventId);
        }
        
        return null;
    }

    /**
     * Get manual tickets
     *
     * @return array
     */
    public function getManualTickets()
    {
        $manualTickets = [];
        $tickets = $this->getTickets();
        
        if ($tickets) {
            foreach ($tickets as $ticket) {
                if (!$ticket->getProductId()) {
                    $manualTickets[] = $ticket;
                }
            }
        }
        
        return $manualTickets;
    }

    /**
     * Get product tickets
     *
     * @return array
     */
    public function getProductTickets()
    {
        $productTickets = [];
        $tickets = $this->getTickets();
        
        if ($tickets) {
            foreach ($tickets as $ticket) {
                if ($ticket->getProductId()) {
                    $productTickets[] = $ticket;
                }
            }
        }
        
        return $productTickets;
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('events/event/tickets', ['event_id' => $this->getEvent()->getId()]);
    }

    /**
     * Get product grid URL
     *
     * @return string
     */
    public function getProductGridUrl()
    {
        return $this->getUrl('events/event/productGrid', ['event_id' => $this->getEvent()->getId()]);
    }
}
