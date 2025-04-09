<?php
namespace Vishal\Events\Ui\DataProvider\EventTicket\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class EventTicketDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
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
        
        $items = $this->collection->getItems();
        
        foreach ($items as $ticket) {
            $this->loadedData[$ticket->getId()] = $ticket->getData();
        }

        $data = $this->dataPersistor->get('event_ticket_form_data');
        if (!empty($data)) {
            $ticket = $this->collection->getNewEmptyItem();
            $ticket->setData($data);
            $this->loadedData[$ticket->getId()] = $ticket->getData();
            $this->dataPersistor->clear('event_ticket_form_data');
        }

        return $this->loadedData;
    }
}