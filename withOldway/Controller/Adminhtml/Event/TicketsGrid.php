<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Request\DataPersistorInterface;

class TicketsGrid extends Event
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param DateTime $dateTime
     * @param DataPersistorInterface $dataPersistor
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory,
        EventResource $eventResource,
        DateTime $dateTime,
        DataPersistorInterface $dataPersistor,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
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
     * Grid action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $event = $this->initEvent();
        if (!$event) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw->setContents('');
        }
        
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->layoutFactory->create();
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents(
            $layout->createBlock(
                \Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Tickets::class,
                'event.tickets.grid'
            )->toHtml()
        );
        
        return $resultRaw;
    }
}