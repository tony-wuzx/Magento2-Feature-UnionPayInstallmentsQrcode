<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Helper;

/**
 * Class Context
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Helper
 */
class Context extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CERT_PATH = __DIR__ . '/../cert/';
    const SANDBOX_FILE_VERIFY_SIGN = self::CERT_PATH . 'open-test-public.cer';
    const SANDBOX_FILE_USER_RSA = self::CERT_PATH . 'user-rsa.pfx';
    const PRODUCTION_FILE_VERIFY_SIGN = self::CERT_PATH . '';
    const PRODUCTION_FILE_USER_RSA = self::CERT_PATH . '';

    const FRONTEND_ROUTE = 'zhixing_uiq/';
    const ACTION_PAID_URL = self::FRONTEND_ROUTE . 'payflow/paid';
    const ACTION_REDIRECT_URL = self::FRONTEND_ROUTE . 'payflow/redirect';
    const CALLBACK_RETURN_FALLBACK_URL = self::FRONTEND_ROUTE . 'payflow/back';
    const CALLBACK_NOTIFY_FALLBACK_URL = self::FRONTEND_ROUTE . 'ipn/callback';

    const CONFIG_GROUP = 'payment/zhixing_uiq/';
    const MODE_SANDBOX = 'sandbox';
    const TITLE = 'title';
    const IS_ACTIVE = 'is_active';
    const MODE = 'mode';
    const DEBUG = 'debug';
    const NOTIFY_URL = 'notify_url';
    const RETURN_URL = 'return_url';
    const API_QRCODE = 'api_qrcode';
    const API_QUERY = 'api_query';
    const API_REFUND = 'api_refund';
    const API_FILE_MGM = 'api_file_mgm';
    const PAY_WAY_LIMIT = 'pay_way_limit';
    const ORDER_STATUS = 'order_status';
    const ORDER_STATUS_PAYMENT_ACCEPTED = 'order_status_payment_accepted';
    const ENABLE_REPAY = 'enable_repay';
    const SHOW_LOGO = 'show_logo';
    const EXPIRE_TIME = 'expire_time';
    const MIN_ORDER_TOTAL = 'min_order_total';
    const MAX_ORDER_TOTAL = 'max_order_total';
    const UN_SUPPORT_NUM = 'un_support_num';
    const SUPPORT_BANK = 'support_bank';
    const INSTRUCTIONS = 'instructions';
    const SANDBOX = 'sandbox/';
    const PRODUCTION = 'production/';
    const SVC_ID = 'svc_id';
    const SER_ID = 'ser_id';
    const GATEWAY_QRCODE = 'gateway_qrcode';
    const GATEWAY_QUERY = 'gateway_query';
    const GATEWAY_REFUND = 'gateway_refund';
    const GATEWAY_FILE_MGM = 'gateway_file_mgm';
    const PRIVATE_KEY_PASS = 'private_key_pass';
    const PRIVATE_KEY = 'private_key';
    const PUBLIC_KEY = 'public_key';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Context constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * Get the config value from the config table
     *
     * @param $key
     * @return string
     */
    public function getConfigValue($key): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_GROUP . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is sandbox mode
     *
     * @return bool
     */
    public function isInSandboxMode(): bool
    {
        return $this->getConfigValue(self::MODE) == self::MODE_SANDBOX;
    }

    /**
     * Get field value
     *
     * @param string $field
     * @return string
     */
    public function getFieldValue($field): string
    {
        $path = $this->isInSandboxMode() ? self::SANDBOX . $field : self::PRODUCTION . $field;
        return $this->getConfigValue($path);
    }

    /**
     * @param string $path
     * @param null $storeId
     * @param null $secure
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl($path, $storeId = null, $secure = null): string
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }

    /**
     * @return string
     */
    public function getGatewayQrcode(): string
    {
        return $this->getFieldValue(self::GATEWAY_QRCODE);
    }

    /**
     * @return string
     */
    public function getGatewayQuery(): string
    {
        return $this->getFieldValue(self::GATEWAY_QUERY);
    }

    /**
     * @return string
     */
    public function getGatewayRefund(): string
    {
        return $this->getFieldValue(self::GATEWAY_REFUND);
    }

    /**
     * @return string
     */
    public function getGatewayFileMgm(): string
    {
        return $this->getFieldValue(self::GATEWAY_FILE_MGM);
    }

    /**
     * @return string
     */
    public function getApiQrcode(): string
    {
        return $this->getConfigValue(self::API_QRCODE);
    }

    /**
     * @return string
     */
    public function getApiQuery(): string
    {
        return $this->getConfigValue(self::API_QUERY);
    }

    /**
     * @return string
     */
    public function getApiRefund(): string
    {
        return $this->getConfigValue(self::API_REFUND);
    }

    /**
     * @return string
     */
    public function getApiFileMgm(): string
    {
        return $this->getConfigValue(self::API_FILE_MGM);
    }

    /**
     * @return string
     */
    public function getSvcId(): string
    {
        return $this->getFieldValue(self::SVC_ID);
    }

    /**
     * @return string
     */
    public function getSerId(): string
    {
        return $this->getFieldValue(self::SER_ID);
    }

    /**
     * @return string
     */
    public function getPrivateKeyPass(): string
    {
        return $this->getFieldValue(self::PRIVATE_KEY_PASS);
    }

    /**
     * @return mixed|string
     */
    public function getPrivateKey(): string
    {
        $privateKey = $this->getFieldValue(self::PRIVATE_KEY);
        $keyPath = $this->isInSandboxMode() ? self::SANDBOX_FILE_USER_RSA : self::PRODUCTION_FILE_USER_RSA;

        if (empty($privateKey) && file_exists($keyPath)) { // phpcs:ignore
            $cert = file_get_contents($keyPath); // phpcs:ignore
            $pass = $this->getPrivateKeyPass();

            openssl_pkcs12_read($cert, $data, $pass);
            $privateKey = $data['pkey'];

        } elseif (is_string($privateKey) && strpos($privateKey, '-----') === false) {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($privateKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }

        return (string) $privateKey;
    }

    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        $publicKey = $this->getFieldValue(self::PUBLIC_KEY);
        $keyPath = $this->isInSandboxMode() ? self::SANDBOX_FILE_VERIFY_SIGN : self::PRODUCTION_FILE_VERIFY_SIGN;

        if (empty($publicKey) && file_exists($keyPath)) { // phpcs:ignore
            $publicKey = file_get_contents($keyPath); // phpcs:ignore

        } elseif (is_string($publicKey) && strpos($publicKey, '-----') === false) {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($publicKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        }

        return (string) $publicKey;
    }

    /**
     * Get return url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReturnUrl(): string
    {
        $returnUrlConfig = $this->getConfigValue(self::RETURN_URL);
        return $returnUrlConfig ?: $this->getUrl(self::CALLBACK_RETURN_FALLBACK_URL);
    }

    /**
     * Get notification url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNotifyUrl(): string
    {
        $notifyUrlConfig = $this->getConfigValue(self::NOTIFY_URL);
        return $notifyUrlConfig ?: $this->getUrl(self::CALLBACK_NOTIFY_FALLBACK_URL);
    }

    /**
     * Get method title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->getConfigValue(self::TITLE);
    }

    /**
     * Get redirection url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedirectUrl(): string
    {
        return $this->getUrl(self::ACTION_REDIRECT_URL);
    }

    /**
     * Get pay url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPaidUrl(): string
    {
        return $this->getUrl(self::ACTION_PAID_URL);
    }

    /**
     * Is debug mode or not
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return (bool) $this->getConfigValue(self::DEBUG);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->getConfigValue(self::MODE);
    }

    /**
     * @return string
     */
    public function getIsAtive()
    {
        return $this->getConfigValue(self::IS_ACTIVE);
    }
    /**
     * @return string
     */
    public function getPayWayLimit()
    {
        return $this->getConfigValue(self::PAY_WAY_LIMIT);
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getConfigValue(self::ORDER_STATUS);
    }

    /**
     * @return string
     */
    public function getOrderStatusPaymentAccepted()
    {
        return $this->getConfigValue(self::ORDER_STATUS_PAYMENT_ACCEPTED);
    }

    /**
     * @return string
     */
    public function getShowLogo()
    {
        return $this->getConfigValue(self::SHOW_LOGO);
    }

    /**
     * @return string
     */
    public function getIsEnableRepay()
    {
        return $this->getConfigValue(self::ENABLE_REPAY);
    }

    /**
     * @return string
     */
    public function getExpireTime()
    {
        return $this->getConfigValue(self::EXPIRE_TIME);
    }

    /**
     * @return string
     */
    public function getMinOrderTotal()
    {
        return $this->getConfigValue(self::MIN_ORDER_TOTAL);
    }

    /**
     * @return string
     */
    public function getMaxOrderTotal()
    {
        return $this->getConfigValue(self::MAX_ORDER_TOTAL);
    }

    /**
     * Get unsupport number
     *
     * @return string
     */
    public function getUnSupportNum(): string
    {
        return (string) $this->getConfigValue(self::UN_SUPPORT_NUM);
    }

    /**
     * Get supported banks
     *
     * @return string
     */
    public function getSupportBank(): string
    {
        $banks =  $this->getConfigValue(self::SUPPORT_BANK);
        if (!empty($banks)) {
            $banksCode = [];
            $banks = json_decode($banks, true);
            foreach ($banks as $bank) {
                if (!empty($bank['code'])) {
                    $banksCode[] = $bank['code'];
                }
            }
            $banks = implode('&', $banksCode);
        }

        return (string) $banks;
    }

    /**
     * Get instruction
     *
     * @return string
     */
    public function getInstructions(): string
    {
        return (string) $this->getConfigValue(self::INSTRUCTIONS);
    }
}
