<?php
namespace Vishal\Events\Block\Adminhtml\Event;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Vishal\Events\Model\Event;
use Magento\Framework\DataObject;
use Magento\Store\Model\System\Store;

class Grid extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $eventCollectionFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $eventCollectionFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->systemStore = $systemStore;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('eventGrid');
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->eventCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'event_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'event_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'event_title',
            [
                'header' => __('Title'),
                'index' => 'event_title',
                'class' => 'xxx'
            ]
        );

        $this->addColumn(
            'event_venue',
            [
                'header' => __('Venue'),
                'index' => 'event_venue'
            ]
        );

        $this->addColumn(
            'start_date',
            [
                'header' => __('Start Date'),
                'index' => 'start_date',
                'type' => 'datetime'
            ]
        );

        $this->addColumn(
            'end_date',
            [
                'header' => __('End Date'),
                'index' => 'end_date',
                'type' => 'datetime'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [
                    Event::STATUS_DISABLED => __('Disabled'),
                    Event::STATUS_ENABLED => __('Enabled')
                ]
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'header' => __('Store View'),
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback' => [$this, '_filterStoreCondition']
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'event_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['event_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $collection->addStoreFilter($value);
    }
}