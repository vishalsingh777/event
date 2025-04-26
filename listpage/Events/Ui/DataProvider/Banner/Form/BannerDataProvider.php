<?php
/**
 * INSEAD Events Banner Data Provider
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Ui\DataProvider\Banner\Form;

use Insead\Events\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class BannerDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $bannerCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $bannerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $bannerCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        // Always return the loadedData array, which is initialized as an empty array in the constructor
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }
        
        $items = $this->collection->getItems();
        
        foreach ($items as $banner) {
            $this->loadedData[$banner->getId()] = $banner->getData();
            
            // Process image data
            if (isset($this->loadedData[$banner->getId()]['image']) && !empty($this->loadedData[$banner->getId()]['image'])) {
                try {
                    $imageUrl = $this->getMediaUrl($this->loadedData[$banner->getId()]['image']);
                    $this->loadedData[$banner->getId()]['image'] = [
                        [
                            'name' => $this->loadedData[$banner->getId()]['image'],
                            'url' => $imageUrl,
                            'size' => $this->getImageSize($this->loadedData[$banner->getId()]['image']),
                            'type' => $this->getMimeType($this->loadedData[$banner->getId()]['image'])
                        ]
                    ];
                } catch (\Exception $e) {
                    // If we can't process the image, just keep the original string value
                }
            }
            
            // Process video data
            if (isset($this->loadedData[$banner->getId()]['video']) && !empty($this->loadedData[$banner->getId()]['video'])) {
                try {
                    $videoUrl = $this->getMediaUrl($this->loadedData[$banner->getId()]['video']);
                    $this->loadedData[$banner->getId()]['video'] = [
                        [
                            'name' => $this->loadedData[$banner->getId()]['video'],
                            'url' => $videoUrl,
                            'size' => $this->getVideoSize($this->loadedData[$banner->getId()]['video']),
                            'type' => $this->getMimeType($this->loadedData[$banner->getId()]['video'])
                        ]
                    ];
                } catch (\Exception $e) {
                    // If we can't process the video, just keep the original string value
                }
            }
        }
        
        $data = $this->dataPersistor->get('insead_events_banner');
        
        if (!empty($data)) {
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
            $this->dataPersistor->clear('insead_events_banner');
        }
        
        // Always return an array, even if it's empty
        return $this->loadedData ?? []; // Ensure we return an array
    }

    /**
     * Get media URL
     *
     * @param string $file
     * @return string
     */
    protected function getMediaUrl(string $file): string
    {
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        
        return $mediaBaseUrl . 'insead/events/banner/' . $file;
    }
    
    /**
     * Get image size
     *
     * @param string $file
     * @return int
     */
    protected function getImageSize(string $file): int
    {
        $filePath = $this->getMediaDirectory() . '/insead/events/banner/' . $file;
        
        if (file_exists($filePath)) {
            return filesize($filePath);
        }
        
        return 0;
    }
    
    /**
     * Get video size
     *
     * @param string $file
     * @return int
     */
    protected function getVideoSize(string $file): int
    {
        $filePath = $this->getMediaDirectory() . '/insead/events/banner/' . $file;
        
        if (file_exists($filePath)) {
            return filesize($filePath);
        }
        
        return 0;
    }
    
    /**
     * Get mime type
     *
     * @param string $file
     * @return string
     */
    protected function getMimeType(string $file): string
    {
        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg'
        ];
        
        return $mimeTypes[$fileExtension] ?? 'application/octet-stream';
    }
    
    /**
     * Get media directory
     *
     * @return string
     */
    protected function getMediaDirectory(): string
    {
        return dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/pub/media';
    }
}