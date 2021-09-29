<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Block;

/**
 * Class Form
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Block
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;
    
    /**
     * whether show logo
     *
     * @var string
     */
    protected $_isShowLogo;
    
    /**
     * post form template
     *
     * @var string
     */
    protected $_template = 'Zhixing_UnionPayInstallmentsQrcode::form/default.phtml';

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethod();
            $this->_instructions = $method->getConfigData('instructions');
        }
        return $this->_instructions;
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIsShowLogo(): ?string
    {
        if ($this->_isShowLogo === null) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethod();
            $this->_isShowLogo = $method->getConfigData('show_logo');
        }
        return $this->_isShowLogo;
    }
}
