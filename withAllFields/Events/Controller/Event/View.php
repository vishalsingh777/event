<?php
namespace Vishal\Events\Controller\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Vishal\Events\Api\EventRepositoryInterface;
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
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $coreRegistry
     * @param EventRepositoryInterface $eventRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        EventRepositoryInterface $eventRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->context = $context;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $urlKey = trim($this->context->getRequest()->getPathInfo(), '/');
        $urlKey = substr($urlKey, strlen('events/'));
        if (!$urlKey) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        
        try {
            $storeId = $this->storeManager->getStore()->getId();

            $event = $this->eventRepository->getByUrlKey($urlKey, $storeId);
            if (!$event->getId() || $event->getStatus() == 0) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Event not found or not active'));
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
            
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
    }
}