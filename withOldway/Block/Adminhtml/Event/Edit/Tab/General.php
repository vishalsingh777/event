<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Vishal\Events\Model\Event;

class General extends Generic implements TabInterface
{

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var Event $model */
        $model = $this->_coreRegistry->registry('current_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('event_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Form')]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'event_id',
                'hidden',
                ['name' => 'event_id']
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => [
                    Event::STATUS_ENABLED => __('Yes'),
                    Event::STATUS_DISABLED => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'event_title',
            'text',
            [
                'name' => 'event_title',
                'label' => __('Event Title'),
                'title' => __('Event Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'event_venue',
            'text',
            [
                'name' => 'event_venue',
                'label' => __('Event Venue'),
                'title' => __('Event Venue'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'color',
            'select',
            [
                'name' => 'color',
                'label' => __('Color'),
                'title' => __('Color'),
                'required' => false,
                'options' => [
                    'green' => __('Green'),
                    'blue' => __('Blue'),
                    'red' => __('Red'),
                    'orange' => __('Orange'),
                    'purple' => __('Purple')
                ]
            ]
        );

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('URL Prefix'),
                'title' => __('URL Prefix'),
                'note' => __('Will be used in the URL to access the event page. Leave empty for automatic generation.')
            ]
        );

        $fieldset->addField(
            'youtube_video_url',
            'text',
            [
                'name' => 'youtube_video_url',
                'label' => __('YouTube Video URL'),
                'title' => __('YouTube Video URL')
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Information');
    }


    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}