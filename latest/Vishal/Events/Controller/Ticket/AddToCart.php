<?php
/**
 * AddToCart.php
 */

declare(strict_types=1);

namespace Vishal\Events\Controller\Ticket;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Vishal\Events\Model\EventTicketRepository;

class AddToCart implements HttpPostActionInterface
{
    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param Context $context
     * @param CustomerCart $cart
     * @param EventTicketRepository $eventTicketRepository
     * @param FormKeyValidator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        EventTicketRepository $eventTicketRepository,
        FormKeyValidator $formKeyValidator,
        ProductRepositoryInterface $productRepository,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->cart = $cart;
        $this->eventTicketRepository = $eventTicketRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * Add ticket to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (!$this->formKeyValidator->validate($this->request)) {
            return $resultRedirect->setRefererUrl();
        }
        
        $ticketId = (int)$this->request->getParam('ticket_id');
        if (!$ticketId) {
            $this->messageManager->addErrorMessage(__('Unable to find ticket to add to cart.'));
            return $resultRedirect->setRefererUrl();
        }

        try {
            $ticket = $this->eventTicketRepository->getById($ticketId);
            
            // Check if this is a product-based ticket
            if ($ticket->getProductId()) {
                try {
                    $product = $this->productRepository->getById($ticket->getProductId());
                    $this->cart->addProduct($product, ['qty' => 1]);
                    $this->cart->save();
                    $this->messageManager->addSuccessMessage(__('You added %1 to your shopping cart.', $ticket->getName()));
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('We can\'t add this ticket to your shopping cart right now.'));
                }
            } else {
                // For manual tickets, create a custom option or something else to represent the ticket
                // Here we'll implement a simple redirect
                $this->messageManager->addErrorMessage(
                    __('Manual tickets are not currently supported. Please use product-based tickets.')
                );
            }
            
            return $resultRedirect->setPath('checkout/cart');
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The ticket no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add this ticket to your shopping cart right now.'));
        }
        
        return $resultRedirect->setRefererUrl();
    }
}