<?php
namespace Insead\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Insead\Events\Model\ResourceModel\Campus as CampusResource;

class Campus extends AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'insead_event_campus';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CampusResource::class);
    }

    /**
     * Get campus ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('campus_id');
    }

    /**
     * Get campus name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Get campus code (identifier)
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Get campus image
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->getData('image');
    }

    /**
     * Get campus description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData('description');
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
     * Set campus name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Set campus code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * Set campus image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        return $this->setData('image', $image);
    }

    /**
     * Set campus description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData('description', $description);
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