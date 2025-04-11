<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Api\EventRepositoryInterface;

class Edit extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::manage_events';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $coreRegistry
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry,
        EventRepositoryInterface $eventRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Edit event
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        $model = $this->eventRepository->getEmptyEntity();

        if ($eventId) {
            try {
                $model = $this->eventRepository->getById($eventId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('current_event', $model);

        // Create result page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vishal_Events::manage_events');
        $resultPage->addBreadcrumb(__('Events'), __('Events'));
        $resultPage->addBreadcrumb(
            $eventId ? __('Edit Event') : __('New Event'),
            $eventId ? __('Edit Event') : __('New Event')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Events'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getEventTitle() : __('New Event'));
        
        return $resultPage;
    }
}