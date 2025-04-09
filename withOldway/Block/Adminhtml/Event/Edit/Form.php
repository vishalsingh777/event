<?php
namespace Vishal\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * Prepare form data
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}