<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller\Checkout;

/**
 * Class WaitingConfirm
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller\Checkout
 */
class WaitingConfirm extends \Magento\Framework\App\Action\Action implements
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * WaitingConfirm constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
    ) {
        parent::__construct($context);
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->_contextHelper = $contextHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam('orderId');
            if ($orderId) {
                $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
            } else {
                $order = $this->_checkoutSession->getLastRealOrder();
            }

            if ($order->getId() && $order->getStatus() == $this->_contextHelper->getOrderStatus()) {
                $resultPage = $this->_resultPageFactory->create();
                $this->registry->register('current_order', $order);
                $resultPage->getConfig()->getTitle()->set(__('Waiting Confirm'));
                return $resultPage;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
            }

        } catch (\Exception $e) {
            $this->getResponse()->setRedirect('checkout/cart');
        }
    }
}
