/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (https://magegiant.com/)
 * @license     https://magegiant.com/license-agreement/
 */

/* Enterprise Gift Wrap Class*/
MagegiantOneStepCheckoutEnterpriseGiftwrap = Class.create();
MagegiantOneStepCheckoutEnterpriseGiftwrap.prototype = {
    initialize: function (config) {
        this.addGiftOptionsContainer = $$(config.addGiftOptionsContainer).first();
        this.addGiftOptionsCheckbox = $$(config.addGiftOptionsCheckbox);
        this.addGiftOrderSelect = $$(config.addGiftOrderSelect);
        this.addGiftOrderItemSelect = $$(config.addGiftOrderItemSelect);
        this.actionPattern = config.actionPattern;
        this.addGiftOptionsUrl = config.addGiftOptionsUrl;
        this.initObserver();
    },
    initObserver: function () {
        this.addGiftOptionsCheckbox.each(function (el) {
            el.observe('click', function (evt) {
                this.addGiftOptions(evt);
            }.bind(this))
        }.bind(this));
        this.addGiftOrderSelect.each(function (el) {
            el.observe('change', function (evt) {
                this.addGiftOptions(evt);
            }.bind(this))
        }.bind(this));
        this.addGiftOrderItemSelect.each(function (el) {
            el.observe('change', function (evt) {
                this.addGiftOptions(evt);
            }.bind(this))
        }.bind(this));
    },
    addGiftOptions: function (evt) {
        var self = this;
        var params = Form.serialize(this.addGiftOptionsContainer, true);
        var requestOptions = {
            method: 'post',
            parameters: params,
            onComplete: function (transport) {
                self.onComplete(transport, evt.target);
            }
        };
        var originPattern = MagegiantOneStepCheckout.actionPattern;
        MagegiantOneStepCheckout.setActionPattern(this.actionPattern);
        MagegiantOneStepCheckout.Request(this.addGiftOptionsUrl, requestOptions);
        MagegiantOneStepCheckout.setActionPattern(originPattern);
    },
    onComplete: function (transport, target) {
        try {
            var response = transport.responseText.evalJSON();
        } catch (e) {
            MagegiantOneStepCheckout.showMessage(target, response.message);
            return false;
        }
    }
};

/*Enterprise Store Credit Class */
MagegiantOneStepCheckoutEnterpriseStoreCredit = Class.create();
MagegiantOneStepCheckoutEnterpriseStoreCredit.prototype = {
    initialize: function (config) {
        this.container = $$(config.containerSelector).first();
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.useStorecreditCheckbox = $(config.useStorecreditCheckbox);
        this.actionPattern = config.actionPattern;
        this.cancelCreditElSelector = config.cancelCreditElSelector;
        this.applyStorecreditUrl = config.applyStorecreditUrl;
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        this.ajaxRequestId = 0;
        this.initObserver();
    },
    initObserver: function () {
        if (this.useStorecreditCheckbox) {
            this.useStorecreditCheckbox.observe('change', function (evt) {
                this.applyStorecredit(evt)
            }.bind(this))
        }
        this.initRemoveHandle();
    },
    initRemoveHandle: function () {
        var me = this;
        $$(this.cancelCreditElSelector).each(function (el) {
            if (el.getAttribute('href') && el.getAttribute('href').indexOf('storecredit/cart/remove/') !== -1) {
                el.observe('click', function (evt) {
                    me.removeCredit(evt);
                });
            }
        });
    },
    removeCredit: function (evt) {
        evt.stop();
        this.useStorecreditCheckbox.checked = false;
        this.applyStorecredit(evt);

    },
    applyStorecredit: function (evt) {
        this.removeMsg();
        var self = this;
        this.ajaxRequestId++;
        var currentAjaxRequestId = this.ajaxRequestId;
        var requestOptions = {
            method: 'post',
            parameters: {
                use_customer_balance: this.useStorecreditCheckbox.getValue()
            },
            onComplete: function (transport) {
                if (currentAjaxRequestId !== self.ajaxRequestId) {
                    return;
                }
                self.onComplete(transport);
            }
        };
        var originPattern = MagegiantOneStepCheckout.actionPattern;
        MagegiantOneStepCheckout.setActionPattern(this.actionPattern);
        MagegiantOneStepCheckout.Request(this.applyStorecreditUrl, requestOptions);
        MagegiantOneStepCheckout.setActionPattern(originPattern);

    },
    onComplete: function (transport) {
        try {
            var response = transport.responseText.evalJSON();
        } catch (e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        this.isPointsApplied = response.points_applied;
        if (response.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                successMsg = response.messages;
            }
            this.showSuccess(successMsg);
            this.initRemoveHandle();
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                errorMsg = response.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function (msg) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.errorMessageBoxCssClass);
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer.down());
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3);
    },
    showSuccess: function (msg) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.successMessageBoxCssClass);
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer.down());
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3);
    },
    removeMsg: function () {
        if (this.msgContainer.down()) {
            var self = this;
            MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, 0, 0.5, function () {
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.errorMessageBoxCssClass);
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.successMessageBoxCssClass);
            })
        }
    }
};
/*Enterprise Reward Points Class*/
MagegiantOneStepCheckoutEnterpriseReward = Class.create();
MagegiantOneStepCheckoutEnterpriseReward.prototype = {
    initialize: function (config) {
        this.container = $$(config.containerSelector).first();
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.usePointsCheckbox = $(config.usePointsCheckbox);
        this.applyPointsUrl = config.applyPointsUrl;
        this.actionPattern = config.actionPattern;
        this.cancelRewardElSelector = config.cancelRewardElSelector;
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        this.ajaxRequestId = 0;
        this.initObserver();
    },
    initObserver: function () {
        if (this.usePointsCheckbox) {
            this.usePointsCheckbox.observe('change', function (evt) {
                this.applyPoints(evt);
            }.bind(this))
        }
        this.initRemoveHandle();
    },
    initRemoveHandle: function () {
        var me = this;
        $$(this.cancelRewardElSelector).each(function (el) {
            if (el.getAttribute('href') && el.getAttribute('href').indexOf('reward/cart/remove/') !== -1) {
                el.observe('click', function (evt) {
                    me.removeReward(evt);
                });
            }
        });
    },
    removeReward: function (evt) {
        evt.stop();
        this.usePointsCheckbox.checked = false;
        this.applyPoints(evt);
    },
    applyPoints: function (evt) {
        this.removeMsg();
        var self = this;
        this.ajaxRequestId++;
        var currentAjaxRequestId = this.ajaxRequestId;
        var requestOptions = {
            method: 'post',
            parameters: {
                use_reward_points: this.usePointsCheckbox.getValue()
            },
            onComplete: function (transport) {
                if (currentAjaxRequestId !== self.ajaxRequestId) {
                    return;
                }
                self.onComplete(transport);
            }
        };
        var originPattern = MagegiantOneStepCheckout.actionPattern;
        MagegiantOneStepCheckout.setActionPattern(this.actionPattern);
        MagegiantOneStepCheckout.Request(this.applyPointsUrl, requestOptions);
        MagegiantOneStepCheckout.setActionPattern(originPattern);
    },
    onComplete: function (transport) {
        try {
            var response = transport.responseText.evalJSON();
        } catch (e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        this.isPointsApplied = response.points_applied;
        if (response.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                successMsg = response.messages;
            }
            this.showSuccess(successMsg);
            this.initRemoveHandle();
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                errorMsg = response.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function (msg, callback) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.errorMessageBoxCssClass);
        var callback = callback || new Function();
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer.down());
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3, callback);
    },
    showSuccess: function (msg, callback) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.successMessageBoxCssClass);
        var callback = callback || new Function();
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer.down());
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3, callback);
    },
    removeMsg: function () {
        if (this.msgContainer.down()) {
            var self = this;
            MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, 0, 0.5, function () {
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.errorMessageBoxCssClass);
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.successMessageBoxCssClass);
            });
        }
    }
};

