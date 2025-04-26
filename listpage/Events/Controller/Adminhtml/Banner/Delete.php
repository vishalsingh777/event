<?php
/**
 * INSEAD Events Banner Delete Controller
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Insead\Events\Model\BannerFactory;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::banner';

    /**
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @param Context $context
     * @param BannerFactory $bannerFactory
     */
    public function __construct(
        Context $context,
        BannerFactory $bannerFactory
    ) {
        $this->bannerFactory = $bannerFactory;
        parent::__construct($context);
    }

    /**
     * Delete banner
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        // Check if we know what should be deleted
        $id = $this->getRequest()->getParam('banner_id');
        if ($id) {
            try {
                // Init model and delete
                $model = $this->bannerFactory->create();
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                
                $model->delete();
                
                // Display success message
                $this->messageManager->addSuccessMessage(__('The banner has been deleted.'));
                
                // Go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                // Display error message
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                // Display error message
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the banner.'));
            }
            
            // Go back to edit form
            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
        }
        
        // Display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a banner to delete.'));
        
        // Go to grid
        return $resultRedirect->setPath('*/*/');
    }
}