<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Vishal\Events\Api\EventRepositoryInterface;
use Vishal\Events\Api\Data\EventInterfaceFactory;

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
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     * @param EventInterfaceFactory $eventFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
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

            // Process recurring and time slots
            if (isset($data['recurring']) && (int)$data['recurring'] === 0) {
                // Single time slot
                if (isset($data['single_start_time']) && isset($data['single_end_time'])) {
                    $timeSlot = $data['single_start_time'] . '-' . $data['single_end_time'];
                    $data['time_slots'] = json_encode([$timeSlot]);
                }
            } else {
                // Multiple time slots
                if (isset($data['time_slots'])) {
                    $timeSlots = $data['time_slots'];
                    if (is_string($timeSlots) && !empty($timeSlots)) {
                        // Split by newlines and clean
                        $timeSlotArray = array_map('trim', explode("\n", $timeSlots));
                        $timeSlotArray = array_filter($timeSlotArray);
                        $data['time_slots'] = json_encode($timeSlotArray);
                    } else if (is_array($timeSlots)) {
                        $data['time_slots'] = json_encode($timeSlots);
                    }
                }
            }

            // Handle customer group data
            if (isset($data['customer_group']) && is_array($data['customer_group'])) {
                $data['customer_group'] = json_encode($data['customer_group']);
            }

            // Handle available days
            if (isset($data['available_days']) && is_array($data['available_days'])) {
                $data['available_days'] = json_encode($data['available_days']);
            } else {
                $data['available_days'] = json_encode([]);
            }

            // Prepare dates
            if (isset($data['start_date'])) {
                $data['start_date'] = $this->prepareDateTimeForSave($data['start_date']);
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
}