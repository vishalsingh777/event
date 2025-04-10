<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $eventId = $this->getEventId();
        $data = [];
        if ($eventId) {
            $data = [
                'label' => __('Delete Event'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this event?'
                ) . '\', \'' . $this->getUrl('*/*/delete', ['event_id' => $eventId]) . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}