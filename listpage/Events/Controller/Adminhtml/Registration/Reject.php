<?php
namespace Insead\Events\Controller\Adminhtml\Registration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Insead\Events\Model\EventRegistrationRepository;
use Insead\Events\Model\EventRepository;
use Insead\Events\Model\EventRegistration;
use Insead\Events\Helper\Data as EmailHelper;
use Psr\Log\LoggerInterface;

class Reject extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::registrations';

    /**
     * @var EventRegistrationRepository
     */
    private $registrationRepository;
    
    /**
     * @var EventRepository
     */
    private $eventRepository;
    
    /**
     * @var EmailHelper
     */
    protected $emailHelper;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param EventRegistrationRepository $registrationRepository
     * @param EventRepository $eventRepository
     * @param EmailHelper $emailHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        EventRegistrationRepository $registrationRepository,
        EventRepository $eventRepository,
        EmailHelper $emailHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->registrationRepository = $registrationRepository;
        $this->eventRepository = $eventRepository;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * Reject registration action
     *
     * @return Redirect
     */
    public function execute()
    {
        // Check if module is enabled
        if (!$this->emailHelper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(__('The events module is currently disabled.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $registrationId = $this->getRequest()->getParam('id');
        
        if (!$registrationId) {
            $this->messageManager->addErrorMessage(__('Registration ID is required.'));
            return $resultRedirect->setPath('*/*/');
        }
        
        try {
            // Load registration
            $registration = $this->registrationRepository->getById($registrationId);
            
            // Check if registration is in pending status
            if ((int) $registration->getStatus() !== EventRegistration::STATUS_PENDING) {
                $this->messageManager->addErrorMessage(__('Only pending registrations can be rejected.'));
                return $resultRedirect->setPath('*/*/');
            }
            
            // Get rejection reason if provided
            $rejectionReason = $this->getRequest()->getParam('reason', '');
            
            // Update status to rejected
            $registration->setStatus(EventRegistration::STATUS_REJECTED);
            $this->registrationRepository->save($registration);
            
            // Send rejection email
            $this->emailHelper->sendRejectionEmail($registration, $rejectionReason);
            
            $this->messageManager->addSuccessMessage(__('Registration has been rejected.'));
            
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Error rejecting registration: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred while rejecting the registration.'));
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}