<?php
/**
 * INSEAD Events Banner Video Upload Controller
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

class VideoUpload extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::banner';

    /**
     * @var ImageUploader
     */
    protected $videoUploader;

    /**
     * @param Context $context
     * @param ImageUploader $videoUploader
     */
    public function __construct(
        Context $context,
        ImageUploader $videoUploader
    ) {
        parent::__construct($context);
        $this->videoUploader = $videoUploader;
    }

    /**
     * Upload video file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $videoId = $this->_request->getParam('param_name', 'video');

        try {
            $result = $this->videoUploader->saveFileToTmpDir($videoId);
            
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