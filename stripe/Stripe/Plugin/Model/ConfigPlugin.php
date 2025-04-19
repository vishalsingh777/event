<?php
namespace Insead\Stripe\Plugin\Model;
use Ewave\InseadIntegration\Api\Data\Sales\OrderItemExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Ewave\InseadIntegration\Api\Data\Sales\OrderExtensionInterface;

class ConfigPlugin
{
    public function afterGetMetadata(
        \StripeIntegration\Payments\Model\Config $subject,
        $result,
        $order
    ) {
        // Modify the metadata for the order here
        $metadata = $result;
        $taxId = $order->getData(OrderExtensionInterface::TAX_ID) ?? '';
        // Retrieve product SKUs from the order
        $productSkus = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $sku = $item->getSku();
            if (!isset($productSkus[$sku])) {
                $productSkus[$sku] = $sku; // Associative array ensures uniqueness
            }
        }

        $orderItem = $this->getFirstParentOrderItem($order);
        $budgetCode = $orderItem->getData(OrderItemExtensionInterface::BUDGET_CODE_FINANCE_SECTION);
        // Get the store name
        $storeName = $order->getStore()->getName();
  
        // Add custom metadata based on the order
        $metadata['Store Name'] = $storeName;
        $metadata['Tax ID (NRIC Number/ FIN)'] = $taxId;
        $metadata['BUDGET CODE'] = $budgetCode;
        // Add product SKUs to metadata
        $metadata['Product Skus'] = implode(', ', $productSkus);

        // Return the modified metadata
        return $metadata;
    }

    /**
     * @param OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface | \Magento\Sales\Model\Order\Item
     */
    protected function getFirstParentOrderItem(OrderInterface $order)
    {
        foreach ($order->getItems() as $orderItem) {
            if (!$orderItem->getParentItemId()) {
                return $orderItem;
            }
        }

        return null;
    }
}
