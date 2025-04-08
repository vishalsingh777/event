<?php
/**
 * Tickets.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/Tickets.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Vishal\Events\Model\EventRepository;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\EventTicketRepository;

class Tickets extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var EventTicketFactory
     */
    protected $eventTicketFactory;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventRepository $eventRepository
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketRepository $eventTicketRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventRepository $eventRepository,
        EventTicketFactory $eventTicketFactory,
        EventTicketRepository $eventTicketRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventRepository = $eventRepository;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketRepository = $eventTicketRepository;
        parent::__construct($context);
    }

    /**
     * Save tickets
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = ['error' => false];
        
        try {
            $eventId = (int)$this->getRequest()->getParam('event_id');
            if (!$eventId) {
                throw new LocalizedException(__('Missing event ID parameter.'));
            }
            
            $event = $this->eventRepository->getById($eventId);
            
            // Process manual tickets
            $tickets = $this->getRequest()->getParam('tickets', []);
            if (!empty($tickets)) {
                $this->processManualTickets($eventId, $tickets);
            }
            
            // Process product tickets
            $selectedProducts = $this->getRequest()->getParam('selected_products', '');
            if (!empty($selectedProducts)) {
                $productIds = explode(',', $selectedProducts);
                $this->processProductTickets($eventId, $productIds);
            }
            
            $response['message'] = __('Tickets have been saved.');
        } catch (LocalizedException $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = __('An error occurred while saving tickets.');
        }
        
        return $resultJson->setData($response);
    }

    /**
     * Process manual tickets data
     *
     * @param int $eventId
     * @param array $ticketsData
     * @return void
     */
    private function processManualTickets($eventId, $ticketsData)
    {
        $existingTickets = $this->eventTicketRepository->getTicketsForEvent($eventId);
        $existingTicketIds = [];
        foreach ($existingTickets as $ticket) {
            if (!$ticket->getProductId()) {  // Only include manually created tickets
                $existingTicketIds[$ticket->getId()] = $ticket->getId();
            }
        }

        foreach ($ticketsData as $ticketData) {
            if (empty($ticketData['name']) || empty($ticketData['sku'])) {
                continue;
            }

            if (!empty($ticketData['ticket_id'])) {
                $ticket = $this->eventTicketRepository->getById($ticketData['ticket_id']);
                if (isset($existingTicketIds[$ticket->getId()])) {
                    unset($existingTicketIds[$ticket->getId()]);
                }
            } else {
                $ticket = $this->eventTicketFactory->create();
            }

            $ticket->setData($ticketData);
            $ticket->setEventId($eventId);
            $this->eventTicketRepository->save($ticket);
        }

        // Remove tickets that were deleted
        foreach ($existingTicketIds as $ticketId) {
            $this->eventTicketRepository->deleteById($ticketId);
        }
    }

    /**
     * Process product tickets data
     *
     * @param int $eventId
     * @param array $productIds
     * @return void
     */
    private function processProductTickets($eventId, $productIds)
    {
        $existingTickets = $this->eventTicketRepository->getTicketsForEvent($eventId);
        $existingProductTickets = [];
        
        foreach ($existingTickets as $ticket) {
            if ($ticket->getProductId()) {  // Only include product-linked tickets
                $existingProductTickets[$ticket->getProductId()] = $ticket;
            }
        }

        $position = 0;
        foreach ($productIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            $productId = (int)$productId;
            
            if (isset($existingProductTickets[$productId])) {
                $ticket = $existingProductTickets[$productId];
                unset($existingProductTickets[$productId]);
            } else {
                $ticket = $this->eventTicketFactory->create();
                $ticket->setEventId($eventId);
                $ticket->setProductId($productId);
                
                // Get product data for ticket info
                $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->load($productId);
                $ticket->setName($product->getName());
                $ticket->setSku($product->getSku());
                $ticket->setPrice($product->getPrice());
            }
            
            $ticket->setPosition(++$position);
            $this->eventTicketRepository->save($ticket);
        }

        // Remove product tickets that were unlinked
        foreach ($existingProductTickets as $ticket) {
            $this->eventTicketRepository->delete($ticket);
        }
    }
}

