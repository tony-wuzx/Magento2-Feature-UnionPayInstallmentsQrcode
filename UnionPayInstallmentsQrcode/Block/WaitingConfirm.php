<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Block;

/**
 * Class WaitingConfirm
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Block
 */
class WaitingConfirm extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var null
     */
    private $_order = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * WaitingConfirm constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->_contextHelper = $contextHelper;
        $this->addData(['cache_lifetime' => false ]);
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return '';
    }

    /**
     * Is customer logged
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get cache key params
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCacheKeyInfo(): array
    {
        return [
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            $this->getTemplateFile(),
            'base_url' => $this->getBaseUrl(),
            'mchntOrderId' => $this->getRealOrderId(),
            'template' => $this->getTemplate()
        ];
    }

    /**
     * Get continue shopping Url
     *
     * @return string
     */
    public function getContinueShoppingUrl(): string
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->_checkoutSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl('checkout/cart');
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }

    /**
     * Get real order ID
     *
     * @return mixed
     */
    public function getRealOrderId(): ?string
    {
        if ($order = $this->getOrder()) {
            return (string) $order->getIncrementId();
        }
        return null;
    }

    /**
     * Get success url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSuccessUrl(): string
    {
        return $this->_contextHelper->getUrl('checkout/onepage/success');
    }

    /**
     * Get pay url
     *
     * @return string
     */
    public function getPaidUrl(): string
    {
        return $this->_contextHelper->getPaidUrl();
    }

    /**
     * Get current order
     *
     * @return mixed|null
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $this->_order = $this->registry->registry('current_order');
        }

        return $this->_order;
    }
}
