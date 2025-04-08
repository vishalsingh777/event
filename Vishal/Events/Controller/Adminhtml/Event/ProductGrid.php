<?php
/**
 * ProductGrid.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/ProductGrid.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;

class ProductGrid extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    /**
     * Product grid for AJAX request
     *
     * @return Layout
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}