<?php
/**
 * Collection.php
 * Path: app/code/Vishal/Events/Model/ResourceModel/Event/Collection.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\ResourceModel\Event;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Vishal\Events\Model\Event;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'event_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(Event::class, EventResource::class);
        $this->_map['fields']['event_id'] = 'main_table.event_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add store filter
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }
        return $this;
    }

    /**
     * Add store filter logic
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Perform operations before rendering filters
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('vishal_event_store', 'event_id');
        parent::_renderFiltersBefore();
    }

    /**
     * Join store relation table only if store filter exists
     */
    protected function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
    }

    /**
     * Perform operations after load
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('vishal_event_store', 'event_id');
        return parent::_afterLoad();
    }

    /**
     * Add store data to each item
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['store_table' => $this->getTable($tableName)])
                ->where('store_table.' . $columnName . ' IN (?)', $items);
            $result = $connection->fetchAll($select);

            $storesData = [];
            foreach ($result as $storeData) {
                $storesData[$storeData[$columnName]][] = $storeData['store_id'];
            }

            foreach ($this as $item) {
                $linkedId = $item->getData($columnName);
                if (!isset($storesData[$linkedId])) {
                    continue;
                }
                $item->setData('store_id', $storesData[$linkedId]);
                $item->setData('stores', $storesData[$linkedId]);
            }
        }
    }

    /**
     * Add filter by status
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    /**
     * Add filter for active status
     */
    public function addActiveFilter()
    {
        return $this->addStatusFilter(1);
    }

    /**
     * Add future events filter
     */
    public function addFutureEventsFilter()
    {
        $now = new \DateTime();
        $this->addFieldToFilter('start_date', ['gteq' => $now->format('Y-m-d H:i:s')]);
        return $this;
    }

    /**
     * Order by start date
     */
    public function addStartDateOrder($dir = 'ASC')
    {
        $this->addOrder('start_date', $dir);
        return $this;
    }
}
