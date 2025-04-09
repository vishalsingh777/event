<?php
namespace Vishal\Events\Controller\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Store\Model\StoreManagerInterface;

class View implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $coreRegistry
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        EventFactory $eventFactory,
        EventResource $eventResource,
        StoreManagerInterface $storeManager
    ) {
        $this->context = $context;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $urlKey = $this->context->getRequest()->getParam('url_key');
        
        if (!$urlKey) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        
        $event = $this->eventFactory->create();
        $event->setStoreId($this->storeManager->getStore()->getId());
        
        $eventId = $event->getResource()->getIdByUrlKey($urlKey);
        if (!$eventId) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        
        $this->eventResource->load($event, $eventId);
        
        if (!$event->getId() || $event->getStatus() == 0) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        
        $this->coreRegistry->register('current_event', $event);
        
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($event->getEventTitle());
        
        // Set meta information
        if ($event->getPageTitle()) {
            $resultPage->getConfig()->getTitle()->set($event->getPageTitle());
        } else {
            $resultPage->getConfig()->getTitle()->set($event->getEventTitle());
        }
        
        if ($event->getKeywords()) {
            $resultPage->getConfig()->setKeywords($event->getKeywords());
        }
        
        if ($event->getDescription()) {
            $resultPage->getConfig()->setDescription($event->getDescription());
        }
        
        return $resultPage;
    }
}