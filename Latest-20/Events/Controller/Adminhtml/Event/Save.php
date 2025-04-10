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

            // Handle customer group data
            if (isset($data['customer_group']) && is_array($data['customer_group'])) {
                $data['customer_group'] = implode(',', $data['customer_group']);
                $data['customer_group'] = json_encode($data['customer_group']);
            }

            // Find the existing code that handles time slots and blocked dates
            // Replace it with this:

            // Handle time slots
            if (isset($data['selected_time_slots'])) {
                $selectedTimeSlots = $data['selected_time_slots'];
                if (!is_array($selectedTimeSlots)) {
                    try {
                        $selectedTimeSlots = json_decode($selectedTimeSlots, true);
                    } catch (\Exception $e) {
                        $selectedTimeSlots = [];
                    }
                }
                
                // Save as JSON for both fields
                $data['selected_time_slots'] = json_encode($selectedTimeSlots);
                $data['time_slots'] = $data['selected_time_slots']; // For backward compatibility
            } elseif (isset($data['time_slots'])) {
                // For backward compatibility
                $timeSlots = $data['time_slots'];
                if (!is_array($timeSlots) && !empty($timeSlots)) {
                    try {
                        // Try to parse if it's a JSON string
                        $parsedTimeSlots = json_decode($timeSlots, true);
                        if (is_array($parsedTimeSlots)) {
                            $data['time_slots'] = json_encode($parsedTimeSlots);
                            $data['selected_time_slots'] = $data['time_slots'];
                        } else {
                            // Handle legacy comma-separated format
                            $timeSlots = array_map('trim', explode(',', $timeSlots));
                            $data['time_slots'] = json_encode($timeSlots);
                            $data['selected_time_slots'] = $data['time_slots'];
                        }
                    } catch (\Exception $e) {
                        $data['time_slots'] = json_encode([]);
                        $data['selected_time_slots'] = json_encode([]);
                    }
                }
            }

        // Handle time slots from multiselect
            if (isset($data['time_slots_grid']) && is_array($data['time_slots_grid'])) {
                $timeSlots = array_values($data['time_slots_grid']); // Convert associative to indexed array
                $data['time_slots'] = json_encode($timeSlots);
                $data['selected_time_slots'] = $data['time_slots']; // For compatibility
            } elseif (isset($data['time_slots']) && is_string($data['time_slots'])) {
                // Keep time_slots as is if it's already a string (JSON or otherwise)
                $data['selected_time_slots'] = $data['time_slots']; // For compatibility
            }

            // Handle available days
            if (isset($data['available_days']) && is_array($data['available_days'])) {
                $data['available_days'] = json_encode($data['available_days']);
            } else {
                $data['available_days'] = json_encode([]);
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