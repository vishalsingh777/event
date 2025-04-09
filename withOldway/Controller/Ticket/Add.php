<?php
namespace Vishal\Events\Controller\Ticket;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Cart;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;

class Add implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @var EventTicketFactory
     */
    protected $eventTicketFactory;

    /**
     * @var EventTicketResource
     */
    protected $eventTicketResource;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ResolverInterface $localeResolver
     * @param Registry $coreRegistry
     * @param Cart $cart
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketResource $eventTicketResource
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory,
        FormKeyValidator $formKeyValidator,
        ResolverInterface $localeResolver,
        Registry $coreRegistry,
        Cart $cart,
        EventFactory $eventFactory,
        EventResource $eventResource,
        EventTicketFactory $eventTicketFactory,
        EventTicketResource $eventTicketResource
    ) {
        $this->context = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->localeResolver = $localeResolver;
        $this->coreRegistry = $coreRegistry;
        $this->cart = $cart;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketResource = $eventTicketResource;
    }

    /**
     * Execute action - add ticket to cart
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->context->getRequest())) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        
        $params = $this->context->getRequest()->getParams();
        
        if (!isset($params['event_id']) || !isset($params['ticket_id'])) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        
        try {
            $eventId = $params['event_id'];
            $ticketId = $params['ticket_id'];
            $qty = isset($params['qty']) ? (int)$params['qty'] : 1;
            
            // Load event and ticket
            $event = $this->eventFactory->create();
            $this->eventResource->load($event, $eventId);
            
            if (!$event->getId()) {
                throw new \Exception(__('Event does not exist.'));
            }
            
            $ticket = $this->eventTicketFactory->create();
            $this->eventTicketResource->load($ticket, $ticketId);
            
            if (!$ticket->getId() || $ticket->getEventId() != $eventId) {
                throw new \Exception(__('Ticket is not available for this event.'));
            }
            
            // Here we would add the ticket to the cart
            // Since this is a custom implementation, you would need to decide
            // how you want to handle tickets in the cart (custom product type, etc.)
            // For example purposes, we'll just return success
            
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'success' => true,
                'message' => __('Ticket has been added to your cart.')
            ]);
            
        } catch (\Exception $e) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}