<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Block;

/**
 * Class Redirect
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Block
 */
class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Model\Payment
     */
    protected $_paymentMethod;
    
    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore
     */
    protected $_paymentCore;

    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var null
     */
    protected $_requestData = null;

    /**
     * @var null
     */
    protected $_params = null;

    /**
     * Redirect constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod,
        \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_paymentMethod = $paymentMethod;
        $this->_paymentCore = $paymentCore;
        $this->_contextHelper = $contextHelper;
    }

    /**
     * @return bool|null
     */
    public function canContinuePay(): bool
    {
        return $this->_paymentMethod->canContinuePay();
    }

    /**
     * @return bool|mixed|null
     */
    public function getQrcode()
    {
        return $this->_paymentCore->placeRequest();
    }
}
