<?php
namespace Insead\Events\Controller\Subscription;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;

class Subscribe extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;
    
    /**
     * @var Escaper
     */
    protected $escaper;
    
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param SubscriberFactory $subscriberFactory
     * @param Escaper $escaper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        SubscriberFactory $subscriberFactory,
        Escaper $escaper,
        ManagerInterface $messageManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->escaper = $escaper;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Subscribe to newsletter (AJAX request)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        $email = $this->getRequest()->getParam('email');
        if (!$email) {
            return $result->setData([
                'success' => false,
                'message' => __('Please enter a valid email address.')
            ]);
        }
        
        try {
            $status = $this->subscriberFactory->create()->subscribe($email);
            $statusMessage = '';
            
            switch ($status) {
                case \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE:
                    $statusMessage = __('The confirmation request has been sent.');
                    break;
                case \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED:
                    $statusMessage = __('Thank you for your subscription.');
                    break;
                case \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED:
                    $statusMessage = __('You have been successfully unsubscribed.');
                    break;
                case \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED:
                    $statusMessage = __('You have been added to our newsletter subscription list, but you must confirm your subscription.');
                    break;
                default:
                    $statusMessage = __('Thank you for your subscription.');
            }
            
            return $result->setData([
                'success' => true,
                'message' => $statusMessage
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('There was a problem with the subscription: %1', $e->getMessage())
            ]);
        }
    }
}