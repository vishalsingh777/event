<?php
/**
 * INSEAD Events Banner Index Controller
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::banner';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Insead_Events::banners');
        $resultPage->addBreadcrumb(__('INSEAD Events'), __('INSEAD Events'));
        $resultPage->addBreadcrumb(__('Manage Banners'), __('Manage Banners'));
        $resultPage->getConfig()->getTitle()->prepend(__('Banners'));

        return $resultPage;
    }
}