<?php
namespace Insead\Events\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Insead\Events\Model\Category as CategoryModel;
use Insead\Events\Model\ResourceModel\Category as CategoryResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'category_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CategoryModel::class, CategoryResource::class);
    }
}