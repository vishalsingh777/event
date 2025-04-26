<?php
namespace Insead\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;

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
        $this->_init('insead_events', 'event_id');
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