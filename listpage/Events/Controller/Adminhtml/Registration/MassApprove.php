<?php
namespace Insead\Events\Controller\Adminhtml\Registration;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Insead\Events\Model\ResourceModel\EventRegistration\CollectionFactory;
use Insead\Events\Model\EventRegistration;
use Insead\Events\Model\EventRegistrationRepository;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class MassApprove extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Insead_Events::registrations';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var EventRegistrationRepository
     */
    protected $registrationRepository;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param EventRegistrationRepository $registrationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        EventRegistrationRepository $registrationRepository,
        LoggerInterface $logger
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->registrationRepository = $registrationRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $approvedCount = 0;
            $errorCount = 0;
            
            foreach ($collection as $registration) {
                try {
                    if ($registration->getStatus() === EventRegistration::STATUS_PENDING) {
                        $registration->setStatus(EventRegistration::STATUS_APPROVED);
                        $this->registrationRepository->save($registration);
                        $approvedCount++;
                        
                        // Here we would normally call a service to send approval email
                        // $this->emailService->sendApprovalEmail($registration);
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error approving registration #' . $registration->getId() . ': ' . $e->getMessage());
                    $errorCount++;
                }
            }
            
            if ($approvedCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 registration(s) have been approved.', $approvedCount)
                );
            }
            
            if ($errorCount) {
                $this->messageManager->addErrorMessage(
                    __('A total of %1 registration(s) could not be approved.', $errorCount)
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Mass approve error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred while approving registrations.'));
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}