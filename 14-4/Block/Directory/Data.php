<?php
namespace Vishal\Events\Block\Directory;

class Data extends \Magento\Directory\Block\Data
{
    /**
     * Get country HTML select
     *
     * @param string $defValue Default value
     * @param string $name Select name
     * @param string $id Select id
     * @param string $title Select title
     * @return string
     */
    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        if ($defValue === null) {
            $defValue = $this->getCountryId();
        }
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            $options = $this->getCountryCollection()->toOptionArray();
            $this->_configCacheType->save(
                $this->getSerializer()->serialize($options),
                $cacheKey,
                ['directory']
            );
        }
        $html = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setName(
            $name
        )->setId(
            $id
        )->setTitle(
            __($title)
        )->setValue(
            $defValue
        )->setOptions(
            $options
        )->setExtraParams(
            'data-validate="{\'validate-select\':true}"'
        )->getHtml();

        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $html;
    }
    
    /**
     * Get country options with pre-selected countries at the top
     *
     * @return array
     */
    public function getCountryOptions()
    {
        $countries = $this->getCountryCollection()->toOptionArray(false);
        
        // Sort countries by name
        usort($countries, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });
        
        // Create an associative array with country code as key
        $countryOptions = [];
        foreach ($countries as $country) {
            $countryOptions[$country['value']] = $country['label'];
        }
        
        return $countryOptions;
    }
}