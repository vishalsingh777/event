<?php
/**
 * DataProvider.php
 * Path: app/code/Vishal/Events/Ui/Component/Form/EventTicket/DataProvider.php
 */

declare(strict_types=1);

namespace Vishal\Events\Ui\Component\Form\EventTicket;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\EventTicketRepository;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param EventTicketRepository $eventTicketRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        EventTicketRepository $eventTicketRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->eventTicketRepository = $eventTicketRepository;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $ticketId = $this->request->getParam($this->requestFieldName);
        if ($ticketId) {
            try {
                $ticket = $this->eventTicketRepository->getById($ticketId);
                $this->loadedData[$ticket->getId()] = $ticket->getData();
            } catch (NoSuchEntityException $e) {
                // Ticket not found
            }
        }

        return $this->loadedData;
    }
}