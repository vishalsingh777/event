<?php
namespace Insead\Events\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Insead\Events\Model\EventRegistration;

class RegistrationActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['registration_id'])) {
                    $actions = [];                
                    
                    // Removed debug code: print_r($item);die; and echo $item['status'];die;
                    
                    // Conditional actions based on status
                    if ((int)$item['status'] === EventRegistration::STATUS_PENDING) {
                        // Approve action for pending registrations
                        $actions['approve'] = [
                            'href' => $this->urlBuilder->getUrl(
                                'events/registration/approve',
                                ['id' => $item['registration_id']]
                            ),
                            'label' => __('Approve'),
                            'hidden' => false,
                            'confirm' => [
                                'title' => __('Approve Registration'),
                                'message' => __('Are you sure you want to approve this registration?')
                            ]
                        ];
                        
                        // Reject action for pending registrations
                        $actions['reject'] = [
                            'href' => $this->urlBuilder->getUrl(
                                'events/registration/reject',
                                ['id' => $item['registration_id']]
                            ),
                            'label' => __('Reject'),
                            'hidden' => false,
                            'confirm' => [
                                'title' => __('Reject Registration'),
                                'message' => __('Are you sure you want to reject this registration?')
                            ]
                        ];
                    }
                    
                    $item[$this->getData('name')] = $actions;
                }
            }
        }
        
        return $dataSource;
    }
}