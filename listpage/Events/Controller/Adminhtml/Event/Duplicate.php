<?php
namespace Insead\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;

class Duplicate extends Action
{
    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * Constructor
     *
     * @param Context $context
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     */
    public function __construct(
        Context $context,
        EventFactory $eventFactory,
        EventResource $eventResource
    ) {
        parent::__construct($context);
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
    }

    /**
     * Duplicate event action
     *
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $eventId = $this->getRequest()->getParam('event_id');
        
        if (!$eventId) {
            $this->messageManager->addErrorMessage(__('We can\'t find the event to duplicate.'));
            return $resultRedirect->setPath('*/*/');
        }
        
        try {
            // Load the original event
            $originalEvent = $this->eventFactory->create();
            $this->eventResource->load($originalEvent, $eventId);
            
            if (!$originalEvent->getEventId()) {
                $this->messageManager->addErrorMessage(__('Event not found.'));
                return $resultRedirect->setPath('*/*/');
            }
            
            // Create a new event with data from the original
            $newEvent = $this->eventFactory->create();
            $data = $originalEvent->getData();
            
            // Remove identifiers and timestamps
            unset($data['event_id']);
            unset($data['created_at']);
            unset($data['updated_at']);
            
            // Modify the title to indicate it's a duplicate
            $data['event_title'] = $data['event_title'] . ' - Copy';
            
            // Modify URL key to ensure uniqueness
            if (isset($data['url_key'])) {
                $data['url_key'] = $data['url_key'] . '-copy-' . time();
            }
            
            // Set the new event data
            $newEvent->setData($data);
            
            // Save the new event
            $this->eventResource->save($newEvent);
            
            // Success message
            $this->messageManager->addSuccessMessage(__('The event has been duplicated.'));
            
            // Redirect to the edit page of the new event
            return $resultRedirect->setPath('*/*/edit', ['event_id' => $newEvent->getEventId()]);
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Check ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Insead_Events::event_save');
    }
}