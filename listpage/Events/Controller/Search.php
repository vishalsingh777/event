<?php
namespace Insead\Events\Controller;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class Search implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param RequestInterface $request
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $resultPageFactory
    ) {
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Search Results'));
        
        return $resultPage;
    }
}