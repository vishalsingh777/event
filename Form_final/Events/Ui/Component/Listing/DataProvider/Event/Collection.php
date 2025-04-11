<?php
/**
 * Collection.php
 * Path: app/code/Vishal/Events/Ui/Component/Listing/DataProvider/Event/Collection.php
 */

declare(strict_types=1);

namespace Vishal\Events\Ui\Component\Listing\DataProvider\Event;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * Override _initSelect to add custom columns
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        
        // Add store names
        $this->getSelect()->joinLeft(
            ['store_table' => $this->getTable('vishal_event_store')],
            'main_table.event_id = store_table.event_id',
            []
        )->joinLeft(
            ['store' => $this->getTable('store')],
            'store_table.store_id = store.store_id',
            ['store_name' => 'GROUP_CONCAT(store.name SEPARATOR ", ")']
        )->group('main_table.event_id');
    }
}