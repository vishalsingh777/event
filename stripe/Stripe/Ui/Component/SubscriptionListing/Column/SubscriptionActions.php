<?php

namespace Insead\Stripe\Ui\Component\SubscriptionListing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class SubscriptionActions extends Column
{
    /**
     * __construct
     *
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $mode = isset($subscription['livemode']) ? '' : 'test';
                if (isset($item['subscription_id'])) {
                    $link = "https://dashboard.stripe.com/".$mode."/subscriptions/";
                    $item[$fieldName] = "<a href='" . $link . $item["subscription_id"] . "' target='_blank' rel='noopener noreferrer'>" .$item['subscription_id']."</a>";
                }                
            }
        }
        return $dataSource;
    }    
}
