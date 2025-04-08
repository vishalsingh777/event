<?php
/**
 * ResetButton.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Edit/ResetButton.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ResetButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30
        ];
    }
}
