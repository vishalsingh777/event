<?php
namespace Insead\Events\Plugin\Sales;

use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
use Magento\Sales\Model\Order\Item;
use Insead\Events\Helper\Data as EventHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class OrderItemRendererPlugin
{
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;
    
    /**
     * @param EventHelper $eventHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        EventHelper $eventHelper,
        JsonHelper $jsonHelper
    ) {
        $this->eventHelper = $eventHelper;
        $this->jsonHelper = $jsonHelper;
    }
    
    /**
     * Show event info in product name in admin
     *
     * @param DefaultRenderer $subject
     * @param string $result
     * @param Item $item
     * @return string
     */
    public function afterGetColumnHtml(
        DefaultRenderer $subject,
        $result,
        Item $item,
        $column,
        $field = null
    ) {
        if ($column == 'name') {
            $additionalOptions = $this->getItemOptions($item);
            if (!empty($additionalOptions)) {
                $result .= '<dl class="item-options">';
                foreach ($additionalOptions as $option) {
                    $result .= '<dt>' . $option['label'] . '</dt>';
                    $result .= '<dd>' . $option['value'] . '</dd>';
                }
                $result .= '</dl>';
            }
        }
        
        return $result;
    }
    
    /**
     * Get additional options from the item
     *
     * @param Item $item
     * @return array
     */
    protected function getItemOptions(Item $item)
    {
        $options = [];
        
        // Check if product has event options
        if ($item->getProductOptions() && isset($item->getProductOptions()['additional_options'])) {
            try {
                // Decode additional options from JSON
                if (is_string($item->getProductOptions()['additional_options'])) {
                    $options = $this->jsonHelper->jsonDecode(
                        $item->getProductOptions()['additional_options']
                    );
                } else {
                    $options = $item->getProductOptions()['additional_options'];
                }
            } catch (\Exception $e) {
                // Silently fail if JSON is invalid
            }
        }
        
        return $options;
    }
}