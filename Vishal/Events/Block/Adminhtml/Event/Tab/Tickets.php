<?php
/**
 * Tab/Tickets.php
 * Path: app/code/Vishal/Events/Block/Adminhtml/Event/Tab/Tickets.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Adminhtml\Event\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Vishal\Events\Model\EventTicketRepository;

class Tickets extends Generic implements TabInterface
{
    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EventTicketRepository $eventTicketRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EventTicketRepository $eventTicketRepository,
        array $data = []
    ) {
        $this->eventTicketRepository = $eventTicketRepository;
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

        $form->setHtmlIdPrefix('tickets_');
        $form->setFieldNameSuffix('tickets');

        $fieldset = $form->addFieldset(
            'tickets_fieldset',
            ['legend' => __('Tickets Management')]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'tickets_container',
                'note',
                [
                    'text' => $this->getTicketsHtml()
                ]
            );
        } else {
            $fieldset->addField(
                'tickets_note',
                'note',
                [
                    'text' => __('You need to save the event first before adding tickets.')
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get tickets HTML
     *
     * @return string
     */
    protected function getTicketsHtml()
    {
        return $this->getLayout()
            ->createBlock(\Vishal\Events\Block\Adminhtml\Event\Tab\TicketsJs::class)
            ->toHtml();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Tickets');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Tickets');
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
