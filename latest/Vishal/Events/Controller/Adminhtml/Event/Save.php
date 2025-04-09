<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Vishal\Events\Api\EventRepositoryInterface;
use Vishal\Events\Api\Data\EventInterfaceFactory;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory as TicketCollectionFactory;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;

class Save extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::manage_events';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var EventInterfaceFactory
     */
    protected $eventFactory;

    /**
     * @var TicketCollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var EventTicketFactory
     */
    protected $eventTicketFactory;

    /**
     * @var EventTicketResource
     */
    protected $eventTicketResource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     * @param EventInterfaceFactory $eventFactory
     * @param TicketCollectionFactory $ticketCollectionFactory
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketResource $eventTicketResource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory,
        TicketCollectionFactory $ticketCollectionFactory,
        EventTicketFactory $eventTicketFactory,
        EventTicketResource $eventTicketResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketResource = $eventTicketResource;
        $this->productRepository = $productRepository;
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
            if (isset($data['status']) && $data['status'] === 'true') {
                $data['status'] = 1;
            }
            if (empty($data['event_id'])) {
                $data['event_id'] = null;
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

            /** @var \Vishal\Events\Api\Data\EventInterface $model */
            $model = $this->eventFactory->create();
            $id = $this->getRequest()->getParam('event_id');
            
            if ($id) {
                try {
                    $model = $this->eventRepository->getById($id);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            
            $model->setData($data);
            
            try {
                $this->eventRepository->save($model);
                
                // Save tickets
                $this->saveTickets($model->getId(), $tickets);
                
                $this->messageManager->addSuccessMessage(__('You saved the event.'));
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
            return $resultRedirect->setPath('*/*/edit', ['event_id' => $this->getRequest()->getParam('event_id')]);
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
     * Save tickets
     *
     * @param int $eventId
     * @param array $tickets
     * @return void
     * @throws \Exception
     */
    protected function saveTickets($eventId, $tickets)
    {
        if (empty($eventId) || !is_array($tickets)) {
            return;
        }
        
        // Get existing ticket IDs
        $existingTicketIds = [];
        $ticketCollection = $this->ticketCollectionFactory->create();
        $ticketCollection->addFieldToFilter('event_id', $eventId);
        
        foreach ($ticketCollection as $ticket) {
            $existingTicketIds[] = $ticket->getId();
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
            if ($ticket->getId()) {
                $this->eventTicketResource->delete($ticket);
            }
        }
    }
}