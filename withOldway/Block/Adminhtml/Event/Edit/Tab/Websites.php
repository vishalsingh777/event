<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;

class Websites extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
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
            'websites_fieldset',
            ['legend' => __('Event in Websites')]
        );

        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name' => 'stores[]',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ]
        );

        // Set special renderer for 'store' field
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
        );
        $field->setRenderer($renderer);

        // This is very important for loading the correct values
        if (!$model->hasData('store_id') && $model->getId()) {
            $storeIds = $model->getResource()->lookupStoreIds($model->getId());
            $model->setData('store_id', $storeIds);
            $model->setData('stores', $storeIds);
        }

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
        return __('Event in Websites');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Event in Websites');
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