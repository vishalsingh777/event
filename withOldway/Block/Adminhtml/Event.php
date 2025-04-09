<?php
namespace Vishal\Events\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Event extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_event';
        $this->_blockGroup = 'Vishal_Events';
        $this->_headerText = __('Events');
        $this->_addButtonLabel = __('Add New Event');
        parent::_construct();
    }
}