/*Enterprise GiftCart Class*/
MagegiantOneStepCheckoutEnterpriseGifCard = Class.create();
MagegiantOneStepCheckoutEnterpriseGifCard.prototype = {
    initialize: function (config) {
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.giftcardCodeInput = $(config.giftcardCodeInput);
        this.actionPattern = config.actionPattern;
        this.applyGiftcardUrl = config.applyGiftcardUrl;
        this.removeGiftcardUrl = config.removeGiftcardUrl;
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        this.applyGiftcardButton = $$(config.applyGiftcardButtonSelector).first();
        this.cancelGiftcardElSelector = config.cancelGiftcardElSelector;
        this.ajaxRequestId = 0;
        this.initObserver();
    },
    initObserver: function () {
        if (this.applyGiftcardButton) {
            this.applyGiftcardButton.observe('click', function (evt) {
                this.applyGiftcard(evt);
            }.bind(this));
        }
        this.initRemoveHandle();
    },

    initRemoveHandle: function () {
        var me = this;
        $$(this.cancelGiftcardElSelector).each(function (el) {
            if (el.getAttribute('href') && el.getAttribute('href').indexOf('giftcard/cart/remove/code/') !== -1) {
                el.observe('click', function (e) {
                    me.removeGiftcard(e, el);
                });
            }
        });
    },

    applyGiftcard: function (evt) {
        this.removeMsg();
        this.giftcardCodeInput.addClassName('required-entry');
        var validationResult = Validation.validate(this.giftcardCodeInput);
        this.giftcardCodeInput.removeClassName('required-entry');
        if (!validationResult) {
            return;
        }
        var self = this;
        this.ajaxRequestId++;
        var currentAjaxRequestId = this.ajaxRequestId;
        var requestOptions = {
            method: 'post',
            parameters: {
                enterprise_giftcard_code: this.giftcardCodeInput.getValue()
            },
            onComplete: function (transport) {
                if (currentAjaxRequestId !== self.ajaxRequestId) {
                    return;
                }
                self.onComplete(transport);
            }
        };
        var originPattern = MagegiantOneStepCheckout.actionPattern;
        MagegiantOneStepCheckout.setActionPattern(this.actionPattern);
        MagegiantOneStepCheckout.Request(this.applyGiftcardUrl, requestOptions);
        MagegiantOneStepCheckout.setActionPattern(originPattern);
    },

    removeGiftcard: function (e, el) {
        e.stop();
        this.removeMsg();
        var code = el.getAttribute('href').match(/giftcard\/cart\/remove\/code\/([^\/]+)\//)[1];
        var requestOptions = {
            method: 'post',
            parameters: {
                enterprise_giftcard_code: code
            }
        };
        var originPattern = MagegiantOneStepCheckout.actionPattern;
        MagegiantOneStepCheckout.setActionPattern(this.actionPattern);
        MagegiantOneStepCheckout.Request(this.removeGiftcardUrl, requestOptions);
        MagegiantOneStepCheckout.setActionPattern(originPattern);
    },

    onComplete: function (transport) {
        try {
            var response = transport.responseText.evalJSON();
        } catch (e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        if (response.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                successMsg = response.messages;
            }
            this.showSuccess(successMsg);
            this.giftcardCodeInput.setValue('');
            this.initRemoveHandle();
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in response) && ("length" in response.messages) && response.messages.length > 0) {
                errorMsg = response.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function (msg, callback) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.errorMessageBoxCssClass);
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer);
        callback = callback || new Function();
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3, callback);
    },
    showSuccess: function (msg, callback) {
        MagegiantOneStepCheckout.showMessage(this.msgContainer, msg, this.successMessageBoxCssClass);
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.msgContainer);
        callback = callback || new Function();
        MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, newHeight, 0.3, callback);
    },
    removeMsg: function () {
        if (this.msgContainer.down()) {
            var self = this;
            MagegiantOneStepCheckout.applyMorphEffect(this.msgContainer, 0, 0.5, function () {
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.errorMessageBoxCssClass);
                MagegiantOneStepCheckout.removeMessage(self.msgContainer, self.successMessageBoxCssClass);
            });
        }
    }
};