<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class BankRender
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Block\Adminhtml\System\Config\Form\Field
 */
class BankRender extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('code', ['label' => __('Code')]);
        $this->addColumn('comment', ['label' => __('Comment')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
