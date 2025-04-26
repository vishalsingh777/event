<?php
/**
 * AddToCart.php
 */

declare(strict_types=1);

namespace Insead\Events\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;

class AddToCart implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var CustomerCart
     */
    protected $cart;

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
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @param Context $context
     * @param CustomerCart $cart
     * @param FormKeyValidator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        FormKeyValidator $formKeyValidator,
        ProductRepositoryInterface $productRepository,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request,
        EventFactory $eventFactory,
        EventResource $eventResource
    ) {
        $this->cart = $cart;
        $this->formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
    }

    /**
     * Clear the cart completely
     *
     * @return void
     */
    protected function clearCart()
    {
        try {
            $cartItems = $this->cart->getQuote()->getAllItems();
            foreach ($cartItems as $item) {
                $itemId = $item->getItemId();
                $this->cart->removeItem($itemId);
            }
            $this->cart->save();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred while clearing your cart.'));
        }
    }

    /**
     * Add event ticket product to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        // We'll support both GET and POST requests
        $productSku = $this->request->getParam('product_sku');
        $eventId = $this->request->getParam('event_id');
        $selectedTimeSlot = $this->request->getParam('selected_time_slot');
        $selectedTimeStart = $this->request->getParam('selected_time_start');
        $selectedTimeEnd = $this->request->getParam('selected_time_end');
        $selectedDate = $this->request->getParam('selected_date');
        
        if (!$productSku && !$eventId) {
            $this->messageManager->addErrorMessage(__('Unable to find event product to add to cart.'));
            return $resultRedirect->setRefererUrl();
        }

        // If we have event_id but not product_sku, load event first to get the SKU
        if ($eventId && !$productSku) {
            try {
                $event = $this->eventFactory->create();
                $this->eventResource->load($event, $eventId);
                
                if (!$event->getId()) {
                    $this->messageManager->addErrorMessage(__('Unable to find the specified event.'));
                    return $resultRedirect->setRefererUrl();
                }
                
                $productSku = $event->getProductSku();
                
                if (!$productSku) {
                    $this->messageManager->addErrorMessage(__('This event does not have an associated product.'));
                    return $resultRedirect->setRefererUrl();
                }
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('An error occurred while loading the event.'));
                return $resultRedirect->setRefererUrl();
            }
        }

        // Validate form key for POST requests only
        if ($this->request->isPost() && !$this->formKeyValidator->validate($this->request)) {
            return $resultRedirect->setRefererUrl();
        }

        try {
            // Load product by SKU
            $product = $this->productRepository->get($productSku);
            
            // If product exists, add to cart
            if ($product->getId()) {
                // Get event title for success message if event_id is provided
                $eventTitle = 'Event Registration';
                if ($eventId) {
                    try {
                        $event = $this->eventFactory->create();
                        $this->eventResource->load($event, $eventId);
                        if ($event->getId()) {
                            $eventTitle = $event->getEventTitle();
                        }
                    } catch (\Exception $e) {
                        // Silently ignore if event loading fails, we'll use the default title
                    }
                }
                
                // Clear the cart before adding the new product
                $this->clearCart();
                
                // Load any custom options or parameters for the product
                $params = [
                    'product' => $product->getId(),
                    'qty' => 1
                ];
                
                // Add event parameters
                if ($eventId) {
                    $params['event_id'] = $eventId;
                }

                // Add time slot if selected
                if ($selectedTimeSlot !== null && $selectedTimeSlot !== '') {
                    $params['selected_time_slot'] = $selectedTimeSlot;
                }
                
                // Add date if selected
                if ($selectedDate !== null && $selectedDate !== '') {
                    $params['selected_date'] = $selectedDate;
                }

                // Add selected start time if selected
                if ($selectedTimeStart !== null && $selectedTimeStart !== '') {
                    $params['selected_time_start'] = $selectedTimeStart;
                }
                
                // Add selected end time  if selected
                if ($selectedTimeEnd !== null && $selectedTimeEnd !== '') {
                    $params['selected_time_end'] = $selectedTimeEnd;
                }
                  
                $this->cart->addProduct($product, $params);
                $this->cart->save();
                
                $this->messageManager->addSuccessMessage(
                    __('You added %1 to your shopping cart.', $eventTitle)
                );
                
                return $resultRedirect->setPath('checkout/cart');
            } else {
                $this->messageManager->addErrorMessage(__('The product associated with this event could not be found.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The product "%1" could not be found.', $productSku));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We couldn\'t add this event to your cart right now.'));
        }
        
        return $resultRedirect->setRefererUrl();
    }
}