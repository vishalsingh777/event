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

class AddToCart implements HttpPostActionInterface
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
     * @param Context $context
     * @param CustomerCart $cart
     * @param FormKeyValidator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        FormKeyValidator $formKeyValidator,
        ProductRepositoryInterface $productRepository,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->cart = $cart;
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
        
        $productSku = (int)$this->request->getParam('product_sku');
        if (!$productSku) {
            $this->messageManager->addErrorMessage(__('Unable to find ticket to add to cart.'));
            return $resultRedirect->setRefererUrl();
        }

        try {
            // Check if this is a product
            if ($productSku) {
                try {
                    $product = $this->productRepositoryInterface->get($sku);
                    $this->cart->addProduct($product, ['qty' => 1]);
                    $this->cart->save();
                    $this->messageManager->addSuccessMessage(__('You added %1 to your shopping cart.', 'load event and add event name'));//$ticket->getName()));
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('We can\'t add this ticket to your shopping cart right now.'));
                }
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