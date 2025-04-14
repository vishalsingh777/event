<?php
namespace Vishal\Events\Plugin;

use Magento\Framework\Event\Config\Reader;

class AddEventObserver
{
    /**
     * Add event observer configuration
     *
     * @param Reader $subject
     * @param array $result
     * @return array
     */
    public function afterRead(Reader $subject, $result)
    {
        // Add observer for cart product add event
        if (isset($result['checkout_cart_product_add_after']['observers'])) {
            $result['checkout_cart_product_add_after']['observers']['vishal_events_cart_item_update'] = [
                'name' => 'vishal_events_cart_item_update',
                'instance' => 'Vishal\Events\Observer\EventCartItemUpdateObserver',
                'method' => 'execute',
                'disabled' => false
            ];
        }
        
        return $result;
    }
}