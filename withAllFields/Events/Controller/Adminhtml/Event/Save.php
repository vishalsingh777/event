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
            // Handle status conversion for checkbox/toggle
            if (isset($data['status']) && $data['status'] === 'true') {
                $data['status'] = 1;
            }
            
            // Handle empty event ID
            if (empty($data['event_id'])) {
                $data['event_id'] = null;
            }

            // Process timezone - using exact field name from form
            if (isset($data['event_timezone'])) {
                $data['event_timezone'] = $data['event_timezone'];
            }

            // Process recurring and time slots
            if (isset($data['recurring']) && (int)$data['recurring'] === 0) {
                // Single time slot - using exact field names from form
                if (isset($data['single_start_time']) && isset($data['single_end_time'])) {
                    // Keep the single time values as they are
                    $data['single_start_time'] = $data['single_start_time'];
                    $data['single_end_time'] = $data['single_end_time'];
                    
                    // Also store the time_slots array format for consistency
                    $timeSlots = [
                        [
                            'time_start' => $data['single_start_time'],
                            'time_end' => $data['single_end_time']
                        ]
                    ];
                    $data['time_slots'] = json_encode($timeSlots);
                }
            } else {
                // Process multiple time slots from the dynamic rows
                $timeSlots = [];
                
                // Check if date_time.data exists for dynamic rows
                if (isset($data['date_time']) && isset($data['date_time']['data']) && is_array($data['date_time']['data'])) {
                    foreach ($data['date_time']['data'] as $rowData) {
                        if (isset($rowData['time_start']) && isset($rowData['time_end']) && !isset($rowData['is_deleted'])) {
                            $timeSlots[] = [
                                'time_start' => $rowData['time_start'],
                                'time_end' => $rowData['time_end']
                            ];
                        }
                    }
                }
                
                // Encode time slots as JSON array
                if (!empty($timeSlots)) {
                    $data['time_slots'] = json_encode($timeSlots);
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

            // Handle block dates
            if (isset($data['block_dates']) && is_array($data['block_dates'])) {
                $data['block_dates'] = json_encode($data['block_dates']);
            } elseif (isset($data['block_dates']) && is_string($data['block_dates']) && !empty($data['block_dates'])) {
                // Split by newlines and clean
                $blockDatesArray = array_map('trim', explode("\n", $data['block_dates']));
                $blockDatesArray = array_filter($blockDatesArray);
                $data['block_dates'] = json_encode($blockDatesArray);
            }

            // Prepare dates
            if (isset($data['start_date'])) {
                $data['start_date'] = $this->prepareDateTimeForSave($data['start_date']);
            }
            
            if (isset($data['end_date'])) {
                $data['end_date'] = $this->prepareDateTimeForSave($data['end_date']);
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
            foreach ($data as $key => $value) {
    if (is_array($value)) {
        print_r($key);die;
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