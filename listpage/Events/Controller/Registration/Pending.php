<?php
namespace Insead\Events\Controller\Registration;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Insead\Events\Model\EventRepository;
use Insead\Events\Model\EventRegistrationRepository;
use Psr\Log\LoggerInterface;

class Pending implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;
    
    /**
     * @var EventRepository
     */
    private $eventRepository;
    
    /**
     * @var EventRegistrationRepository
     */
    private $registrationRepository;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param EventRepository $eventRepository
     * @param EventRegistrationRepository $registrationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        EventRepository $eventRepository,
        EventRegistrationRepository $registrationRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $registrationId = $this->request->getParam('id');
            $eventId = $this->request->getParam('event_id');
            
            if (!$registrationId || !$eventId) {
                throw new LocalizedException(__('Missing registration information.'));
            }
            
            // Load registration
            $registration = $this->registrationRepository->getById($registrationId);
            
            // Verify this registration belongs to this event
            if ($registration->getEventId() != $eventId) {
                throw new LocalizedException(__('Invalid registration information.'));
            }
            
            // Load event
            $event = $this->eventRepository->getById($eventId);
            
            // Create page
            $resultPage = $this->resultPageFactory->create();
           // $resultPage->getConfig()->getTitle()->set(__('Registration Request Received'));
            
            // Set registration and event data for the block
            $block = $resultPage->getLayout()->getBlock('registration.pending');
            if ($block) {
                $block->setRegistration($registration);
                $block->setEvent($event);
            }
            
            return $resultPage;
            
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error('Registration pending error: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while loading the registration information.'));
            $this->logger->error('Registration pending exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        
        // Redirect to events listing if there's an error
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('events/index/index');
    }
}