<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Event implements HttpPostActionInterface
{
    /**
     * Delete event action
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $eventId = $this->getRequest()->getParam('event_id');
        
        if ($eventId) {
            try {
                $model = $this->eventFactory->create();
                $this->eventResource->load($model, $eventId);
                $this->eventResource->delete($model);
                $this->messageManager->addSuccessMessage(__('Event has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['event_id' => $eventId]);
            }
        }
        
        $this->messageManager->addErrorMessage(__('We can\'t find an event to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}