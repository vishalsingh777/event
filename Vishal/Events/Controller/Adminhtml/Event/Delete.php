<?php
/**
 * Delete.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/Delete.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vishal\Events\Model\EventRepository;

class Delete extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_delete';

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param EventRepository $eventRepository
     */
    public function __construct(
        Context $context,
        EventRepository $eventRepository
    ) {
        $this->eventRepository = $eventRepository;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $id = $this->getRequest()->getParam('event_id');
        if ($id) {
            try {
                $this->eventRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The event has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['event_id' => $id]);
            }
        }
        
        $this->messageManager->addErrorMessage(__('We can\'t find an event to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}