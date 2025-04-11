<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class BlockedDates extends Template
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
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->registry->registry('current_event');
    }

    /**
     * Get blocked dates
     *
     * @return array
     */
    public function getBlockedDates()
    {
        $event = $this->getEvent();
        if (!$event) {
            return [];
        }

        return $event->getBlockDates();
    }

    /**
     * Format date for display
     *
     * @param string|null $date
     * @param int $format
     * @param bool $showTime
     * @param string|null $timezone
     * @return string
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false, $timezone = null)
    {
        try {
            if ($date === null) {
                return ''; // Return empty string if no date is provided
            }

            $dateObject = new \DateTime($date);

            // If you need to show the time, you can modify the format accordingly
            if ($showTime) {
                return $dateObject->format('F j, Y, g:i A');
            } else {
                return $dateObject->format('F j, Y');
            }
        } catch (\Exception $e) {
            return $date;
        }
    }

}