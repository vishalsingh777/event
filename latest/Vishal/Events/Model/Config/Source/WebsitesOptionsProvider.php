<?php
/**
 * Products.php
 * Path: app/code/Vishal/Events/Model/Config/Source/Products.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\Config\Source;

class WebsitesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $store;

    /**
     * @param \Magento\Store\Model\System\Store $store
     */
    public function __construct(\Magento\Store\Model\System\Store $store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->store->getWebsiteValuesForForm();
    }
}
