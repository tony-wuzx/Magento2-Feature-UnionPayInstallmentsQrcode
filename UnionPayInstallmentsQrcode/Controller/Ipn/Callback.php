<?php

namespace Zhixing\UnionPayInstallmentsQrcode\Controller\Ipn;

/**
 * Class Callback
 *
 * @package Zhixing\UnionPayInstallmentsQrcode\Controller\Ipn
 */
class Callback extends \Zhixing\UnionPayInstallmentsQrcode\Controller\Payflow
{
    /**
     * @return void
     */
    public function execute()
    {
        $notify = $this->getRequestArray();
        $this->_dataHelper->setNotifyLog()->log('notify', $notify);

        try {
            if (!empty($notify)) {
                //verify the request with payment server to check this is the safe request
                //if it is not safe or not from the uiq, show the error info
                if (!$this->_paymentCore->verifyResponse($notify)) {
                    $this->_dataHelper->setNotifyLog()->log('verifyResponse', 'verify notify data failed');
                    $this->_failure();
                }

                $resultContent = $this->_paymentCore->decodeResult($notify);
                if ($resultContent) {
                    $orderId = !empty($resultContent['mchntOrderId']) ? $resultContent['mchntOrderId'] : null;
                    $transId = !empty($resultContent['traceNo']) ? $resultContent['traceNo'] : null;
                    if (!$orderId || !$transId) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
                    }

                    $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

                    if (!$order || !$order->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
                    }

                    if (!$order->hasInvoices() && $order->getStatus() == $this->_contextHelper->getOrderStatus()) {
                        $amount  = $order->getGrandTotal();
                        $payment = $order->getPayment();

                        //txn auth
                        $payment->setIsTransactionClosed(false);
                        $payment->setTransactionId($transId);
                        $payment->setLastTransId($transId);

                        /**@var \Magento\Sales\Model\Order\Payment\Transaction $transaction*/
                        $this->txnBuilder->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($transId)
                            ->setFailSafe(true)
                            ->addAdditionalInformation('order_id', $order->getId());
                        $transaction = $this->txnBuilder->build(
                            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH
                        );
                        $this->transactionRepository->save($transaction);

                        $payment->addTransactionCommentsToOrder($transaction, __('Authorize amount: %1', $amount));
                        $payment->setParentTransactionId(null);
                        $payment->save();

                        // add invoice
                        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                        $invoice = $this->invoiceManagement->prepareInvoice($order);
                        $invoice->pay();
                        $invoice->register();
                        $invoice->setOrder($order);
                        $invoice->setCanVoidFlag(false);
                        $invoice->setTransactionId($transId);
                        $this->invoiceRepository->save($invoice);

                        //txn capture
                        $this->txnBuilder->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($transId)
                            ->setFailSafe(true)
                            ->addAdditionalInformation('order_id', $order->getId());
                        $transaction = $this->txnBuilder->build(
                            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE
                        );
                        $transaction->setIsClosed(true);
                        $this->transactionRepository->save($transaction);

                        $payment->addTransactionCommentsToOrder($transaction, __('Captured amount of %1.', $amount));
                        $payment->setIsTransactionClosed(true);
                        $payment->setLastTransId($transId);
                        $payment->setAdditionalInformation('unionpay_captured', true);
                        $payment->setAdditionalInformation('unionpay_transaction_id', $transId);
                        $payment->save();

                        //update order
                        $order->setTotalPaid($amount);
                        $order->addRelatedObject($invoice);
                        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $this->orderRepository->save($order);

                        //add event here
                        $this->_eventManager->dispatch(
                            'sales_order_invoice_pay_captured',
                            ['invoice' => $invoice, 'order' => $order]
                        );
                    }

                    $this->_success();
                }
            }
        } catch (\Exception $e) {
            $this->_dataHelper->setNotifyLog()->log('return', $e->getMessage());
        }

        $this->_failure();
    }

    /**
     * Failure output for the Payment service notify
     */
    private function _failure()
    {
        $this->getResponse()->setBody('fail');
    }

    /**
     * Success output for the payment service notify
     */
    private function _success()
    {
        $this->getResponse()->setBody('success');
    }
}
