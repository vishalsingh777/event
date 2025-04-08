<?php
/**
 * TicketsModifier.php
 * Path: app/code/Vishal/Events/Ui/Component/Form/Event/Modifier/TicketsModifier.php
 */

declare(strict_types=1);

namespace Vishal\Events\Ui\Component\Form\Event\Modifier;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Vishal\Events\Model\EventRepository;

class TicketsModifier implements ModifierInterface
{
    const DATA_SOURCE_DEFAULT = 'event';
    const DATA_SCOPE_TICKET = 'manual_tickets';
    const DATA_SCOPE_PRODUCT = 'product_tickets';
    const TICKET_LISTING_NAME = 'ticket_listing';
    const PRODUCT_LISTING_NAME = 'product_listing';
    const CONTAINER_TICKET_HEADER_NAME = 'ticket_header';
    const CONTAINER_PRODUCT_HEADER_NAME = 'product_header';
    const TICKET_GRID_NAME = 'manual_tickets';
    const PRODUCT_GRID_NAME = 'product_tickets';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param ArrayManager $arrayManager
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param EventRepository $eventRepository
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        ArrayManager $arrayManager,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EventRepository $eventRepository,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->eventRepository = $eventRepository;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'tickets' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Fieldset::NAME,
                                'label' => __('Tickets'),
                                'collapsible' => true,
                                'dataScope' => '',
                                'sortOrder' => 50
                            ],
                        ],
                    ],
                    'children' => [
                        self::CONTAINER_TICKET_HEADER_NAME => $this->getTicketHeaderContainer(),
                        self::TICKET_GRID_NAME => $this->getManualTicketsGrid(),
                        self::CONTAINER_PRODUCT_HEADER_NAME => $this->getProductHeaderContainer(),
                        self::PRODUCT_GRID_NAME => $this->getProductTicketsGrid()
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Get manual tickets grid
     *
     * @return array
     */
    protected function getManualTicketsGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Manual Ticket'),
                        'componentType' => 'dynamicRows',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => self::DATA_SCOPE_TICKET,
                        'dndConfig' => [
                            'enabled' => true,
                        ],
                        'disabled' => false,
                        'sortOrder' => 20,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'ticket_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'input',
                                        'dataType' => 'text',
                                        'visible' => false,
                                        'dataScope' => 'ticket_id',
                                    ],
                                ],
                            ],
                        ],
                        'name' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'input',
                                        'dataType' => 'text',
                                        'label' => __('Ticket Name'),
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                        'dataScope' => 'name',
                                    ],
                                ],
                            ],
                        ],
                        'sku' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'input',
                                        'dataType' => 'text',
                                        'label' => __('SKU'),
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                        'dataScope' => 'sku',
                                    ],
                                ],
                            ],
                        ],
                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'input',
                                        'dataType' => 'price',
                                        'label' => __('Price'),
                                        'validation' => [
                                            'validate-number' => true,
                                        ],
                                        'addbefore' => '$',
                                        'dataScope' => 'price',
                                    ],
                                ],
                            ],
                        ],
                        'position' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'input',
                                        'dataType' => 'number',
                                        'label' => __('Position'),
                                        'validation' => [
                                            'validate-number' => true,
                                        ],
                                        'dataScope' => 'position',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => 'text',
                                        'label' => '',
                                        'dataScope' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get ticket header container
     *
     * @return array
     */
    protected function getTicketHeaderContainer()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'sortOrder' => 10,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'header_title' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/form/element/static',
                                'elementTmpl' => 'ui/form/element/text',
                                'componentType' => Field::NAME,
                                'formElement' => Field::NAME,
                                'content' => __('Manual Tickets'),
                                'additionalClasses' => 'admin__fieldset-section-title'
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get product header container
     *
     * @return array
     */
    protected function getProductHeaderContainer()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'sortOrder' => 30,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'header_title' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/form/element/static',
                                'elementTmpl' => 'ui/form/element/text',
                                'componentType' => Field::NAME,
                                'formElement' => Field::NAME,
                                'content' => __('Product Tickets (Link Existing Products)'),
                                'additionalClasses' => 'admin__fieldset-section-title'
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get product tickets grid
     *
     * @return array
     */
    protected function getProductTicketsGrid()
    {
        $eventId = $this->request->getParam('event_id');
        $hasEvent = !empty($eventId);
        
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'insertListing',
                        'component' => 'Magento_Ui/js/form/components/insert-listing',
                        'dataScope' => self::DATA_SCOPE_PRODUCT,
                        'externalProvider' => 'product_listing.product_listing_data_source',
                        'selectionsProvider' => 'product_listing.product_listing.product_columns.ids',
                        'ns' => 'product_listing',
                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                        'realTimeLink' => true,
                        'loading' => $hasEvent,
                        'dataLinks' => [
                            'imports' => false,
                            'exports' => true
                        ],
                        'externalFilterMode' => true,
                        'imports' => [
                            'store_id' => '${ $.provider }:data.store_id',
                        ],
                        'exports' => [
                            'store_id' => '${ $.externalProvider }:params.store_id',
                        ],
                        'sortOrder' => 40
                    ],
                ],
            ],
        ];
    }
}