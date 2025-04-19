<?php
namespace Insead\Stripe\Plugin;

use Magento\Framework\App\RequestInterface;

class CsrfValidatorPlugin
{
    /**
     * Skip CSRF validation for specific URLs
     */
    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $subject,
        \Closure $proceed,
        RequestInterface $request,
        $action
    ) {
        $url = $request->getOriginalPathInfo();
        if (strpos($url, 'stripewebhook/webhook/index') !== false) {
            return; // Skip CSRF validation for this URL
        }

        $proceed($request, $action);
    }
}
