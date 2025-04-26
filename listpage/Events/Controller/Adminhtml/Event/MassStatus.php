<?php
/**
 * MassStatus.php
 * Path: app/code/Insead/Events/Controller/Adminhtml/Event/MassStatus.php
 */

declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory;
use Insead\Events\Model\EventRepository;

class MassStatus extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::event_save';

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
        $status = (int) $this->getRequest()->getParam('status');
        $eventsUpdated = 0;
        
        foreach ($collection as $event) {
            try {
                $event->setStatus($status);
                $this->eventRepository->save($event);
                $eventsUpdated++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to change status for event %1: %2', $event->getEventTitle(), $e->getMessage()));
            }
        }
        
        if ($eventsUpdated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 event(s) have been updated.', $eventsUpdated)
            );
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
