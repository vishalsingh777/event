<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\Model\View\Result\Page;

class Index extends Event implements HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vishal_Events::manage_events');
        $resultPage->addBreadcrumb(__('Events'), __('Events'));
        $resultPage->addBreadcrumb(__('Manage Events'), __('Manage Events'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Events'));

        return $resultPage;
    }
}