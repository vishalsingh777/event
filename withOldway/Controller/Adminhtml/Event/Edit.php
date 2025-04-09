<?php
namespace Vishal\Events\Controller\Adminhtml\Event;

use Vishal\Events\Controller\Adminhtml\Event;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

class Edit extends Event implements HttpGetActionInterface
{
    /**
     * Edit event action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        $model = $this->initEvent();
        
        if (!$model) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        
        $data = $this->dataPersistor->get('event_form_data');
        if (!empty($data)) {
            $model->setData($data);
            $this->dataPersistor->clear('event_form_data');
        }
        
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vishal_Events::manage_events');
        $resultPage->addBreadcrumb(__('Events'), __('Events'));
        $resultPage->addBreadcrumb(
            $eventId ? __('Edit Event') : __('New Event'),
            $eventId ? __('Edit Event') : __('New Event')
        );
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getEventTitle() : __('New Event'));
        
        return $resultPage;
    }
}