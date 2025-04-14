<?php
namespace Vishal\Events\Plugin\Checkout;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Serialize\Serializer\Json;

class CartItemRendererPlugin
{
    /**
     * @var Json
     */
    protected $jsonSerializer;
    
    /**
     * @param Json $jsonSerializer
     */
    public function __construct(
        Json $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
    }
    
    /**
     * Override product name display in cart
     *
     * @param Renderer $subject
     * @param string $result
     * @return string
     */
    public function afterGetProductName(Renderer $subject, $result)
    {
        $item = $subject->getItem();
        $additionalOptions = $this->getItemOptions($item);
        
        if (!empty($additionalOptions)) {
            // Find the Event name from additional options
            foreach ($additionalOptions as $option) {
                if ($option['label'] == 'Event') {
                    // Return event name instead of product name
                    return $option['value'];
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Add event information to product options in cart
     *
     * @param Renderer $subject
     * @param array $result
     * @return array
     */
    public function afterGetOptionList(Renderer $subject, $result)
    {
        $item = $subject->getItem();
        $additionalOptions = $this->getItemOptions($item);
/*        print_r($additionalOptions);die;
        if (!empty($additionalOptions)) {
            // Add all options except Event name (already shown as product name)
            foreach ($additionalOptions as $option) {
                if ($option['label'] != 'Event') {
                    $result[] = [
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'option_id' => '',
                        'option_value' => '',
                    ];
                }
            }
        }*/
        
        return $result;
    }
    
    /**
     * Get item options with event information
     *
     * @param Item $item
     * @return array
     */
    protected function getItemOptions(Item $item)
    {
        $options = [];
        
        // Get additional options with event details
        $optionStr = $item->getOptionByCode('additional_options');
        
        if ($optionStr && $optionStr->getValue()) {
            try {
                $options = $this->jsonSerializer->unserialize($optionStr->getValue());
            } catch (\Exception $e) {
                // Silently fail on JSON decoding error
            }
        }
        
        return $options;
    }
}