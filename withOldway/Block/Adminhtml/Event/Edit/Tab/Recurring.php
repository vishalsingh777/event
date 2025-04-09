<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Vishal\Events\Model\Event;

class Recurring extends Generic implements TabInterface
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
            'recurring_fieldset',
            ['legend' => __('Recurring Events')]
        );

        $recurringField = $fieldset->addField(
            'recurring',
            'select',
            [
                'name' => 'recurring',
                'label' => __('Recurring'),
                'title' => __('Recurring'),
                'options' => Event::getRecurringOptions()
            ]
        );

        $repeatField = $fieldset->addField(
            'repeat',
            'select',
            [
                'name' => 'repeat',
                'label' => __('Repeat'),
                'title' => __('Repeat'),
                'options' => Event::getRepeatOptions(),
                'note' => __('How often to repeat this event')
            ]
        );

        $repeatEveryField = $fieldset->addField(
            'repeat_every',
            'text',
            [
                'name' => 'repeat_every',
                'label' => __('Repeat Every'),
                'title' => __('Repeat Every'),
                'class' => 'validate-number',
                'note' => __('Weeks / Days / Months / Years')
            ]
        );

        // Setting field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($recurringField->getHtmlId(), $recurringField->getName())
                ->addFieldMap($repeatField->getHtmlId(), $repeatField->getName())
                ->addFieldMap($repeatEveryField->getHtmlId(), $repeatEveryField->getName())
                ->addFieldDependence($repeatField->getName(), $recurringField->getName(), '1')
                ->addFieldDependence($repeatEveryField->getName(), $recurringField->getName(), '1')
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
        return __('Recurring Events');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Recurring Events');
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