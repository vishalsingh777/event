<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\Model\View\Result\Forward;

class NewAction extends Event implements HttpGetActionInterface
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param DateTime $dateTime
     * @param DataPersistorInterface $dataPersistor
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory,
        EventResource $eventResource,
        DateTime $dateTime,
        DataPersistorInterface $dataPersistor,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $eventFactory,
            $eventResource,
            $dateTime,
            $dataPersistor
        );
    }

    /**
     * New event action
     *
     * @return Forward
     */
    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}