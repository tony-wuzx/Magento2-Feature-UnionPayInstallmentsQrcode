<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Model;

/**
 * Class InstructionsConfigProvider
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Model
 */
class InstructionsConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
            Payment::METHOD_CODE,
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * InstructionsConfigProvider constructor.
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Escaper   $escaper
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->escaper = $escaper;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * Get config data
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     * @param string $code
     * @return string
     */
    protected function getInstructions($code): string
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
}
