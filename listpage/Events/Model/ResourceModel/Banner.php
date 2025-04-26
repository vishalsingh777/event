<?php
/**
 * INSEAD Events Banner Resource Model
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;

class Banner extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        ?string $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('insead_events_banner', 'banner_id');
    }

    /**
     * Process banner data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        // Set creation time for new banners
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateTime->gmtDate());
        }
        
        // Always update the modified time
        $object->setUpdatedAt($this->dateTime->gmtDate());
        

        return parent::_beforeSave($object);
    }

    /**
     * Process banner data after load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        // Convert comma-separated store IDs and event IDs to arrays

        return parent::_afterLoad($object);
    }
}