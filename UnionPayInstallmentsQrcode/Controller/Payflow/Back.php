<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow;

/**
 * Class Back
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
 */
class Back extends \Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $return = $this->getRequestArray();
        $this->_dataHelper->setReturnLog()->log('return', $return);
        try {
            if (!empty($return)) {
                //verify the request with payment server to check this is the safe request
                //if it is not safe or not from the uiq, show the error info
                if (!$this->_paymentCore->verifyResponse($return)) {
                    $this->_dataHelper->setReturnLog()->log('verifyResponse', 'verify notify data failed');
                    throw new \Magento\Framework\Exception\LocalizedException(__('Sorry, payment failed, Please contact us!'));
                }

                $resultContent = $this->_paymentCore->decodeResult($return);
                if ($resultContent) {
                    $errorMessage = __('No order for processing found');

                    $orderId = !empty($resultContent['mchntOrderId']) ? $resultContent['mchntOrderId'] : null;
                    if (!$orderId) {
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    }

                    $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
                    if (!$order || !$order->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    }

                    //if we get the payment notify to tell us the order is paid, direct to success page
                    //if not, direct to then waiting confirm page.
                    $state = !empty($order->getState()) ? $order->getState() : $order->getStatus();
                    if ($state == \Magento\Sales\Model\Order::STATE_PROCESSING) {
                        $resultRedirect->setPath('checkout/onepage/success');
                    } else {
                        $resultRedirect->setPath(
                            'zhixing_uiq/checkout/waitingconfirm',
                            ['_secure' => true, 'orderId' => $orderId]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_dataHelper->setReturnLog()->log('return', $e->getMessage());
            $resultRedirect->setPath('checkout/cart');
        }
        return $resultRedirect;
    }
}
