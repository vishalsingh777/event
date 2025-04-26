<?php
namespace Insead\Events\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class ImageUpload
{
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $baseTmpPath = 'insead/events/banner/tmp';

    /**
     * @var string
     */
    private $basePath = 'insead/events/banner';

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
    }

    /**
     * Move file from temp directory to final directory
     *
     * @param string $imageName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveFileFromTmp($imageName)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        
        $baseImagePath = $this->getFilePath($basePath, $imageName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);
        
        try {
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            return $imageName;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while moving the file: %1', $e->getMessage())
            );
        }
    }

    /**
     * Save image upload
     *
     * @param string $fileId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveFileToTmpDir($fileId)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(true);
        
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));
        
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File cannot be saved to the destination folder.')
            );
        }
        
        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['path'] = str_replace('\\', '/', $result['path']);
        $result['url'] = $this->getBaseUrl() . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];
        
        return $result;
    }
    
    /**
     * Save video upload
     *
     * @param string $fileId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveVideoToTmpDir($fileId)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions(['mp4', 'webm', 'ogg']);
        $uploader->setAllowRenameFiles(true);
        
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));
        
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File cannot be saved to the destination folder.')
            );
        }
        
        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['path'] = str_replace('\\', '/', $result['path']);
        $result['url'] = $this->getBaseUrl() . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];
        
        return $result;
    }

    /**
     * Get base temp path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get file path
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }
}