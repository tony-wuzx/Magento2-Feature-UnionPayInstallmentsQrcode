<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Block;

/**
 * Class Info
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Block
 */
class Info extends \Magento\Payment\Block\Info
{

    /**
     * Info template
     * @var string
     */
    protected $_template = 'Zhixing_UnionPayInstallmentsQrcode::info/default.phtml';
    
    /**
     * Instructions text
     * @var string
     */
    protected $_instructions;
    
    /**
     * Get instructions text from config
     * @return null|string
     */
    public function getInstructions(): ?string
    {
        if ($this->_instructions === null) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethod();
            $this->_instructions = $method->getConfigData('instructions');
        }
        return $this->_instructions;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'payment/uiq.png';
    }
}
