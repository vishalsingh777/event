<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class DateTimeModal extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

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
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current event
     *
     * @return \Vishal\Events\Model\Event|null
     */
    public function getEvent()
    {
        return $this->registry->registry('current_event');
    }
}