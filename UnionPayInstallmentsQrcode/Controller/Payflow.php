<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller;

/**
 * Class Payflow
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Payflow extends \Magento\Framework\App\Action\Action implements
    \Magento\Framework\App\CsrfAwareActionInterface,
    \Magento\Framework\App\Action\HttpPostActionInterface,
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Model\Payment
     */
    protected $_paymentMethod;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Data
     */
    protected $_dataHelper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore
     */
    protected $_paymentCore;

    /**
     * var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagement;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory
     */
    protected $txnCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Builder
     */
    protected $txnBuilder;

    /**
     * Payflow constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod
     * @param \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $txnCollectionFactory
     * @param \Magento\Sales\Model\Order\Payment\Transaction\Builder $txnBuilder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod,
        \Zhixing\UnionPayInstallmentsQrcode\Model\PaymentCore $paymentCore,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $txnCollectionFactory,
        \Magento\Sales\Model\Order\Payment\Transaction\Builder $txnBuilder
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_paymentMethod = $paymentMethod;
        $this->_paymentCore = $paymentCore;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_dataHelper = $dataHelper;
        $this->_logger = $logger;
        $this->_contextHelper = $contextHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->txnCollectionFactory = $txnCollectionFactory;
        $this->txnBuilder = $txnBuilder;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @return array|\Magento\Framework\App\RequestInterface|mixed|null
     */
    public function getRequestArray()
    {
        $request = null;
        try {
            if ($this->getRequest()->isPost()) {
                $request = $this->getRequest()->getPost();
            } elseif ($this->getRequest()->isGet()) {
                $request = $this->getRequest()->getQuery();
            }

            $request = json_decode($request);
            if (is_array($request) && !empty($request)) {
                return $request;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Get order
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        $incrementId = $this->getRequest()->getParam('orderId');
        $sessionIncrementId = $this->_checkoutSession->getLastRealOrderId();

        if ($incrementId == $sessionIncrementId) {
            return $this->_checkoutSession->getLastRealOrder();
        } elseif ($this->_customerSession->isLoggedIn()) {
            $order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
            if ($order->getCustomerId() == $this->_customerSession->getCustomerId()) {
                return $order;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request)
    : ?\Magento\Framework\App\Request\InvalidRequestException
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('/');

        return new \Magento\Framework\App\Request\InvalidRequestException(
            $resultRedirect,
            [new \Magento\Framework\Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }
}
