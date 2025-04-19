<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Insead\Stripe\Ui\Component\SubscriptionListing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class LiveMode
 */
class LiveMode extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,        
        array $components = [],
        array $data = []
    ) {       
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
            $liveMode = isset($subscription['livemode']) ? $subscription['livemode'] : '0';

                if ($liveMode == 0) {
                    $item['livemode'] = 'Test';
                } else {
                    $item['livemode'] = 'Live';
                }
            }
        }
        return $dataSource;
    }
}
