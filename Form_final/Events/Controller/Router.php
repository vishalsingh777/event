<?php
namespace Vishal\Events\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class Router implements RouterInterface
{
    protected $actionFactory;
    /**
     * @var EventResource
     */
    private $resource;

    public function __construct(
        ActionFactory $actionFactory,
        EventResource $resource
    ) {
        $this->actionFactory = $actionFactory;
        $this->resource = $resource;
    }
 
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        // Check if URL starts with 'events/'
        if (strpos($identifier, 'events/') !== 0) {
            return null;
        }

        // Get the url_key part (after events/)
        $urlKey = substr($identifier, strlen('events/'));

        /** @var \Vishal\Events\Model\Event $event */
        $eventId = $this->resource->getIdByUrlKey($urlKey);

        if (!$eventId) {
            return null; 
        }
        $request->setModuleName('events')
            ->setControllerName('event')
            ->setActionName('view')
            ->setParam('id', $eventId);

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
