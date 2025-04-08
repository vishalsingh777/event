<?php
/**
 * MassDelete.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/MassDelete.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Vishal\Events\Model\EventRepository;

class MassDelete extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_delete';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param EventRepository $eventRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        EventRepository $eventRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->eventRepository = $eventRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $eventsDeleted = 0;
        
        foreach ($collection as $event) {
            try {
                $this->eventRepository->delete($event);
                $eventsDeleted++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to delete event %1: %2', $event->getEventTitle(), $e->getMessage()));
            }
        }
        
        if ($eventsDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 event(s) have been deleted.', $eventsDeleted)
            );
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}





