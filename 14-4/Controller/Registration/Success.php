<?php
namespace Vishal\Events\Controller\Registration;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Vishal\Events\Model\EventRegistrationFactory;
use Vishal\Events\Model\ResourceModel\EventRegistration as EventRegistrationResource;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class Success extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var EventRegistrationFactory
     */
    protected $eventRegistrationFactory;
    
    /**
     * @var EventRegistrationResource
     */
    protected $eventRegistrationResource;
    
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var EventResource
     */
    protected $eventResource;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EventRegistrationFactory $eventRegistrationFactory
     * @param EventRegistrationResource $eventRegistrationResource
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventRegistrationFactory $eventRegistrationFactory,
        EventRegistrationResource $eventRegistrationResource,
        EventFactory $eventFactory,
        EventResource $eventResource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->eventRegistrationResource = $eventRegistrationResource;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        parent::__construct($context);
    }
    
    /**
     * Success action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Registration Complete'));
        
        $registrationId = $this->getRequest()->getParam('registration_id');
        if ($registrationId) {
            $registration = $this->eventRegistrationFactory->create();
            $this->eventRegistrationResource->load($registration, $registrationId);
            
            if ($registration->getId()) {
                // Load event to get more details
                $eventId = $registration->getEventId();
                $event = $this->eventFactory->create();
                $this->eventResource->load($event, $eventId);
                
                // Pass registration and event data to the block
                $resultPage->getLayout()->getBlock('event.registration.success')
                    ->setRegistration($registration)
                    ->setEvent($event);
            }
        }
        
        return $resultPage;
    }
}