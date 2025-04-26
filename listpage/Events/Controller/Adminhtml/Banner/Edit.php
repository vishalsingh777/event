<?php
/**
 * INSEAD Events Banner Edit Controller
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Insead\Events\Model\BannerFactory;
use Magento\Framework\Registry;

class Edit extends Action
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
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BannerFactory $bannerFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BannerFactory $bannerFactory,
        Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerFactory = $bannerFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit Banner
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get ID and create model
        $id = $this->getRequest()->getParam('banner_id');
        $model = $this->bannerFactory->create();
        
        // Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $this->coreRegistry->register('insead_events_banner', $model);
        
        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Insead_Events::banners')
            ->addBreadcrumb(__('INSEAD Events'), __('INSEAD Events'))
            ->addBreadcrumb(__('Manage Banners'), __('Manage Banners'));
        
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Banner'));
        
        return $resultPage;
    }
}