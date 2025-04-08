<?php
/**
 * Tab/General.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Tab/General.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Vishal\Events\Model\Source\IsActive;

class General extends Generic implements TabInterface
{
    /**
     * @var IsActive
     */
    protected $isActive;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param IsActive $isActive
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        IsActive $isActive,
        array $data = []
    ) {
        $this->isActive = $isActive;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('vishal_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information')]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'event_id',
                'hidden',
                ['name' => 'event_id']
            );
        }

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
                'title' => __('Event Venue')
            ]
        );

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'note' => __('Leave empty to auto-generate from title')
            ]
        );

        $fieldset->addField(
            'color',
            'text',
            [
                'name' => 'color',
                'label' => __('Color'),
                'title' => __('Color'),
                'class' => 'jscolor',
                'note' => __('Used for visual representation (e.g., #FF5733)')
            ]
        );

        $fieldset->addField(
            'youtube_video_url',
            'text',
            [
                'name' => 'youtube_video_url',
                'label' => __('YouTube Video URL'),
                'title' => __('YouTube Video URL'),
                'note' => __('Example: https://www.youtube.com/watch?v=xxxxxxxxxxx')
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => $this->isActive->toOptionArray()
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
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