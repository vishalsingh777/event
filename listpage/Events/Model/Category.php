<?php
namespace Insead\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Insead\Events\Model\ResourceModel\Category as CategoryResource;

class Category extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'insead_event_category';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CategoryResource::class);
    }

    /**
     * Get category ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('category_id');
    }

    /**
     * Get category name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Get category code (identifier)
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Get icon class
     *
     * @return string|null
     */
    public function getIconClass()
    {
        return $this->getData('icon_class');
    }
    
    /**
     * Get sort order
     *
     * @return int|null
     */
    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Set category code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * Set icon class
     *
     * @param string $iconClass
     * @return $this
     */
    public function setIconClass($iconClass)
    {
        return $this->setData('icon_class', $iconClass);
    }
    
    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData('sort_order', $sortOrder);
    }
}