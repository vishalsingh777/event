<?php
/**
 * INSEAD Events Banner Generic Button
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Block\Adminhtml\Banner\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Insead\Events\Model\BannerFactory;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @param Context $context
     * @param BannerFactory $bannerFactory
     */
    public function __construct(
        Context $context,
        BannerFactory $bannerFactory
    ) {
        $this->context = $context;
        $this->bannerFactory = $bannerFactory;
    }

    /**
     * Get banner ID
     *
     * @return int|null
     */
    public function getBannerId(): ?int
    {
        try {
            return (int)$this->context->getRequest()->getParam('banner_id');
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate URL by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}