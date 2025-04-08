<?php
/**
 * View.php
 * Path: app/code/Vishal/Events/Controller/Index/View.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Helper\Data as EventHelper;
use Vishal\Events\Model\EventRepository;

class View implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventHelper
     */
    private $eventHelper;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param EventRepository $eventRepository
     * @param StoreManagerInterface $storeManager
     * @param EventHelper $eventHelper
     * @param Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        EventRepository $eventRepository,
        StoreManagerInterface $storeManager,
        EventHelper $eventHelper,
        Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
        $this->eventHelper = $eventHelper;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
    }

    /**
     * Event view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $urlKey = $this->request->getParam('event_url');
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $event = $this->eventRepository->getByUrlKey($urlKey, $storeId);
            
            // Register the event to make it available to blocks
            $this->coreRegistry->register('current_event', $event);
            
            // Create and return the page result
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set($event->getEventTitle());
            
            return $resultPage;
            
        } catch (\Exception $e) {
            // Log the error
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            
            // Forward to the NoRoute (404) page
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}