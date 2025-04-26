<?php
namespace Insead\Events\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Insead\Events\Model\Event;

class Status extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->getStatusLabel($item[$fieldName]);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get status label
     *
     * @param int $status
     * @return string
     */
    protected function getStatusLabel($status)
    {
        $statuses = Event::getAvailableStatuses();
        
        return isset($statuses[$status]) ? $statuses[$status] : __('Unknown Status');
    }
}