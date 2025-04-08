<?php
/**
 * Index.php
 * Path: app/code/Vishal/Events/Controller/Index/Index.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Helper\Data as EventHelper;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param PageFactory $resultPageFactory
     * @param EventHelper $eventHelper
     * @param RequestInterface $request
     */
    public function __construct(
        PageFactory $resultPageFactory,
        EventHelper $eventHelper,
        RequestInterface $request
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->eventHelper = $eventHelper;
        $this->request = $request;
    }

    /**
     * Events list page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->eventHelper->isEnabled()) {
            return $this->resultPageFactory->create()->setStatusHeader(404, '1.1', 'Not Found');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($this->eventHelper->getListMetaTitle());

        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->eventHelper->getListTitle());
        }

        return $resultPage;
    }
}

