<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Request\DataPersistorInterface;


class Save extends Event implements HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var EventTicketFactory
     */
    protected $eventTicketFactory;

    /**
     * @var EventTicketResource
     */
    protected $eventTicketResource;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param DateTime $dateTime
     * @param DataPersistorInterface $dataPersistor
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketResource $eventTicketResource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory,
        EventResource $eventResource,
        DateTime $dateTime,
        DataPersistorInterface $dataPersistor,
        EventTicketFactory $eventTicketFactory,
        EventTicketResource $eventTicketResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketResource = $eventTicketResource;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $eventFactory,
            $eventResource,
            $dateTime,
            $dataPersistor
        );
    }

    /**
     * Save event action
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            $eventId = $this->getRequest()->getParam('event_id');
            $model = $this->eventFactory->create();
            
            if ($eventId) {
                try {
                    $this->eventResource->load($model, $eventId);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            
            // Prepare dates
            if (isset($data['start_date'])) {
                $data['start_date'] = $this->prepareDateTimeForSave($data['start_date']);
            }
            
            if (isset($data['end_date'])) {
                $data['end_date'] = $this->prepareDateTimeForSave($data['end_date']);
            }
            
            // Prepare tickets data
            $tickets = [];
            if (isset($data['tickets']) && is_array($data['tickets'])) {
                $tickets = $data['tickets'];
                unset($data['tickets']);
            }
            
            $model->setData($data);
            
            try {
                $this->eventResource->save($model);
                
                // Save tickets
                $this->saveTickets($model->getId(), $tickets);
                
                // Process selected products for tickets
                $this->processTicketProducts($model->getId());
                
                $this->messageManager->addSuccessMessage(__('Event has been saved.'));
                $this->dataPersistor->clear('event_form_data');
                
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['event_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the event.'));
            }
            
            $this->dataPersistor->set('event_form_data', $data);
            return $resultRedirect->setPath('*/*/edit', ['event_id' => $eventId]);
        }
        
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Prepare date time for save
     *
     * @param string $dateTime
     * @return string
     */
    protected function prepareDateTimeForSave($dateTime)
    {
        if (!$dateTime) {
            return null;
        }
        
        try {
            return date('Y-m-d H:i:s', strtotime($dateTime));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Process selected products for tickets
     *
     * @param int $eventId
     * @return void
     */
    protected function processTicketProducts($eventId)
    {
        $selectedProducts = $this->getRequest()->getPost('in_tickets', []);
        
        if (!empty($selectedProducts)) {
            $ticketCollection = $this->eventTicketFactory->create()->getCollection();
            $ticketCollection->addFieldToFilter('event_id', $eventId);
            
            // Get existing product IDs for this event
            $existingProductIds = [];
            foreach ($ticketCollection as $ticket) {
                if ($ticket->getProductId()) {
                    $existingProductIds[$ticket->getProductId()] = $ticket->getId();
                }
            }
            
            // Create tickets for newly selected products
            foreach ($selectedProducts as $productId) {
                if (isset($existingProductIds[$productId])) {
                    // Product already has a ticket, skip
                    continue;
                }
                
                try {
                    // Get product info
                    $product = $this->productRepository->getById($productId);
                    
                    // Create new ticket
                    $ticket = $this->eventTicketFactory->create();
                    $ticket->setEventId($eventId);
                    $ticket->setProductId($productId);
                    $ticket->setName($product->getName());
                    $ticket->setSku($product->getSku());
                    $ticket->setPrice($product->getPrice());
                    $ticket->setPosition(0);
                    
                    $this->eventTicketResource->save($ticket);
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Error creating ticket for product ID %1', $productId));
                }
            }
            
            // Remove tickets for products that are no longer selected
            foreach ($existingProductIds as $productId => $ticketId) {
                if (!in_array($productId, $selectedProducts)) {
                    try {
                        $ticket = $this->eventTicketFactory->create();
                        $this->eventTicketResource->load($ticket, $ticketId);
                        if ($ticket->getId()) {
                            $this->eventTicketResource->delete($ticket);
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Error removing ticket for product ID %1', $productId));
                    }
                }
            }
        }
    }

    /**
     * Save tickets
     *
     * @param int $eventId
     * @param array $tickets
     * @return void
     * @throws \Exception
     */
    protected function saveTickets($eventId, $tickets)
    {
        if (empty($tickets) || !is_array($tickets)) {
            return;
        }
        
        // Get existing ticket IDs
        $existingTicketIds = [];
        if ($eventId) {
            $ticketCollection = $this->eventTicketFactory->create()->getCollection();
            $ticketCollection->addFieldToFilter('event_id', $eventId);
            
            foreach ($ticketCollection as $ticket) {
                if (!$ticket->getProductId()) { // Only process manually created tickets
                    $existingTicketIds[] = $ticket->getId();
                }
            }
        }
        
        $newTicketIds = [];
        foreach ($tickets as $ticketData) {
            if (empty($ticketData['name'])) {
                continue;
            }
            
            $ticketId = isset($ticketData['ticket_id']) ? $ticketData['ticket_id'] : null;
            $ticket = $this->eventTicketFactory->create();
            
            if ($ticketId) {
                $this->eventTicketResource->load($ticket, $ticketId);
                if (!$ticket->getId()) {
                    continue;
                }
                
                $newTicketIds[] = $ticketId;
            }
            
            $ticket->setData($ticketData);
            $ticket->setEventId($eventId);
            $ticket->setProductId(isset($ticketData['product_id']) ? $ticketData['product_id'] : null);
            $this->eventTicketResource->save($ticket);
            
            if (!$ticketId) {
                $newTicketIds[] = $ticket->getId();
            }
        }
        
        // Remove tickets that are no longer needed
        $ticketsToDelete = array_diff($existingTicketIds, $newTicketIds);
        foreach ($ticketsToDelete as $ticketId) {
            $ticket = $this->eventTicketFactory->create();
            $this->eventTicketResource->load($ticket, $ticketId);
            if ($ticket->getId() && !$ticket->getProductId()) {
                $this->eventTicketResource->delete($ticket);
            }
        }
    }
}