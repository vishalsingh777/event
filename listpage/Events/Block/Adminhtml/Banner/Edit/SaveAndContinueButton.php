<?php
/**
 * INSEAD Events Banner Save And Continue Button
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form'
                    ]
                ]
            ],
            'sort_order' => 80
        ];
    }
}