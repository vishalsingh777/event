<?php
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
   protected function _prepareLayout()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Information'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\General'
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'date_section',
            [
                'label' => __('Date & Time'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Date'
                )->toHtml()
            ]
        );

        $this->addTab(
            'content_section',
            [
                'label' => __('Content'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Content'
                )->toHtml()
            ]
        );

        $this->addTab(
            'recurring_section',
            [
                'label' => __('Recurring Events'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Recurring'
                )->toHtml()
            ]
        );

        $this->addTab(
            'contact_section',
            [
                'label' => __('Contact Information'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Contact'
                )->toHtml()
            ]
        );

        $this->addTab(
            'meta_section',
            [
                'label' => __('Meta Information'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Meta'
                )->toHtml()
            ]
        );

        $this->addTab(
            'websites_section',
            [
                'label' => __('Event in Websites'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Websites'
                )->toHtml()
            ]
        );
/*
        $this->addTab(
            'tickets_section',
            [
                'label' => __('Event Tickets'),
                'content' => $this->getLayout()->createBlock(
                    'Vishal\Events\Block\Adminhtml\Event\Edit\Tab\Tickets'
                )->toHtml()
            ]
        );*/

        return parent::_prepareLayout();
    }

}