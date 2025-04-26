<?php
namespace Insead\Events\Controller\Registration;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Insead\Events\Model\EventRepository;
use Insead\Events\Model\EventRegistrationFactory;
use Insead\Events\Model\EventRegistrationRepository;
use Insead\Events\Model\EventRegistration;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Insead\Events\Helper\Data as EmailHelper;
use Psr\Log\LoggerInterface;

class Process implements HttpPostActionInterface
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
     * @var JsonFactory
     */
    private $resultJsonFactory;

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
     * @var EventRegistrationFactory
     */
    private $registrationFactory;
    
    /**
     * @var EventRegistrationRepository
     */
    private $registrationRepository;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var EmailHelper
     */
    private $emailHelper;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param EventRepository $eventRepository
     * @param EventRegistrationFactory $registrationFactory
     * @param EventRegistrationRepository $registrationRepository
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EmailHelper $emailHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        EventRepository $eventRepository,
        EventRegistrationFactory $registrationFactory,
        EventRegistrationRepository $registrationRepository,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EmailHelper $emailHelper,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->eventRepository = $eventRepository;
        $this->registrationFactory = $registrationFactory;
        $this->registrationRepository = $registrationRepository;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // Check if module is enabled
        if (!$this->emailHelper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(__('The events module is currently disabled.'));
            return $this->resultRedirectFactory->create()->setPath('');
        }
    
        $this->logger->info('Registration Process: Starting execution');
        
        // Check if this is an AJAX request
        $isAjax = $this->request->isXmlHttpRequest();
        $this->logger->info('Registration Process: Is AJAX request: ' . ($isAjax ? 'yes' : 'no'));
        
        if (!$this->request->isPost()) {
            $this->logger->warning('Registration Process: Invalid request method (not POST)');
            $this->messageManager->addErrorMessage(__('Invalid request method.'));
            
            if ($isAjax) {
                return $this->getJsonResponse(false, __('Invalid request method.'));
            }
            
            return $this->resultRedirectFactory->create()->setPath('events/index/index');
        }
        
        $eventId = (int)$this->request->getParam('event_id');
        $registrationType = $this->request->getParam('registration_type');
        
        $this->logger->info('Registration Process: Processing for event ID ' . $eventId . ', registration type ' . $registrationType);
        
        try {
            // Get event details
            $event = $this->eventRepository->getById($eventId);
            if (!$event->getId()) {
                $this->logger->warning('Registration Process: Event not found - ID: ' . $eventId);
                throw new LocalizedException(__('Event not found.'));
            }
            
            $this->logger->info('Registration Process: Event found - ' . $event->getTitle());
            
            // Create registration object
            $registration = $this->registrationFactory->create();
            $registration->setEventId($eventId);
            $registration->setFirstName($this->request->getParam('first_name'));
            $registration->setLastName($this->request->getParam('last_name'));
            $registration->setEmail($this->request->getParam('email'));
            $registration->setStreet($this->request->getParam('street'));
            $registration->setCity($this->request->getParam('city'));
            $registration->setCountry($this->request->getParam('country'));
            $registration->setZipcode($this->request->getParam('zipcode'));
            
            // Set time slot information if provided
            if ($this->request->getParam('selected_date')) {
                $registration->setSelectedDate($this->request->getParam('selected_date'));
            }
            
            if ($this->request->getParam('selected_time_start')) {
                $registration->setSelectedTimeStart($this->request->getParam('selected_time_start'));
            }
            
            if ($this->request->getParam('selected_time_end')) {
                $registration->setSelectedTimeEnd($this->request->getParam('selected_time_end'));
            }

            if ($this->request->getParam('selected_time_start') && $this->request->getParam('selected_time_end')) {
                $timeSlot = $this->request->getParam('selected_time_start'). '-'.$this->request->getParam('selected_time_end');
                $registration->setTimeSlot($timeSlot);
            }
            
            if ($this->request->getParam('selected_time_slot')) {
                $registration->setTimeSlot($this->request->getParam('selected_time_slot'));
            }
            
            $successMessage = '';
            $redirectPath = '';
            
            // Handle different registration types
            switch ($registrationType) {
                case '0': // Paid event
                    // This should not happen as paid events should go through cart
                    $registration->setStatus(EventRegistration::STATUS_PENDING);
                    $registration->setPaymentStatus(EventRegistration::PAYMENT_STATUS_PENDING);
                    $errorMessage = __('This event requires payment. Please use the payment option.');
                    
                    $this->logger->warning('Registration Process: Attempted direct registration for paid event');
                    $this->messageManager->addWarningMessage($errorMessage);
                    
                    if ($isAjax) {
                        return $this->getJsonResponse(false, $errorMessage);
                    }
                    
                    return $this->resultRedirectFactory->create()->setPath('events/index/view', ['id' => $eventId]);
                    
                case '1': // Register event (instant confirmation)
                    $registration->setStatus(EventRegistration::STATUS_REGISTERED);
                    $registration->setPaymentMethod('free');
                    $redirectPath = 'success';
                    $this->logger->info('Registration Process: Processing free registration (type 1)');
                    break;
                    
                case '2': // Register event with approval
                    $registration->setStatus(EventRegistration::STATUS_PENDING);
                    $registration->setPaymentMethod('free');
                    /*$successMessage = __('Thank you for your interest. Your registration request has been received and is pending approval. We will notify you once it has been reviewed.');*/
                    $redirectPath = 'pending';
                    $this->logger->info('Registration Process: Processing approval registration (type 2)');
                    break;
                    
                default:
                    $this->logger->warning('Registration Process: Invalid registration type: ' . $registrationType);
                    throw new LocalizedException(__('Invalid registration type.'));
            }
            
            // Save registration
            $this->logger->info('Registration Process: Saving registration');
            $this->registrationRepository->save($registration);
            $registrationId = $registration->getId();
            
            $this->logger->info('Registration Process: Registration saved successfully, ID: ' . $registrationId);
            
            // Send email notification
            if ($registrationType == '1') {
                // Free registration with instant confirmation
                $this->emailHelper->sendFreeRegistrationEmail($registration);
            } 
            
            // Show success message
            if($successMessage){
                $this->messageManager->addSuccessMessage($successMessage);
            }
            
            
            // Build redirect URL
            $redirectUrl = $this->storeManager->getStore()->getUrl('events/registration/' . $redirectPath, [
                'id' => $registrationId,
                'event_id' => $eventId
            ]);
            
            // Return appropriate response
            if ($isAjax) {
                $this->logger->info('Registration Process: Returning JSON success response');
                return $this->getJsonResponse(true, $successMessage, $registrationId, $redirectUrl);
            }
            
            // Redirect to success or pending page
            $this->logger->info('Registration Process: Redirecting to ' . $redirectPath . ' page');
            return $this->resultRedirectFactory->create()->setPath('events/registration/' . $redirectPath, [
                'id' => $registrationId,
                'event_id' => $eventId
            ]);
            
        } catch (LocalizedException $e) {
            $this->logger->error('Registration Process: LocalizedException - ' . $e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            
            if ($isAjax) {
                return $this->getJsonResponse(false, $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->error('Registration Process: Exception - ' . $e->getMessage());
            $this->logger->error('Registration Process: Stack trace - ' . $e->getTraceAsString());
            $this->messageManager->addErrorMessage(__('An error occurred while processing your registration.'));
            
            if ($isAjax) {
                return $this->getJsonResponse(false, __('An error occurred while processing your registration.'));
            }
        }
        
        // Default redirect in case of error
        if ($isAjax) {
            return $this->getJsonResponse(false, __('An error occurred while processing your registration.'));
        }
        
        return $this->resultRedirectFactory->create()->setPath('events/index/view', ['id' => $eventId]);
    }
    
    /**
     * Create JSON response
     *
     * @param bool $success
     * @param string $message
     * @param int|null $registrationId
     * @param string|null $redirectUrl
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function getJsonResponse($success, $message, $registrationId = null, $redirectUrl = null)
    {
        $result = $this->resultJsonFactory->create();
        
        $data = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($registrationId) {
            $data['registration_id'] = $registrationId;
        }
        
        if ($redirectUrl) {
            $data['redirect'] = $redirectUrl;
        }
        
        return $result->setData($data);
    }
}