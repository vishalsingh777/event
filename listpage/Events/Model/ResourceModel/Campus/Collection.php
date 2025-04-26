<?php
namespace Insead\Events\Model\ResourceModel\Campus;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Insead\Events\Model\Campus as CampusModel;
use Insead\Events\Model\ResourceModel\Campus as CampusResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'campus_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CampusModel::class, CampusResource::class);
    }
}