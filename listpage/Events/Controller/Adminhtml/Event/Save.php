<?php
namespace Insead\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Insead\Events\Api\EventRepositoryInterface;
use Insead\Events\Api\Data\EventInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Save extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::manage_events';

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
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     * @param EventInterfaceFactory $eventFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
            $this->debugFormData($data);
            
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
                }
            } else {
                // For recurring events, handle time slots
                if (isset($data['date_time'])) {
                    // Keep the raw data_time data structure for processing in repository
                    // This contains the nested structure including the 'date_time' keys
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

            // Store store IDs as comma-separated string
            if (isset($data['store_id']) && is_array($data['store_id'])) {
                $data['store_id'] = implode(',', $data['store_id']);
            }

            // Prepare dates
            if (isset($data['start_date'])) {
                $data['start_date'] = $this->prepareDateTimeForSave($data['start_date']);
            }
            
            if (isset($data['end_date'])) {
                $data['end_date'] = $this->prepareDateTimeForSave($data['end_date']);
            }
            
            // Handle URL key uniqueness
            if (isset($data['url_key']) && !empty($data['url_key'])) {
                $id = $this->getRequest()->getParam('event_id');
                $data['url_key'] = $this->ensureUniqueUrlKey($data['url_key'], $id);
            }

            /** @var \Insead\Events\Api\Data\EventInterface $model */
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
     * Ensure URL key is unique
     * 
     * @param string $urlKey
     * @param int|null $eventId
     * @return string
     */
    protected function ensureUniqueUrlKey($urlKey, $eventId = null)
    {
        // Create a URL key if empty
        if (empty($urlKey)) {
            $urlKey = 'event-' . time();
        }
        
        // Convert URL key to valid format
        $urlKey = $this->formatUrlKey($urlKey);
        
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('url_key', $urlKey)
            ->create();
        
        $events = $this->eventRepository->getList($searchCriteria)->getItems();
        
        // If no events with this URL key or the only one is the current event
        if (empty($events) || (count($events) === 1 && reset($events)->getId() == $eventId)) {
            return $urlKey;
        }
        
        // URL key exists, generate a new one
        $i = 1;
        $newUrlKey = $urlKey;
        
        do {
            $newUrlKey = $urlKey . '-' . $i++;
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('url_key', $newUrlKey)
                ->create();
            $events = $this->eventRepository->getList($searchCriteria)->getItems();
        } while (!empty($events));
        
        return $newUrlKey;
    }
    
    /**
     * Format URL key to be valid
     *
     * @param string $urlKey
     * @return string
     */
    protected function formatUrlKey($urlKey)
    {
        // Convert spaces to hyphens
        $urlKey = str_replace(' ', '-', $urlKey);
        
        // Remove special characters
        $urlKey = preg_replace('/[^a-z0-9-]/', '', strtolower($urlKey));
        
        // Replace multiple hyphens with a single one
        $urlKey = preg_replace('/-+/', '-', $urlKey);
        
        // Trim hyphens from beginning and end
        $urlKey = trim($urlKey, '-');
        
        return $urlKey;
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
     * Debug form data structure
     *
     * @param array $data
     * @return void
     */
    private function debugFormData($data)
    {
        $logFile = BP . '/var/log/event_form_debug.log';
        file_put_contents($logFile, print_r($data, true), FILE_APPEND);
    }
}