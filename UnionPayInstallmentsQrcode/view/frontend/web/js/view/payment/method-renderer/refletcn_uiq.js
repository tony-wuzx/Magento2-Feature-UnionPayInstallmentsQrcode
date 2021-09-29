/*browser:true*/
/*global define*/
define(
    [
    	'ko',
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (ko, Component, url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Zhixing_UnionPayInstallmentsQrcode/payment/form'
            },
            
            redirectAfterPlaceOrder: false,
            
            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                window.location.replace(url.build('zhixing_uiq/payflow/redirect/'));
            },
            
            /** Returns payment method instructions */
            getInstructions: function() {
            	//console.log(window.checkoutConfig.payment.instructions);
               return window.checkoutConfig.payment.instructions[this.item.method];
            }
        });
    }
);
