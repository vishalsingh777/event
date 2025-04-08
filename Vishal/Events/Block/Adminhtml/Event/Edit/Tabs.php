<?php
/**
 * Tabs.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Edit/Tabs.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('event_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Event Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Information'),
                'title' => __('General Information'),
                'content' => $this->getChildHtml('general_section'),
                'active' => true
            ]
        );

        $this->addTab(
            'date_time_section',
            [
                'label' => __('Date & Time'),
                'title' => __('Date & Time'),
                'content' => $this->getChildHtml('date_time_section')
            ]
        );

        $this->addTab(
            'content_section',
            [
                'label' => __('Content'),
                'title' => __('Content'),
                'content' => $this->getChildHtml('content_section')
            ]
        );

        $this->addTab(
            'recurring_section',
            [
                'label' => __('Recurring'),
                'title' => __('Recurring'),
                'content' => $this->getChildHtml('recurring_section')
            ]
        );

        $this->addTab(
            'contact_section',
            [
                'label' => __('Contact Information'),
                'title' => __('Contact Information'),
                'content' => $this->getChildHtml('contact_section')
            ]
        );

        $this->addTab(
            'meta_section',
            [
                'label' => __('Meta Information'),
                'title' => __('Meta Information'),
                'content' => $this->getChildHtml('meta_section')
            ]
        );

        $this->addTab(
            'tickets_section',
            [
                'label' => __('Tickets'),
                'title' => __('Tickets'),
                'content' => $this->getChildHtml('tickets_section')
            ]
        );

        $this->addTab(
            'stores_section',
            [
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'content' => $this->getChildHtml('stores_section')
            ]
        );

        return parent::_beforeToHtml();
    }
}





