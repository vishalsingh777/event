<?php
/**
 * INSEAD Events Banner Delete Button
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        $bannerId = $this->getBannerId();
        
        if ($bannerId) {
            $data = [
                'label' => __('Delete Banner'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this banner?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        
        return $data;
    }

    /**
     * Get URL for delete button
     *
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['banner_id' => $this->getBannerId()]);
    }
}