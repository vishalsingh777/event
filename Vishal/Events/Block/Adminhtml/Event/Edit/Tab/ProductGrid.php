<?php
/**
 * Tab/ProductGrid.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Tab/ProductGrid.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Vishal\Events\Model\EventTicketRepository;

class ProductGrid extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $productCollectionFactory
     * @param Registry $coreRegistry
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param EventTicketRepository $eventTicketRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $productCollectionFactory,
        Registry $coreRegistry,
        Status $productStatus,
        Visibility $productVisibility,
        EventTicketRepository $eventTicketRepository,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->eventTicketRepository = $eventTicketRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        if ($this->getEvent() && $this->getEvent()->getId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
    }

    /**
     * Get current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('vishal_event');
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility')
            ->addAttributeToFilter('type_id', ProductType::TYPE_SIMPLE)
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->addAttributeToFilter('visibility', ['in' => $this->productVisibility->getVisibleInSiteIds()]);
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );
        
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx'
            ]
        );
        
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );
        
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->productStatus->getOptionArray()
            ]
        );
        
        return parent::_prepareColumns();
    }

    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('events/event/productGrid', ['_current' => true]);
    }

    /**
     * Get selected products
     *
     * @return array
     */
    protected function getSelectedProducts()
    {
        $products = [];
        
        // Get currently assigned products
        $event = $this->getEvent();
        if ($event && $event->getId()) {
            $tickets = $this->eventTicketRepository->getTicketsForEvent($event->getId());
            foreach ($tickets as $ticket) {
                if ($ticket->getProductId()) {
                    $products[$ticket->getProductId()] = $ticket->getProductId();
                }
            }
        }
        
        return array_values($products);
    }
}