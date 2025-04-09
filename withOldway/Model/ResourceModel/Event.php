<?php
namespace Vishal\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

class Event extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->date = $date;
        $this->storeManager = $storeManager;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vishal_events', 'event_id');
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        // Set timestamps
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }
        $object->setUpdatedAt($this->date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * After save process
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveStoreRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = [Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['event_store' => $this->getTable('vishal_event_store')],
                $this->getMainTable() . '.event_id = event_store.event_id',
                []
            )->where('event_store.store_id IN (?)', $storeIds)
            ->order('event_store.store_id DESC')
            ->limit(1);
        }
        return $select;
    }

    /**
     * Save store relation
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveStoreRelation(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('vishal_event_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['event_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['event_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $eventId
     * @return array
     */
    public function lookupStoreIds($eventId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('vishal_event_store'),
            'store_id'
        )->where(
            'event_id = ?',
            (int)$eventId
        );
        return $connection->fetchCol($select);
    }

        /**
     * Get ID by url_key
     *
     * @param string $urlKey
     * @return int|false
     */
    public function getIdByUrlKey($urlKey)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'event_id')
            ->where('url_key = ?', $urlKey);
        
        return $connection->fetchOne($select);
    }

}