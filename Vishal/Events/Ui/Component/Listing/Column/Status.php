<?php
/**
 * Status.php
 * Path: app/code/Vishal/Events/Ui/Component/Listing/Column/Status.php
 */

declare(strict_types=1);

namespace Vishal\Events\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Vishal\Events\Model\Source\IsActive;

class Status extends Column
{
    /**
     * @var IsActive
     */
    protected $isActive;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param IsActive $isActive
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsActive $isActive,
        array $components = [],
        array $data = []
    ) {
        $this->isActive = $isActive;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $options = $this->isActive->toArray();
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $item[$this->getData('name')] = $options[$item[$this->getData('name')]];
                }
            }
        }

        return $dataSource;
    }
}