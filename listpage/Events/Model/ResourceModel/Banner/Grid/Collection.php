<?php
/**
 * INSEAD Events Banner Grid Collection
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model\ResourceModel\Banner\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

class Collection extends SearchResult
{
    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable = 'insead_events_banner',
        $resourceModel = \Insead\Events\Model\ResourceModel\Banner::class,
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        parent::setSearchCriteria($searchCriteria);
        
        if ($searchCriteria !== null) {
            if ($searchCriteria->getFilterGroups()) {
                foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                    foreach ($filterGroup->getFilters() as $filter) {
                        $condition = $filter->getConditionType() ?: 'eq';
                        $this->addFieldToFilter(
                            $filter->getField(),
                            [$condition => $filter->getValue()]
                        );
                    }
                }
            }
        }
        
        return $this;
    }
}