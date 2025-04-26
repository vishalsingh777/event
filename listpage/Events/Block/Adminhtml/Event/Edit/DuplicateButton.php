<?php
namespace Insead\Events\Block\Adminhtml\Event\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;

class DuplicateButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param Registry $registry
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Registry $registry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $event = $this->registry->registry('current_event');
        
        if ($event && $event->getEventId()) {
            $data = [
                'label' => __('Duplicate'),
                'class' => 'duplicate',
                'on_click' => sprintf("location.href = '%s';", $this->getDuplicateUrl($event->getEventId())),
                'sort_order' => 40,
            ];
        }
        
        return $data;
    }

    /**
     * Get URL for duplicate button
     *
     * @param int $eventId
     * @return string
     */
    public function getDuplicateUrl($eventId)
    {
        return $this->urlBuilder->getUrl('events/event/duplicate', ['event_id' => $eventId]);
    }
}