<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory as TicketCollectionFactory;

class Tickets extends Extended implements TabInterface
{
    protected $productCollectionFactory;
    protected $_coreRegistry;
    protected $ticketCollectionFactory;

    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Registry $registry,
        ProductCollectionFactory $productCollectionFactory,
        TicketCollectionFactory $ticketCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('event_tickets_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);  // Enable AJAX for the grid
    }

    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku', 'price']);
        $collection->addAttributeToFilter('status', 1);
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        $collection->addAttributeToFilter('type_id', ['in' => ['simple', 'virtual', 'downloadable']]);
        
        $event = $this->_coreRegistry->registry('current_event');
        if ($event && $event->getId()) {
            $ticketCollection = $this->ticketCollectionFactory->create();
            $ticketCollection->addFieldToFilter('event_id', $event->getId());
            
            $productIds = [];
            foreach ($ticketCollection as $ticket) {
                if ($ticket->getProductId()) {
                    $productIds[] = $ticket->getProductId();
                }
            }

            if (!empty($productIds)) {
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_tickets',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_tickets[]',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'field_name' => 'in_tickets[]'
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
                'header' => __('Product Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrencyCode(),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/ticketsGrid', ['_current' => true]);
    }

    protected function _getSelectedProducts()
    {
        $event = $this->_coreRegistry->registry('current_event');
        $productIds = [];
        
        if ($event && $event->getId()) {
            $ticketCollection = $this->ticketCollectionFactory->create();
            $ticketCollection->addFieldToFilter('event_id', $event->getId());
            
            foreach ($ticketCollection as $ticket) {
                if ($ticket->getProductId()) {
                    $productIds[] = $ticket->getProductId();
                }
            }
        }
        
        return $productIds;
    }

    public function getTabLabel()
    {
        return __('Event Tickets Products');
    }

    public function getTabTitle()
    {
        return __('Event Tickets Products');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
