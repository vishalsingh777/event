<?php
namespace Vishal\Events\Block\Adminhtml\Event;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * 
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'event_id';
        $this->_blockGroup = 'Vishal_Events';
        $this->_controller = 'adminhtml_event';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Event'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        $event = $this->coreRegistry->registry('current_event');
        if ($event && $event->getId()) {
            return $this->getUrl('*/*/save', ['event_id' => $event->getId()]);
        }
        return $this->getUrl('*/*/save');
    }
}