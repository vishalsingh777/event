<?php
/**
 * Tickets.php
 * Path: app/code/Vishal/Events/Block/Event/Tickets.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block\Event;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Vishal\Events\Model\EventTicketRepository;

class Tickets extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param EventTicketRepository $eventTicketRepository
     * @param PricingHelper $pricingHelper
     * @param ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        EventTicketRepository $eventTicketRepository,
        PricingHelper $pricingHelper,
        ProductRepository $productRepository,
        array $data = []
    ) {
        $this->coreRegistry = $context->getRegistry();
        $this->eventTicketRepository = $eventTicketRepository;
        $this->pricingHelper = $pricingHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get current event
     *
     * @return \Vishal\Events\Model\Event|null
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('current_event');
    }

    /**
     * Get tickets for current event
     *
     * @return \Vishal\Events\Model\ResourceModel\EventTicket\Collection
     */
    public function getTickets()
    {
        $event = $this->getEvent();
        if ($event && $event->getId()) {
            return $this->eventTicketRepository->getTicketsForEvent($event->getId());
        }
        
        return null;
    }

    /**
     * Check if event has tickets
     *
     * @return bool
     */
    public function hasTickets()
    {
        $tickets = $this->getTickets();
        return $tickets && $tickets->getSize() > 0;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Get add to cart URL for ticket
     *
     * @param \Vishal\Events\Model\EventTicket $ticket
     * @return string
     */
    public function getAddToCartUrl($ticket)
    {
        if ($ticket->getProductId()) {
            try {
                $product = $this->getProductById($ticket->getProductId());
                return $this->getAddToCartUrlForProduct($product);
            } catch (NoSuchEntityException $e) {
                // Product not found, continue with manual ticket
            }
        }
        
        // Manual ticket
        return $this->getUrl('events/ticket/addtocart', ['ticket_id' => $ticket->getId()]);
    }

    /**
     * Get product by ID
     *
     * @param int $productId
     * @return Product
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Get add to cart URL for product
     *
     * @param Product $product
     * @return string
     */
    public function getAddToCartUrlForProduct($product)
    {
        return $this->getUrl('checkout/cart/add', ['product' => $product->getId()]);
    }
}