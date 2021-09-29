<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow;

/**
 * Class Paid
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
 */
class Paid extends \Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
{
    /**
     * If the order payment confirm, show ok.
     * ajax request by qrCode page to check the order's status.
     * if order payment, just need to show ok string for the ajax
     *
     * @return mixed
     */
    public function execute()
    {
        try {
            $order = $this->getOrder();
            if ($order) {
                $status = $order->getStatus();
                if ($status == \Magento\Sales\Model\Order::STATE_PROCESSING ||
                    $status == $this->_contextHelper->getOrderStatusPaymentAccepted()
                ) {
                    return $this->getResponse()->setBody('ok');
                } elseif ($status == \Magento\Sales\Model\Order::STATE_CANCELED) {
                    return $this->getResponse()->setBody('failed');
                } else {
                    return $this->getResponse()->setBody('waiting');
                }
            } else {
                return $this->getResponse()->setBody('need to login');
            }
        } catch (\Exception $e) {
            $this->_dataHelper->setReturnLog()->log('paid', $e->getMessage());
            return $this->getResponse()->setBody('error');
        }
    }
}
