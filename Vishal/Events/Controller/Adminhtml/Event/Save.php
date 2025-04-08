<?php
/**
 * Save.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/Save.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Vishal\Events\Model\Event;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\EventRepository;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\EventTicketRepository;

class Save extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

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
     * @param DataPersistorInterface $dataPersistor
     * @param EventFactory $eventFactory
     * @param EventRepository $eventRepository
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketRepository $eventTicketRepository
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EventFactory $eventFactory,
        EventRepository $eventRepository,
        EventTicketFactory $eventTicketFactory,
        EventTicketRepository $eventTicketRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketRepository = $eventTicketRepository;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            if (empty($data['event_id'])) {
                $data['event_id'] = null;
            } 

            // Handle empty store selection
            if (!isset($data['store_id']) || empty($data['store_id'])) {
                $data['store_id'] = [0];
            }

            /** @var Event $model */
            $model = $this->eventFactory->create();

            $id = $this->getRequest()->getParam('event_id');
            if ($id) {
                try {
                    $model = $this->eventRepository->getById($id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);

            try {
                $this->eventRepository->save($model);
                print_r($data);die;
                
                // Process product tickets
                if (isset($data['product_tickets']) && is_array($data['product_tickets'])) {
                    $this->processProductTickets($model->getId(), $data['product_tickets']);
                }
                
                $this->messageManager->addSuccessMessage(__('You saved the event.'));
                $this->dataPersistor->clear('vishal_event');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['event_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the event.'));
            }

            $this->dataPersistor->set('vishal_event', $data);
            return $resultRedirect->setPath('*/*/edit', ['event_id' => $this->getRequest()->getParam('event_id')]);
        }
        return $resultRedirect->setPath('*/*/');
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
     * @param string $productTicketsData Comma-separated product IDs
     * @return void
     */
    private function processProductTickets($eventId, $productTicketsData)
    {
        // Convert to array if it's a string
        if (is_string($productTicketsData)) {
            $productIds = !empty($productTicketsData) ? explode(',', $productTicketsData) : [];
        } else {
            $productIds = (array)$productTicketsData;
        }
        
        // Filter out empty values
        $productIds = array_filter($productIds);
        
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
                if ($product && $product->getId()) {
                    $ticket->setName($product->getName());
                    $ticket->setSku($product->getSku());
                    $ticket->setPrice($product->getPrice());
                    
                    // Add store ID 
                    $storeId = $this->getRequest()->getParam('store_id');
                    if (is_array($storeId)) {
                        $storeId = $storeId[0] ?? 0;
                    }
                    $ticket->setStoreId($storeId);
                }
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