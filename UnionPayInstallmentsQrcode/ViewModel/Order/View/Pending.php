<?php

declare(strict_types=1);

namespace Zhixing\UnionPayInstallmentsQrcode\ViewModel\Order\View;

/**
 * Class Pending
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\ViewModel\Order\View
 */
class Pending implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * Pending constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
    ) {
        $this->registry = $registry;
        $this->_contextHelper = $contextHelper;
    }

    /**
     * @return bool
     */
    public function isUiqOrder()
    {
        $method = $this->getOrder()->getPayment()->getMethod();
        return  $method == \Zhixing\UnionPayInstallmentsQrcode\Model\Payment::METHOD_CODE;
    }

    /**
     * @return bool
     */
    public function isPendingOrder()
    {
        return $this->getOrder()->getStatus() == $this->_contextHelper->getOrderStatus();
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_contextHelper->getRedirectUrl().'?orderId='.$this->getOrderIncrementId();
    }

    /**
     * @return bool
     */
    public function isEnableRepay()
    {
        return $this->_contextHelper->getIsEnableRepay();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    /**
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function getOrder()
    {
        return $this->registry->registry('current_order');
    }
}
