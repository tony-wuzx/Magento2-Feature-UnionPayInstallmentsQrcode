<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Model;

/**
 * Class PaymentCore
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Model
 */
class PaymentCore
{
    /**
     * success return code
     */
    const SUCCESS_CODE = '0000';

    /**
     * @var array
     */
    private $_config = [];

    /**
     * ignore sign fields
     * @var array
     */
    private $ignores = ['sign'];

    /**
     * @var Payment
     */
    protected $_paymentMethod;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Data
     */
    private $_dataHelper;

    /**
     * @var \Zhixing\UnionPayInstallmentsQrcode\Helper\Context
     */
    protected $_contextHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $curlFactory;

    /**
     * PaymentCore constructor.
     *
     * @param Payment $paymentMethod
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper
     * @param \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     */
    public function __construct(
        \Zhixing\UnionPayInstallmentsQrcode\Model\Payment $paymentMethod,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Data $dataHelper,
        \Zhixing\UnionPayInstallmentsQrcode\Helper\Context $contextHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
    ) {
        $this->_paymentMethod = $paymentMethod;
        $this->_dataHelper = $dataHelper;
        $this->_contextHelper = $contextHelper;
        $this->httpClient = \Http\Discovery\HttpClientDiscovery::find();
        $this->requestFactory = \Http\Discovery\MessageFactoryDiscovery::find();
        $this->request = $request;
        $this->curlFactory = $curlFactory;
    }

    /**
     * Set default config data for this payment method
     */
    public function setConfig(): self
    {
        $config = $this->_paymentMethod->prepareConfig();
        $this->_config = $config;

        return $this;
    }

    /**
     * Get the default config data for this payment method
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->_config;
    }

    /**
     * @return bool|mixed|null
     */
    public function placeRequest()
    {
        try {
            $qrcode = $this->_paymentMethod->getOrderQrcode();
            if (!empty($qrcode)) {
                return $qrcode;
            }

            $this->setConfig();
            $this->generateRequestData();
            $result = $this->curl();
            $result = $this->decodeResult($result);

            $qrcode = $result['qrImage'] ?? null;

            if (!empty($qrcode)) {
                $this->_paymentMethod->setOrderQrcode($qrcode);
            }

            return $qrcode;

        } catch (\Exception $e) {
            $this->_dataHelper->setRequestLog()->log(__FUNCTION__, $e->getMessage());
        }
        return false;
    }

    /**
     * @param $result
     * @return bool|mixed|null
     */
    public function decodeResult($result)
    {
        if (isset($result['respCd']) && $result['respCd'] == self::SUCCESS_CODE) {
            return $result['respContent'] ? json_decode($result['respContent'], true) : true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function curl()
    {
        $url = $this->_contextHelper->getGatewayQrcode();
        $jsonData = json_encode($this->getConfig(), true);

        /** @var \Magento\Framework\HTTP\Adapter\Curl $curlRequest */
        $curlRequest = $this->curlFactory->create();

        $curlRequest->addOption(CURLOPT_FOLLOWLOCATION, true);
        $curlRequest->addOption(CURLOPT_MAXREDIRS, 10);
        $curlRequest->addOption(CURLOPT_TIMEOUT, 0);
        $curlRequest->addOption(CURLOPT_RETURNTRANSFER, true);
        $curlRequest->addOption(CURLOPT_FOLLOWLOCATION, true);

        $curlRequest->write(
            'POST',
            $url,
            '1.1',
            [
                'Content-Type' => 'Content-Type: application/json'
            ],
            $jsonData
        );

        $response = $curlRequest->read();
        if (false === $response) {
            $this->_dataHelper->setRequestLog()->log('errorMsg', $curlRequest->getError());
        }

        $response = \Zend_Http_Response::fromString($response);
        $curlRequest->close();

        $result = json_decode($response->getBody(), true);
        $this->_dataHelper->setRequestLog()->log('result', $result);

        return $result;
    }

    /**
     * Generate post data with fields
     *
     * @return array
     */
    private function generateRequestData(): array
    {
        $this->_config['sign'] = $this->sign();
        $this->_dataHelper->setRequestLog()->log(
            'generateRequestData',
            json_encode(
                $this->_config,
                true
            )
        );
        return $this->_config;
    }

    /**
     * @return bool|string
     */
    protected function sign()
    {
        return $this->signWithRSA2($this->_config['sign']);
    }

    /**
     * @param $privateKey
     * @return bool|string
     */
    public function signWithRSA2($privateKey)
    {
        $content = $this->getContentToSign();
        return $this->signContentWithRSA2($content, $privateKey);
    }

    /**
     * @param int $alg
     * @return string
     */
    public function getContentToSign()
    {
        return hash('sha256', $this->getPayload());
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        $params = $this->getParamsToSign();
        return urldecode(http_build_query($params));
    }

    /**
     * @return array
     */
    public function getParamsToSign()
    {
        $params = $this->_config;
        $this->unsetKeys($params);
        $params = $this->filter($params);
        $this->sort($params);

        return $params;
    }

    /**
     * @param $content
     * @param $privateKey
     * @param $alg
     * @return bool|string
     */
    protected function signContentWithRSA2($content, $privateKey)
    {
        $res = openssl_pkey_get_private($privateKey);
        $sign = null;

        try {
            openssl_sign($content, $sign, $res, OPENSSL_ALGO_SHA256);
        } catch (\Exception $e) {
            $this->_dataHelper->setRequestLog()->log('sign', $e->getMessage());
            return false;
        }

        $sign = base64_encode($sign);

        openssl_free_key($res);

        return $sign;
    }

    /**
     * @param $content
     * @param $sign
     * @param $publicKey
     * @return bool
     */
    public function verifyWithRSA2($content, $sign, $publicKey)
    {
        $res = openssl_pkey_get_public($publicKey);

        if (!$res) {
            $this->_dataHelper->setNotifyLog()->log('sign', 'The public key is invalid');
            return false;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $result = (bool) openssl_verify($content, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);

        openssl_free_key($res);

        return $result;
    }

    /**
     * @param $params
     */
    protected function unsetKeys(&$params)
    {
        foreach ($this->ignores as $key) {
            unset($params[$key]);
        }
    }

    private function filter($params)
    {
        return array_filter($params, 'strlen');
    }

    /**
     * @param $params
     */
    protected function sort(&$params)
    {
        ksort($params);
    }

    /**
     * Verify with payment service
     * Verify the sign key is matched or not
     *
     * @param $data
     * @return bool
     */
    public function verifyResponse($data)
    {
        $this->setConfig();
        if (!empty($data['sign'])) {
            $content = $this->getContentToSign();
            $publicKey = $this->_contextHelper->getPublicKey();

            return $this->verifyWithRSA2($content, $data['sign'], $publicKey);
        }

        return false;
    }

    /**
     * Get the remote address
     *
     * @return string
     */
    public function getIp(): string
    {
        // For Ali CDN
        $ip = $this->request->getServer('HTTP_ALI_CDN_REAL_IP');
        if ($ip) {
            return $ip;
        }

        // HTTP_X_FORWARDED_FOR
        $ip = $this->request->getServer('HTTP_X_FORWARDED_FOR');
        if ($ip) {
            return $ip;
        }
        return $this->request->getServer('REMOTE_ADDR', '');
    }
}
