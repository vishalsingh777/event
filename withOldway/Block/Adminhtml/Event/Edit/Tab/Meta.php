<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Meta extends Generic implements TabInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('event_');

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Meta Information')]
        );

        $fieldset->addField(
            'page_title',
            'text',
            [
                'name' => 'page_title',
                'label' => __('Page Title'),
                'title' => __('Page Title')
            ]
        );

        $fieldset->addField(
            'keywords',
            'textarea',
            [
                'name' => 'keywords',
                'label' => __('Keywords'),
                'title' => __('Keywords'),
                'note' => __('Comma-separated')
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description')
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
        return __('Meta Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Meta Information');
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