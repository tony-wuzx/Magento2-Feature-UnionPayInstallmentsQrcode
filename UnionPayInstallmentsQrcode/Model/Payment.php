<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Model;

/**
 * Class Payment
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'zhixing_uiq';

    /**
     * the mix order valid second if you want to pay by uiq
     */
    const CAN_PAY_MIX_PERIOD = 60;

    /**
     * @var string[]
     */
    private $allowCurrency = ['CNY'];

    /**
     * @var string
     */
    public $_code = self::METHOD_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Zhixing\UnionPayInstallmentsQrcode\Block\Form::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Zhixing\UnionPayInstallmentsQrcode\Block\Info::class;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var \Magento\Framework\Exception\LocalizedExceptionFactory
     */
    protected $_exception;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $_transactionRepository;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Payment constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory $exception
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_exception = $exception;
        $this->_transactionRepository = $transactionRepository;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_orderFactory = $orderFactory;
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->datetime = $datetime;
        $this->timezone = $timezone;
        $this->_contextHelper = $contextHelper;
        $this->request = $request;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        return $this->_contextHelper->getInstructions();
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl(): string
    {
        return $this->_contextHelper->getRedirectUrl();
    }

    /**
     * @return string
     */
    public function getSuccessUrl(): string
    {
        return $this->_contextHelper->getReturnUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReturnUrl(): string
    {
        return $this->_contextHelper->getReturnUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNotifyUrl()
    {
        return $this->_contextHelper->getNotifyUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCancelUrl()
    {
        return $this->_contextHelper->getReturnUrl();
    }

    /**
     * validate to action or not
     *
     * @return bool
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * @param string $field
     * @param null $storeId
     * @return mixed|string
     */
    public function getConfigData($field, $storeId = null)
    {
        try {
            if ('order_place_redirect_url' === $field) {
                return $this->getOrderPlaceRedirectUrl();
            }
            return parent::getConfigData($field, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //do nothing here, we just need to get the config data
            return null;
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $orderId = $this->request->getParam('orderId');
            if ($orderId) {
                $this->_order = $this->_orderFactory->create()->loadByIncrementId($orderId);
                return $this->_order;
            }
            try {
                $this->_checkoutSession->start();
                $this->_order = $this->_checkoutSession->getLastRealOrder();
            } catch (\Magento\Framework\Exception\SessionException $e) {
                //do nothing here, we just need to get order
                return null;
            }
        }
        return $this->_order;
    }

    /**
     * Set order
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareConfig()
    {
        $config = [
            'svcId' => $this->_contextHelper->getSvcId(),
            'svcApi' => $this->_contextHelper->getApiQrcode(),
            'serId' => $this->_contextHelper->getSerId(),
            'charset' => 'utf-8',
            'format' => 'json',
            'signType' => 'RSA2',
            'sign' => $this->_contextHelper->getPrivateKey(),
            'version' => '1.0.2',
            'timestamp' => $this->formatDatetime(),
            'notifyUrl' => $this->_contextHelper->getNotifyUrl(),
            'bizContent' => json_encode($this->prepareBizData()),
        ];

        return $config;
    }

    /**
     * @return float|mixed|null
     */
    public function getOrderQrcode()
    {
        $qrcode = null;
        $order = $this->getOrder();
        if ($order && !empty($order->getQrcode())) {
            $qrcode = $order->getQrcode();
        }

        return $qrcode;
    }

    /**
     * @param $qrcode
     * @throws \Exception
     */
    public function setOrderQrcode($qrcode)
    {
        $order = $this->getOrder();
        if ($order) {
            $order->setQrcode($qrcode);
            $order->save();
        }
    }

    /**
     * @return array
     */
    public function prepareBizData(): array
    {
        $orderCurrency = $this->_getCurrency();
        $orderTotal = $this->_getTotalFee();

        if (in_array($orderCurrency, $this->allowCurrency)) {
            $totalFee = $orderTotal;
        } else {
            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            echo __('Please install CNY currency');
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            die();
        }

        return [
            'mchntOrderId' => $this->getOrder()->getRealOrderId(),
            'currency' => '156',
            'transAt' => $totalFee,
            'timeStart' => $this->getOrderCreatedAt(),
            'timeExpired' => $this->getExpiredTime(),
            'limitCreditPay' => '0',
            'transTp' => '01',
            'subTransTp' => '0124',
            'suppBankName' => $this->_contextHelper->getSupportBank(),
            'unSuppNum' => $this->_contextHelper->getUnSupportNum(),
        ];
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getOrderCreatedAt($locale = null): string
    {
        if ($order = $this->getOrder()) {
            if ($locale && ($locale == 'UTC')) {
                return $order->getCreatedAt();
            }
            return $this->formatDatetime($order->getCreatedAt());
        }

        return '';
    }

    /**
     * @param null $timestamp
     * @param string $type
     * @param string $format
     * @return int|string
     */
    public function formatDatetime($timestamp = null, $type = 'datetime', $format = 'YmdHis')
    {
        if ($timestamp === null) {
            $timestamp = $this->datetime->gmtTimestamp();
        }

        switch ($type) {
            case 'timestamp':
                $datetime = $this->timezone->date($timestamp, null)->getTimestamp();
                break;
            default:
            case 'datetime':
                $datetime = $this->timezone->date($timestamp, null)->format($format);
        }
        return $datetime;
    }

    /**
     * @param string $type
     * @return int|string
     */
    public function getExpiredTime($type = 'datetime')
    {
        if ($this->getOrder() && $this->getExpiredTimeFromConfig()) {
            $timestamp = $this->datetime->gmtTimestamp($this->getOrder()->getCreatedAt())
                + $this->getExpiredTimeFromConfig();
            return $this->formatDatetime($timestamp, $type);
        }
        return 0;
    }

    /**
     * @return bool|float|int
     */
    public function getExpiredTimeFromConfig()
    {
        return (int) $this->_contextHelper->getExpireTime();
    }

    /**
     * @return int|string
     */
    public function orderValidSeconds()
    {
        $expiredTimestamp = $this->getExpiredTime('timestamp');
        $nowTimestamp = $this->formatDatetime(null, 'timestamp');
        $remainSecond = $expiredTimestamp - $nowTimestamp;
        $remainSecond = ($remainSecond > 0) ? $remainSecond : 0;
        return $remainSecond;
    }

    /**
     * whether can pay or not
     *
     * @return bool
     */
    public function canContinuePay(): bool
    {
        $expireTime = $this->getExpiredTimeFromConfig();
        if ($expireTime <= 0) {
            return true; //no limit for this
        }
        if ($this->orderValidSeconds() > self::CAN_PAY_MIX_PERIOD) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function _getTotalFee(): string
    {
        return sprintf("%.2f", $this->getOrder()->getGrandTotal());
    }

    /**
     * @return null|string
     */
    protected function _getCurrency(): ?string
    {
        return $this->getOrder()->getOrderCurrencyCode();
    }

    /**
     * @return string
     */
    protected function _getSubject(): string
    {
        return (string) __('Order No: #%1', $this->getOrder()->getRealOrderId());
    }

    /**
     * @return string
     */
    protected function _getBody(): string
    {
        return (string) __('Order No: #%1', $this->getOrder()->getRealOrderId());
    }

    /**
     * @param $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canRepay($order): bool
    {
        try {
            //check the payment method is active or not
            if ($this->getConfigData('active') <= 0) {
                return false;
            }
            //check payment method config settings whether open repay
            if (!$this->_contextHelper->getIsEnableRepay()) {
                return false;
            }
            //check order's status is right or not. only pending status's order can repay to avoid twice payment
            if ($order->getStatus() != $this->_contextHelper->getOrderStatus()) {
                return false;
            }
            //if the order is not use the payment, must return false
            if ($order->getPayment()->getMethodInstance()->getCode() != $this->getCode()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $order
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRepayUrl($order): string
    {
        $orderId = $order->getIncrementId();
        $url = $this->_contextHelper->getRedirectUrl();
        $url .= '?orderId=' . $orderId . '&repay=1';
        return $url;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return false
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function acceptPayment(\Magento\Payment\Model\InfoInterface $payment): bool
    {
        return true;
    }
}
