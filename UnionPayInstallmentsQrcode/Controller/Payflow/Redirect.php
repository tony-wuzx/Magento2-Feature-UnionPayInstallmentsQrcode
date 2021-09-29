<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow;

/**
 * Class Redirect
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
 */
class Redirect extends \Magento\Framework\App\Action\Action implements
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Model\Payment
     */
    protected $_paymentMethod;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore
     */
    protected $_paymentCore;
    
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    
    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Redirect constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod,
        \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_paymentMethod = $paymentMethod;
        $this->_paymentCore = $paymentCore;
        $this->_checkoutSession = $checkoutSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderFactory = $orderFactory;
        $this->_dataHelper = $dataHelper;
        $this->_contextHelper = $contextHelper;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Call ajax to ask uiq payment
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->_dataHelper->setRequestLog()->log('referer', $this->_redirect->getRefererUrl());
            $request = $this->getRequest()->getQuery();
            if (isset($request['orderId']) && $request['orderId']) {
                $this->_checkoutSession->setLastRealOrderId($request['orderId']);
                $order = $this->_orderFactory->create()->loadByIncrementId($request['orderId']);
                $isFromSession = false;
            } else {
                $order = $this->_checkoutSession->getLastRealOrder();
                $isFromSession = true;
            }
        
            if ($order->getId()) {
                $payWayLimit = $this->_contextHelper->getPayWayLimit();
                if ($payWayLimit && ($order->getPayment()->getMethod() !=
                        \Zhixing\UnionPayInstallmentsQrcode\Model\Payment::METHOD_CODE)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order can not use this payment way.')
                    );
                }

                if ($order->getStatus() == $this->_contextHelper->getOrderStatus()) {
                    if (!$isFromSession) {
                        //add order id to checkout session , and then let user can go to success page
                        $this->addOrderSuccessSessionData($order);
                    }

                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(__('UnionPay Installments Qrcode'));
                    return $resultPage;

                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
            }
        } catch (\Exception $e) {
            $this->_dataHelper->setRequestLog()->log('redirect', $e->getMessage());
            $this->getResponse()->setRedirect('checkout/cart');
        }
    }

    /**
     * for this project
     * the PRD needs the all paid order can go to success page
     *
     * @param $order
     */
    protected function addOrderSuccessSessionData($order)
    {
        $this->_checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getRealOrderId())
            ->setLastOrderStatus($order->getStatus())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastQuoteId($order->getQuoteId())
        ;
    }
}
