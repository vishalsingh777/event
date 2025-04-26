<?php
namespace Insead\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Insead\Events\Api\EventRepositoryInterface;

class InlineEdit extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::manage_events';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        EventRepositoryInterface $eventRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $eventId) {
                    try {
                        $model = $this->eventRepository->getById($eventId);
                        $model->setData(array_merge($model->getData(), $postItems[$eventId]));
                        $this->eventRepository->save($model);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithEventId(
                            $model,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add event title to error message
     *
     * @param \Insead\Events\Api\Data\EventInterface $event
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithEventId($event, $errorText)
    {
        return '[Event ID: ' . $event->getId() . '] ' . $errorText;
    }
}