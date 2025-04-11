<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vishal\Events\Api\EventRepositoryInterface;

class Delete extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::manage_events';

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
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
                $this->messageManager->addSuccessMessage(__('You deleted the event.'));
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