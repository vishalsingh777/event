<?php
/**
 * Validate.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/Validate.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Vishal\Events\Model\EventRepository;
use Vishal\Events\Model\ResourceModel\Event as ResourceEvent;
use Magento\Framework\Controller\Result\Json;

class Validate extends Action
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var ResourceEvent
     */
    protected $resourceEvent;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param EventRepository $eventRepository
     * @param ResourceEvent $resourceEvent
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        EventRepository $eventRepository,
        ResourceEvent $resourceEvent
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->eventRepository = $eventRepository;
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * Validate action
     *
     * @return Json
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setData([
            'error' => false,
            'messages' => []
        ]);

        $eventData = $this->getRequest()->getParam('event', []);
        $eventId = $eventData['event_id'] ?? null;

        try {
            // Validate URL key
            if (!empty($eventData['url_key'])) {
                $urlKey = $eventData['url_key'];
                $storeId = !empty($eventData['store_id']) && is_array($eventData['store_id']) 
                    ? (int) current($eventData['store_id']) 
                    : 0;

                if ($this->resourceEvent->checkUrlKeyExists($urlKey, $storeId, $eventId)) {
                    $response->setData('error', true);
                    $response->setData('messages', ['URL Key for specified store already exists.']);
                }
            }

            // Validate required fields
            if (empty($eventData['event_title'])) {
                $response->setData('error', true);
                $response->setData('messages', ['Event title is required.']);
            }

            // Validate date
            if (empty($eventData['start_date'])) {
                $response->setData('error', true);
                $response->setData('messages', ['Start date is required.']);
            }

        } catch (\Exception $e) {
            $response->setData('error', true);
            $response->setData('messages', [$e->getMessage()]);
        }

        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response->getData());
    }
}