<?php
namespace Insead\Events\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Insead\Events\Model\EventRegistrationRepository;
use Insead\Events\Model\EventRegistration;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Cancel implements HttpGetActionInterface
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
     * @var ManagerInterface
     */
    private $messageManager;
    
    /**
     * @var EventRegistrationRepository
     */
    private $registrationRepository;
    
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param EventRegistrationRepository $registrationRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        EventRegistrationRepository $registrationRepository,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->registrationRepository = $registrationRepository;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $eventId = $this->request->getParam('event_id');
        $registrationId = $this->request->getParam('registration_id');
        $quoteId = $this->request->getParam('quote_id');
        
        try {
            if ($registrationId) {
                // Update registration status
                $registration = $this->registrationRepository->getById($registrationId);
                $registration->setPaymentStatus(EventRegistration::PAYMENT_STATUS_FAILED);
                $this->registrationRepository->save($registration);
                
                $this->logger->info('Updated registration payment status to failed', [
                    'registration_id' => $registrationId
                ]);
            }
            
            if ($quoteId) {
                // Mark quote as inactive/canceled
                try {
                    $quote = $this->quoteRepository->get($quoteId);
                    $quote->setIsActive(false);
                    $this->quoteRepository->save($quote);
                    
                    $this->logger->info('Marked quote as inactive', [
                        'quote_id' => $quoteId
                    ]);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to cancel quote: ' . $e->getMessage(), [
                        'quote_id' => $quoteId
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Cancel controller error: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'registration_id' => $registrationId,
                'quote_id' => $quoteId
            ]);
        }
        
        $this->messageManager->addNoticeMessage(__('Your payment was not completed. You can try again or choose another payment method.'));
        
        if ($eventId) {
            return $resultRedirect->setPath('events/index/view', ['id' => $eventId]);
        }
        
        return $resultRedirect->setPath('events/index/index');
    }
}