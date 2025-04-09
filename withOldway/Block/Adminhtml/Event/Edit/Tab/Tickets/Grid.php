<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Tickets;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Registry;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory;
use Magento\Catalog\Model\Config\Source\Product\Options\Type as ProductOptionsType;

class Grid extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'Vishal_Events::event/edit/tab/tickets/grid.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var ProductOptionsType
     */
    protected $productOptionsType;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $ticketCollectionFactory
     * @param ProductOptionsType $productOptionsType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $ticketCollectionFactory,
        ProductOptionsType $productOptionsType,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->productOptionsType = $productOptionsType;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->registry->registry('current_event');
    }

    /**
     * Get tickets
     *
     * @return array
     */
    public function getTickets()
    {
        $event = $this->getEvent();
        $tickets = [];
        
        if ($event && $event->getId()) {
            $collection = $this->ticketCollectionFactory->create();
            $collection->addEventFilter($event->getId());
            foreach ($collection as $ticket) {
                $tickets[] = $ticket;
            }
        }
        
        return $tickets;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Add Ticket'),
                'class' => 'action-add',
                'id' => 'add_new_ticket'
            ]
        );
        
        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Add button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Get row init JavaScript code
     *
     * @return string
     */
    public function getRowInitJsCode()
    {
        return 'ticketControl.addItem()';
    }
}