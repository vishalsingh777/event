<?php
namespace Insead\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Insead\Events\Api\EventRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

abstract class GenericButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository
    ) {
        $this->context = $context;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Return event ID
     *
     * @return int|null
     */
    public function getEventId()
    {
        try {
            $eventId = $this->context->getRequest()->getParam('event_id');
            
            if ($eventId) {
                $event = $this->eventRepository->getById($eventId);
                return $event->getId();
            }
        } catch (NoSuchEntityException $e) {
            // Handle exception
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
