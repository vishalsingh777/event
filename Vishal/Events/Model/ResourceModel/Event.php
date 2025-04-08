<?php
/**
 * Event.php (Resource Model)
 * Path: app/code/Vishal/Events/Model/ResourceModel/Event.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Api\Data\EventInterface;

class Event extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vishal_events', 'event_id');
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $eventId
     * @return array
     */
    public function getStoreIds($eventId)
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
     * Process event data before saving
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        // Generate URL key if not provided
        if (!$object->getUrlKey()) {
            $object->setUrlKey(
                $this->generateUrlKey($object->getEventTitle())
            );
        } else {
            $object->setUrlKey(
                $this->formatUrlKey($object->getUrlKey())
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Process event data after saving
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
     * Save store relation
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveStoreRelation(AbstractModel $object)
    {
        $oldStores = $this->getStoreIds($object->getId());
        $newStores = $object->getStoreId();

        if (empty($newStores)) {
            $newStores = [Store::DEFAULT_STORE_ID];
        }

        $table = $this->getTable('vishal_event_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        $connection = $this->getConnection();

        if ($delete) {
            $connection->delete(
                $table,
                ['event_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete]
            );
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'event_id' => (int)$object->getId(),
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->getStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Check if URL key exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @param int $eventId
     * @return bool
     */
    public function checkUrlKeyExists($urlKey, $storeId, $eventId = null)
    {
        $select = $this->getConnection()->select()
            ->from(['e' => $this->getMainTable()])
            ->join(
                ['es' => $this->getTable('vishal_event_store')],
                'e.event_id = es.event_id',
                []
            )
            ->where('e.url_key = ?', $urlKey)
            ->where('es.store_id IN (?)', [$storeId, Store::DEFAULT_STORE_ID]);

        if ($eventId) {
            $select->where('e.event_id <> ?', $eventId);
        }

        if ($this->getConnection()->fetchRow($select)) {
            return true;
        }
        
        return false;
    }

    /**
     * Format URL key
     *
     * @param string $str
     * @return string
     */
    protected function formatUrlKey($str)
    {
        $str = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $str = strtolower($str);
        $str = trim($str, '-');
        return $str;
    }

    /**
     * Generate URL key
     *
     * @param string $title
     * @return string
     */
    protected function generateUrlKey($title)
    {
        return $this->formatUrlKey($title);
    }

    /**
     * Save an object using entity manager
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete an object using entity manager
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}