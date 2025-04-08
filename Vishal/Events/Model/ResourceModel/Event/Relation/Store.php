<?php
/**
 * Store.php
 * Path: app/code/Vishal/Events/Model/ResourceModel/Event/Relation/Store.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\ResourceModel\Event\Relation;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Model\ResourceModel\Event;

class Store implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Event
     */
    protected $resourceEvent;

    /**
     * @param MetadataPool $metadataPool
     * @param Event $resourceEvent
     */
    public function __construct(
        MetadataPool $metadataPool,
        Event $resourceEvent
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId() && $entity instanceof EventInterface) {
            $stores = $this->resourceEvent->getStoreIds($entity->getId());
            $entity->setData('store_id', $stores);
        }

        return $entity;
    }
}