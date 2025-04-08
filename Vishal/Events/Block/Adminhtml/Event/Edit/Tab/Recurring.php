<?php
/**
 * Tab/Recurring.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Tab/Recurring.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Vishal\Events\Model\Source\RepeatType;

class Recurring extends Generic implements TabInterface
{
    /**
     * @var RepeatType
     */
    protected $repeatType;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RepeatType $repeatType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RepeatType $repeatType,
        array $data = []
    ) {
        $this->repeatType = $repeatType;
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
            'recurring_fieldset',
            ['legend' => __('Recurring Event Information')]
        );

        $fieldset->addField(
            'recurring',
            'select',
            [
                'name' => 'recurring',
                'label' => __('Is Recurring'),
                'title' => __('Is Recurring'),
                'values' => [
                    ['value' => 0, 'label' => __('No')],
                    ['value' => 1, 'label' => __('Yes')]
                ],
                'note' => __('Set to yes if this is a recurring event')
            ]
        );

        $fieldset->addField(
            'repeat',
            'select',
            [
                'name' => 'repeat',
                'label' => __('Repeat Type'),
                'title' => __('Repeat Type'),
                'values' => $this->repeatType->toOptionArray(),
                'note' => __('Select how often the event repeats')
            ]
        );

        $fieldset->addField(
            'repeat_every',
            'text',
            [
                'name' => 'repeat_every',
                'label' => __('Repeat Every'),
                'title' => __('Repeat Every'),
                'class' => 'validate-number',
                'note' => __('Number of days/weeks/months/years between occurrences')
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
        return __('Recurring');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Recurring');
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


