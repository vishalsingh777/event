<?php
/**
 * InlineEdit.php
 * Path: app/code/Vishal/Events/Controller/Adminhtml/Event/InlineEdit.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Vishal\Events\Model\EventRepository;

class InlineEdit extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vishal_Events::event_save';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param EventRepository $eventRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        EventRepository $eventRepository
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
                foreach ($postItems as $eventId => $eventData) {
                    try {
                        $event = $this->eventRepository->getById($eventId);
                        $event->setData(array_merge($event->getData(), $eventData));
                        $this->eventRepository->save($event);
                    } catch (LocalizedException $e) {
                        $messages[] = $this->getErrorWithEventId($event, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithEventId(
                            $event,
                            __('Something went wrong while saving the event.')
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
     * @param \Vishal\Events\Model\Event $event
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithEventId(\Vishal\Events\Model\Event $event, $errorText)
    {
        return '[Event ID: ' . $event->getId() . '] ' . $errorText;
    }
}