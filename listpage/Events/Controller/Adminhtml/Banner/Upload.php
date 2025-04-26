<?php
/**
 * INSEAD Events Banner Image Upload Controller
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::banner';

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @param Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
     public function execute()
        {
            $imageId = $this->_request->getParam('param_name', 'image');

            try {
                $result = $this->imageUploader->saveFileToTmpDir($imageId);
                
                // Ensure the file path doesn't contain extra slashes
                if (isset($result['name'])) {
                    $result['name'] = ltrim($result['name'], '/');
                }
                
            } catch (\Exception $e) {
                $result = [
                    'error' => $e->getMessage(),
                    'errorcode' => $e->getCode()
                ];
            }
            
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
        }
}