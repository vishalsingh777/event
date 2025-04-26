<?php
namespace Insead\Events\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\ConvertToCsv as ConvertToCsvParent;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\DataObject;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends ConvertToCsvParent
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param TimezoneInterface $timezone
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        TimezoneInterface $timezone,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $name = md5(microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';
        
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();
        
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        
        // Generate the file based on component name
        if ($component->getName() === 'events_registration_listing') {
            // Manual export for our custom grid
            $this->exportEventsRegistrationData($dataProvider, $component, $fields, $options, $stream);
        } else {
            // Default Magento behavior for other grids
            $this->exportStandardData($dataProvider, $component, $fields, $options, $stream);
        }
        
        $stream->unlock();
        $stream->close();
        
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * Export events registration data
     *
     * @param mixed $dataProvider
     * @param \Magento\Ui\Component\AbstractComponent $component
     * @param array $fields
     * @param array $options
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     * @return void
     */
    private function exportEventsRegistrationData($dataProvider, $component, $fields, $options, $stream)
    {
        // Get all data from the data provider
        $data = $dataProvider->getData();
        $items = $data['items'] ?? [];
        
        // Process each item and write to CSV
        foreach ($items as $item) {
            $row = [];
            foreach ($fields as $field) {
                $value = $item[$field] ?? '';
                if (isset($options[$field])) {
                    $value = $options[$field][$value] ?? $value;
                }
                $row[] = $value;
            }
            $stream->writeCsv($row);
        }
    }

    /**
     * Export standard data using Magento's approach
     *
     * @param mixed $dataProvider
     * @param \Magento\Ui\Component\AbstractComponent $component
     * @param array $fields
     * @param array $options
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     * @return void
     */
    private function exportStandardData($dataProvider, $component, $fields, $options, $stream)
    {
        try {
            // Try the standard approach with search criteria
            if (method_exists($dataProvider, 'getSearchCriteria') && $dataProvider->getSearchCriteria()) {
                $searchCriteria = $dataProvider->getSearchCriteria()
                    ->setCurrentPage(1)
                    ->setPageSize($this->pageSize);
                $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
                $currentPage = 1;
                
                while ($totalCount > 0) {
                    $items = $dataProvider->getSearchResult()->getItems();
                    foreach ($items as $item) {
                        if ($item instanceof DocumentInterface) {
                            $this->metadataProvider->convertDate($item, $component->getName());
                            $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                        }
                    }
                    $searchCriteria->setCurrentPage(++$currentPage);
                    $totalCount = $totalCount - $this->pageSize;
                }
            } else {
                // Fallback to direct data access
                $items = $dataProvider->getData()['items'] ?? [];
                foreach ($items as $item) {
                    if ($item instanceof DocumentInterface) {
                        $this->metadataProvider->convertDate($item, $component->getName());
                        $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                    } else {
                        // Handle non-DocumentInterface items
                        $row = [];
                        foreach ($fields as $field) {
                            $value = $item[$field] ?? '';
                            if (isset($options[$field])) {
                                $value = $options[$field][$value] ?? $value;
                            }
                            $row[] = $value;
                        }
                        $stream->writeCsv($row);
                    }
                }
            }
        } catch (\Exception $e) {
            // Last resort fallback
            $items = $dataProvider->getData()['items'] ?? [];
            foreach ($items as $item) {
                $row = [];
                foreach ($fields as $field) {
                    $value = $item[$field] ?? '';
                    if (isset($options[$field])) {
                        $value = $options[$field][$value] ?? $value;
                    }
                    $row[] = $value;
                }
                $stream->writeCsv($row);
            }
        }
    }
}