<?php
/**
 * INSEAD Events Banner Save Controller
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Insead\Events\Model\BannerFactory;
use Insead\Events\Model\Banner;
use Insead\Events\Model\ImageUpload;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
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
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ImageUpload
     */
    protected $imageUpload;

    /**
     * @param Context $context
     * @param BannerFactory $bannerFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param ImageUpload $imageUpload
     */
    public function __construct(
        Context $context,
        BannerFactory $bannerFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        ImageUpload $imageUpload
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->imageUpload = $imageUpload;
        parent::__construct($context);
    }

    /**
     * Save banner
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
     
        if ($data) {
            if (isset($data['status']) && $data['status'] === 'true') {
                $data['status'] = Banner::STATUS_ENABLED;
            }
            
            if (empty($data['banner_id'])) {
                $data['banner_id'] = null;
            }
            
            // Process store IDs
            if (isset($data['store_ids'])) {
                // Handle both array and string inputs for flexibility
                if (is_array($data['store_ids'])) {
                    // If "All Stores" (0) is selected along with specific stores, just use "0"
                    if (in_array('0', $data['store_ids'])) {
                        $data['store_ids'] = '0';
                    } else {
                        // Otherwise convert the array to a comma-separated string
                        $data['store_ids'] = implode(',', $data['store_ids']);
                    }
                }
                // If string - keep as is
            } else {
                // If no store is selected, set default to current store
                $data['store_ids'] = (string)$this->storeManager->getStore()->getId();
            }
            
            // Process event IDs
            if (isset($data['event_ids'])) {
                // Handle both array and string inputs for flexibility
                if (is_array($data['event_ids'])) {
                    if (!empty($data['event_ids'])) {
                        $data['event_ids'] = implode(',', $data['event_ids']);
                    } else {
                        $data['event_ids'] = '';
                    }
                }
                // If string - keep as is
            } else {
                // If no events are selected, set to empty string
                $data['event_ids'] = '';
            }

            /** @var Banner $model */
            $model = $this->bannerFactory->create();

            $id = $this->getRequest()->getParam('banner_id');
            if ($id) {
                try {
                    $model->load($id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('This banner no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            
            // Process image field
            if (isset($data['image']) && is_array($data['image'])) {
                if (!empty($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    try {
                        // Move image from tmp directory
                        $data['image'] = $this->imageUpload->moveFileFromTmp($data['image'][0]['name']);
                        $this->messageManager->addSuccessMessage(__('Image uploaded successfully.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Error uploading image: %1', $e->getMessage()));
                        if ($model->getId() && isset($model['image'])) {
                            $data['image'] = $model['image'];
                        } else {
                            $data['image'] = null;
                        }
                    }
                } elseif (isset($data['image'][0]['name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } else {
                    if ($model->getId() && isset($model['image'])) {
                        $data['image'] = $model['image'];
                    } else {
                        $data['image'] = null;
                    }
                }
            }
            
            // Process video field
            if (isset($data['video']) && is_array($data['video'])) {
                if (!empty($data['video'][0]['name']) && isset($data['video'][0]['tmp_name'])) {
                    try {
                        // Move video from tmp directory
                        $data['video'] = $this->imageUpload->moveFileFromTmp($data['video'][0]['name']);
                        $this->messageManager->addSuccessMessage(__('Video uploaded successfully.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Error uploading video: %1', $e->getMessage()));
                        if ($model->getId() && isset($model['video'])) {
                            $data['video'] = $model['video'];
                        } else {
                            $data['video'] = null;
                        }
                    }
                } elseif (isset($data['video'][0]['name'])) {
                    $data['video'] = $data['video'][0]['name'];
                } else {
                    if ($model->getId() && isset($model['video'])) {
                        $data['video'] = $model['video'];
                    } else {
                        $data['video'] = null;
                    }
                }
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the banner.'));
                $this->dataPersistor->clear('insead_events_banner');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['banner_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the banner: ') . $e->getMessage());
            }

            $this->dataPersistor->set('insead_events_banner', $data);
            return $resultRedirect->setPath('*/*/edit', ['banner_id' => $this->getRequest()->getParam('banner_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}