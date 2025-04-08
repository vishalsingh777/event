<?php
/**
 * Edit.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/Edit.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventRepository;

class Edit extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EventRepository $eventRepository
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventRepository $eventRepository,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->eventRepository = $eventRepository;
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit event
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get ID and create model
        $id = $this->getRequest()->getParam('event_id');
        $resultPage = $this->resultPageFactory->create();
        
        // Set title for new and existing event
        $resultPage->setActiveMenu('Vishal_Events::event');
        $resultPage->addBreadcrumb(__('Events'), __('Events'));
        $resultPage->getConfig()->getTitle()->prepend(__('Event Information'));

        // Process the event model
        if ($id) {
            try {
                $model = $this->eventRepository->getById($id);
                $resultPage->getConfig()->getTitle()->prepend($model->getEventTitle());
                
                // Register event for block context
                $this->coreRegistry->register('vishal_event', $model);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Event'));
        }
        
        $this->_initAction();
        
        return $resultPage;
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        // Load layout, set active menu and add breadcrumb
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vishal_Events::event');
        
        return $resultPage;
    }
}