<?php
namespace Vishal\Events\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Request\DataPersistorInterface;

abstract class Event extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::manage_events';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param DateTime $dateTime
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory,
        EventResource $eventResource,
        DateTime $dateTime,
        DataPersistorInterface $dataPersistor
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->dateTime = $dateTime;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Init event
     *
     * @return \Vishal\Events\Model\Event|bool
     */
    protected function initEvent()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        $event = $this->eventFactory->create();
        
        if ($eventId) {
            try {
                $this->eventResource->load($event, $eventId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                return false;
            }
        }
        
        $this->coreRegistry->register('current_event', $event);
        return $event;
    }
}