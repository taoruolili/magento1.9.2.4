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

var MagegiantOneStepCheckout = Class.create();
MagegiantOneStepCheckout.prototype = {
    initialize: function () {
        this.blocks = {};
        this.queueBlocks = {};
        this.currentRequest = '';
        this.queueRequest = [];
        this.initAjaxResponse();

    },
    setConfig: function (config) {
        this.blockMapping = config.blockMapping;
        this.loadingClass = config.loadingClass;
        this.showNumber = config.showNumber;
        this.actionPattern = config.actionPattern;
        this.isUsedMorphEffect = config.isUsedMorphEffect;
        this.saveFormUrl = config.saveFormUrl;

    },
    setActionPattern: function (pattern) {
        this.actionPattern = pattern;
    },
    initAjaxResponse: function () {
        Ajax.Responders.register({
            onComplete: function (response) {
                if (response.transport.status === 403) {
                    document.location.reload();
                }
            }
        });
    },
    setBlocks: function (config) {
        $H(config.blocks).each(function (el) {
            this._addBlock(el[0], el[1]);
        }.bind(this));
    },
    _addBlock: function (name, selector) {
        if (typeof(this.blocks[name]) != 'undefined') {
            return;
        }
        $$(selector).each(function (el) {
            this.blocks[name] = el;

        }.bind(this));
    },
    saveFormData: function (formContainer) {
        var params = Form.serialize(formContainer, true);
        var requestOptions = {
            method: 'post',
            parameters: params
        }
        new Ajax.Request(this.saveFormUrl, requestOptions);
    },
    applyMorphEffect: function (selector, newHeight, duration, callBack) {

        if (this.isUsedMorphEffect) {
            if (selector.effect) {
                selector.effect.cancel();
            }
            var callBack = callBack || Prototype.emptyFunction;
            selector.effect = new Effect.Morph(selector, {
                style: {
                    'height': newHeight + 'px'
                },
                duration: duration,
                afterFinish: function () {
                    delete selector.effect;
                    callBack();
                }
            });
        }
        else {
            if (newHeight === 0) {
                selector.setStyle({
                    display: 'none',
                    height: 0
                })
            }
            else {
                selector.setStyle({
                    height: ''
                })
            }

        }

    },
    getHeightFromElement: function (element) {
        var currentHeight = element.style.height;
        var currentDisplay = element.style.display;
        var currentVisibility = element.style.visibility;
        element.setStyle({
            'height': '',
            'display': '',
            'visibility': 'hidden'
        });
        var height = element.getHeight();
        element.setStyle({
            'height': currentHeight,
            'display': currentDisplay,
            'visibility': currentVisibility
        });
        return height;

    },
    showMessage: function (block, message, messageClass) {
        if (typeof(message) === 'object' && message.length > 0) {
            message.each(function (msg) {
                this._appendMessage(block, msg, messageClass);
            }.bind(this));
        }
        else if (typeof(message) === 'string') {
            this._appendMessage(block, message, messageClass);
        }

    },
    removeMessage: function (block, messageClass) {
        block.select('.' + messageClass).each(function (el) {
            el.remove();
        })
    },
    _appendMessage: function (block, message, messageClass) {
        var currenMessage = null;
        var messageSection = block.select("." + messageClass + " ol");
        if (messageSection.length === 0) {
            var messageElement = new Element('div');
            messageElement.addClassName(messageClass);
            messageElement.appendChild(new Element('ol'));
            block.insertBefore(messageElement, block.down());
            currenMessage = messageElement.down();
        } else {
            currenMessage = messageSection.first();
        }
        var newMessage = new Element('li');
        newMessage.update(message);
        currenMessage.appendChild(newMessage);
    },
    addOverlay: function (block, className) {
        var overlay = new Element('div');
        overlay.addClassName(className);
        block.appendChild(overlay);
    },
    removeOverlay: function (block, className) {
        block.select('.' + className).each(function (el) {
            el.remove();
        })
    },
    Request: function (url, options) {
        var action = this._getActionFromUrl(url, this.actionPattern);
        this.addBlocksToQueue(action);
        if (this.currentRequest === '') {
            this.newRequest(url, options);
        } else {
            this.queueRequest.push([url, options]);
        }
    },
    newRequest: function (url, options) {
        var action = this._getActionFromUrl(url, this.actionPattern);
        var options = options || {};
        var requestOptions = Object.extend({}, options);
        var self = this;
        requestOptions = Object.extend(requestOptions, {
            onComplete: function (transport) {
                self.onComplete(transport, action);
                if (options.onComplete) {
                    options.onComplete(transport);
                }
            }
        });
        this.currentRequest = new Ajax.Request(url, requestOptions);
    },
    reRequest: function (url, options) {
        this.newRequest(url, options);
    },
    _getActionFromUrl: function (url, pattern) {
        var matches = url.match(pattern);
        if (!matches || !matches[1]) {
            return null;
        }
        return matches[1];
    },
    addBlocksToQueue: function (action) {
        if (!action || !this.blockMapping[action]) {
            return;
        }
        this.blockMapping[action].each(function (name) {
            if (typeof(this.queueBlocks[name]) === 'undefined') {
                this.queueBlocks[name] = 0;
            }
            if (!this.blocks[name]) {
                return;
            }
            if (this.queueBlocks[name] === 0) {
                var selection = this.blocks[name];
                if ("appendQueueBeforeFinish" in this.blocks[name]) {
                    this.blocks[name].appendQueueBeforeFinish();
                }
                this.appendLoading(selection);
                if ("appendQueueAfterFinish" in this.blocks[name]) {
                    this.blocks[name].appendQueueAfterFinish();
                }
            }
            this.queueBlocks[name]++;
        }.bind(this));
    },
    appendLoading: function (block, loadingClass) {
        var className = loadingClass;
        if (!className) {
            className = this.loadingClass;
        }
        //fix minicart 1.9
        block.setStyle({
            'position': 'relative'
        })
        var ajaxLoading = new Element('div');
        ajaxLoading.addClassName(className);
        block.insertBefore(ajaxLoading, block.down());
    },

    onComplete: function (transport, action) {
        try {
            eval("var response = " + transport.responseText);
        } catch (e) {
            var response = {
                blocks: {}
            };
        }
        this.removeBlockQueue(action, response);
        this.currentRequest = '';
        if (this.queueRequest.length > 0) {
            this._emptyQueue();
            var args = this.queueRequest.shift();
            this.reRequest(args[0], args[1]);
        }
    },
    removeBlockQueue: function (action, response) {
        if (!action || !this.blockMapping[action]) {
            return;
        }
        var response = response || {};
        var responseBlocks = response.blocks || {};
        this.blockMapping[action].each(function (blockName) {
            if (!this.blocks[blockName]) {
                return;
            }
            this.queueBlocks[blockName]--;
            if (this.queueBlocks[blockName] === 0) {
                if (responseBlocks[blockName]) {
                    this.blocks[blockName].update(responseBlocks[blockName]);
                }
                if ("removeQueueBeforeFinish" in this.blocks[blockName]) {
                    this.blocks[blockName].removeQueueBeforeFinish(response);
                }
                this.updateNumbers();
                this.removeLoading(this.blocks[blockName]);
                if ("removeQueueAfterFinish" in this.blocks[blockName]) {
                    this.blocks[blockName].removeQueueAfterFinish(response);
                }
            }
        }.bind(this));
    },
    removeLoading: function (block, loadingClass) {
        var className = loadingClass;
        if (!className) {
            className = this.loadingClass;
        }
        var selector = "." + className;
        block.setStyle({
            'position': ''
        })
        block.select(selector).each(function (el) {
            el.remove();
        });
    },
    _emptyQueue: function () {
        var actions = [];
        var removed = [];
        //Fix for muli click to element when processing
        this.queueRequest.reverse().each(function (args, key) {
            var action = this._getActionFromUrl(args[0], this.actionPattern);
            if (actions.indexOf(action) === -1) {
                actions.push(action);
            } else {
                removed.push(key);
            }
        }.bind(this));
        var newQueue = [];
        this.queueRequest.each(function (args, key) {
            var action = this._getActionFromUrl(args[0], this.actionPattern);
            if (removed.indexOf(key) === -1) {
                newQueue.push(args);
            } else {
                this.removeBlockQueue(action);
            }
        }.bind(this));
        this.queueRequest = newQueue.reverse();
    },
    updateNumbers: function () {
        if (!this.showNumber)
            return;
        var currentField = 1;
        $$('.one-step-checkout-number').each(function (el) {
            if (el.up().getHeight() === 0 || (el.next() && el.next().getHeight() === 0)) {
                return false;
            }
            el.update(currentField + '. ');
            currentField++;
        });
    }
};
/*One Step Checkout Popup- Tiny box 2*/
var MagegiantOneStepCheckoutPopup = Class.create();
MagegiantOneStepCheckoutPopup.prototype = {
    initialize: function (config) {
        this.selector = $$(config.selector).first();
        this.tinyBoxConfig = config.tinyBoxConfig;
        this.initObserver();

    },
    initObserver: function () {
        if (this.selector) {
            this.selector.observe('click', function () {
                this.showPopup();
            }.bind(this))
        }
    },
    showPopup: function () {
        if (typeof(this.tinyBoxConfig.html) === 'object') {
            this.tinyBoxConfig.html.setStyle({
                display: ''
            });
        }
        TINY2.box.show(this.tinyBoxConfig);
    },
    hidePopup: function () {
        TINY2.box.hide();
    }
};
/* One Step Checkout Authentication Class*/
var MagegiantOneStepCheckoutAuthentication = Class.create();
MagegiantOneStepCheckoutAuthentication.prototype = {
    initialize: function (config) {
        this.popupContainer = $(config.popupContainer);
        this.loginContainer = $(config.loginContainer);
        this.forgotContainer = $(config.forgotContainer);
        this.forgotLink = $(config.forgotLink);
        this.backLink = $(config.backLink);
        this.loginUrl = config.loginUrl;
        this.forgotUrl = config.forgotUrl;
        this.loginSubmitBtn = $(config.loginSubmitBtn);
        this.forgotSubmitBtn = $(config.forgotSubmitBtn);
        this.loadingClass = config.loadingClass;
        this.errorClass = config.errorClass;
        this.successClass = config.successClass;
        this.mode = 'form_login';
        this.initObserver();

    },
    initObserver: function () {
        this.forgotLink.observe('click', function () {
            this.loginContainer.hide();
            this.forgotContainer.show();
            this.mode = 'form_forgot';

        }.bind(this));
        this.backLink.observe('click', function () {
            this.forgotContainer.hide();
            this.loginContainer.show();
            this.mode = 'form_login';

        }.bind(this));
        this.loginSubmitBtn.observe('click', function () {
            this._processLogin();

        }.bind(this));
        this.forgotSubmitBtn.observe('click', function () {
            this._processForgotPw();

        }.bind(this));
        document.observe('keypress', this.keypressHandler.bind(this));
    },
    keypressHandler: function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            if (this.mode == 'form_login') {
                this._processLogin();
            } else if (this.mode == 'form_forgot') {
                this._processForgotPw();
            }
            return;
        }
    },
    _processLogin: function () {
        var self = this;
        var loginForm = this.loginContainer.down('form');
        var successClass = this.successClass;
        var errorClass = this.errorClass;
        loginForm.validator = new Validation(loginForm);
        loginForm.select('.validation-advice').each(function (adviceEl) {
            adviceEl.remove();
        });
        if (loginForm.validator.validate()) {
            this._removeAllMessage();
            MagegiantOneStepCheckout.appendLoading(loginForm, this.loadingClass);
            var requestOption = {
                parameters: loginForm.serialize(true),
                onComplete: function (transport) {
                    try {
                        var response = transport.responseText.evalJSON();
                        if (response.success === true) {
                            MagegiantOneStepCheckout.showMessage(loginForm, response.message, successClass);
                            document.location.reload();
                        }
                        else if (response.error) {
                            MagegiantOneStepCheckout.removeLoading(loginForm, self.loadingClass);
                            MagegiantOneStepCheckout.showMessage(loginForm, response.error, errorClass);
                        }
                    } catch (e) {
                    }
                }
            };
            MagegiantOneStepCheckout.Request(this.loginUrl, requestOption);
        }
    },
    _processForgotPw: function () {
        var forgotForm = this.forgotContainer.down('form');
        var successClass = this.successClass;
        var errorClass = this.errorClass;
        forgotForm.validator = new Validation(forgotForm);
        forgotForm.select('.validation-advice').each(function (adviceEl) {
            adviceEl.remove();
        });
        if (forgotForm.validator.validate()) {
            this._removeAllMessage();
            MagegiantOneStepCheckout.appendLoading(forgotForm);
            var requestOption = {
                parameters: forgotForm.serialize(true),
                onComplete: function (transport) {
                    try {
                        var response = transport.responseText.evalJSON();
                        if (response.success === true) {
                            MagegiantOneStepCheckout.showMessage(forgotForm, response.message, successClass);
                        }
                        else if (response.error) {
                            MagegiantOneStepCheckout.showMessage(forgotForm, response.error, errorClass);
                        }
                        MagegiantOneStepCheckout.removeLoading(forgotForm);
                    } catch (e) {
                    }
                }
            };
            MagegiantOneStepCheckout.Request(this.forgotUrl, requestOption);
        }
    },
    _removeAllMessage: function () {
        MagegiantOneStepCheckout.removeMessage(this.loginContainer, this.errorClass);
        MagegiantOneStepCheckout.removeMessage(this.loginContainer, this.successClass);
        MagegiantOneStepCheckout.removeMessage(this.forgotContainer, this.errorClass);
        MagegiantOneStepCheckout.removeMessage(this.forgotContainer, this.successClass);
    }

};

/*One Step Chekcout Address Class*/
var MagegiantOnestepcheckoutAddress = Class.create();
MagegiantOnestepcheckoutAddress.prototype = {
    initialize: function (config) {
        this.addressContainer = $$(config.addressContainer).first();
        this.saveAddressUrl = config.saveAddressUrl;
        this.billingAddress = {};
        this.billingAddress.countryRegionData = {};
        this.shippingAddress = {};
        this.shippingAddress.countryRegionData = {};
    },
    setBillingAddress: function (config) {
        this.billingAddress.container = $$(config.container).first();
        this.billingAddress.addressSelect = $$(config.addressSelect).first();
        this.billingAddress.newAddressContainer = $$(config.newAddressContainer).first();
        this.billingAddress.createAccountCheckbox = $$(config.createAccountCheckbox).first();
        this.billingAddress.passwordContainer = $$(config.passwordContainer).first();
        this.billingAddress.useSameAddressCheckbox = $$(config.useSameAddressCheckbox).first();
        this.billingAddress.triggerElements = config.triggerElements;
        this.billingAddress.countryRegionElements = config.countryRegionElements;
        this.billingAddress.countrySelect = $(this.billingAddress.countryRegionElements.countrySelect);
        this.billingAddress.regionSelect = $(this.billingAddress.countryRegionElements.regionSelect);
        this.billingAddress.regionInput = $(this.billingAddress.countryRegionElements.regionInput);


    },
    setShippingAddress: function (config) {
        this.shippingAddress.container = $$(config.container).first();
        this.shippingAddress.addressSelect = $$(config.addressSelect).first();
        this.shippingAddress.newAddressContainer = $$(config.newAddressContainer).first();
        this.shippingAddress.triggerElements = config.triggerElements;
        this.shippingAddress.countryRegionElements = config.countryRegionElements;
        this.shippingAddress.countrySelect = $(this.billingAddress.countryRegionElements.countrySelect);
        this.shippingAddress.regionSelect = $(this.billingAddress.countryRegionElements.regionSelect);
        this.shippingAddress.regionInput = $(this.billingAddress.countryRegionElements.regionInput);
    },
    initAddress: function () {
        this._sameAddressProcess(this.billingAddress.useSameAddressCheckbox);
        this._processAddressSelect(this.billingAddress);
        this._processAddressSelect(this.shippingAddress);
    },
    initObserver: function () {
        //Billing
        var useSameAddressCheckbox = this.billingAddress.useSameAddressCheckbox;
        var createAccountCheckbox = this.billingAddress.createAccountCheckbox;
        var billingCountrySelect = this.billingAddress.countrySelect;
        var billingRegionSelect = this.billingAddress.regionSelect;
        var billingRegionInput = this.billingAddress.regionInput;
        var billingTriggerElements = this.billingAddress.triggerElements;
        var billingAddressSelect = this.billingAddress.addressSelect;
        if (useSameAddressCheckbox) {
            useSameAddressCheckbox.observe('change', function (evt) {
                this._sameAddressProcess(evt);
                this.changeAddressFinish();
                this._processAddressSelect(this.shippingAddress);
            }.bind(this))
        }
        if (createAccountCheckbox) {
            createAccountCheckbox.observe('change', function (evt) {
                this._createAccountProcess(evt);
            }.bind(this))
        }
        if (billingCountrySelect) {
            billingCountrySelect.observe('change', function () {
                this.countrySelectChanged(this.billingAddress);
            }.bind(this))
        }
        if (billingRegionSelect) {
            billingRegionSelect.observe('change', function () {
                this.regionSelectChanged(this.billingAddress);
            }.bind(this))
        }
        if (billingRegionInput) {
            billingRegionInput.observe('change', function () {
                this.regionInputChanged(this.billingAddress);
            }.bind(this))
        }
        if (billingTriggerElements) {
            billingTriggerElements.each(function (id) {
                var element = $(id);
                if (element) {
                    element.observe('change', function () {
                        this.triggerElementChanged(this.billingAddress);
                    }.bind(this))
                }

            }.bind(this));
        }
        if (billingAddressSelect) {
            billingAddressSelect.observe('change', function () {
                this._processAddressSelect(this.billingAddress);
                this.changeAddressFinish();

            }.bind(this))
        }
        //Shipping
        var shippingCountrySelect = this.shippingAddress.countrySelect;
        var shippingRegionSelect = this.shippingAddress.regionSelect;
        var shippingRegionInput = this.shippingAddress.regionInput;
        var shippingTriggerElements = this.shippingAddress.triggerElements;
        var shippingAddressSelect = this.shippingAddress.addressSelect;
        if (shippingCountrySelect) {
            shippingCountrySelect.observe('change', function () {
                this.countrySelectChanged(this.shippingAddress);
            }.bind(this))
        }
        if (shippingRegionSelect) {
            shippingRegionSelect.observe('change', function () {
                this.regionSelectChanged(this.shippingAddress);
            }.bind(this))
        }
        if (shippingRegionInput) {
            shippingRegionInput.observe('change', function () {
                this.regionInputChanged(this.shippingAddress);
            }.bind(this))
        }
        if (shippingTriggerElements) {
            shippingTriggerElements.each(function (id) {
                var element = $(id);
                if (element) {
                    element.observe('change', function () {
                        this.triggerElementChanged(this.shippingAddress);
                    }.bind(this))
                }

            }.bind(this));
        }
        if (shippingAddressSelect) {
            shippingAddressSelect.observe('change', function () {
                this._processAddressSelect(this.shippingAddress);
                this.changeAddressFinish();

            }.bind(this))
        }
        //Save Form Data Session
        Form.getElements(this.addressContainer).each(function (el) {
            var elId = el.id;
            if (el === this.billingAddress.useSameAddressCheckbox
                || this.billingAddress.triggerElements.indexOf(elId) !== -1
                || this.shippingAddress.triggerElements.indexOf(elId) !== -1) {
                return false;
            }
            else {
                el.observe('change', function () {
                    this.saveFormData();
                }.bind(this));
            }

        }.bind(this))

    },
    _sameAddressProcess: function (evt) {
        if (typeof(evt) === 'object') {
            var isChecked = false;
            if (evt.target)
                isChecked = evt.target.checked;
            else isChecked = evt.checked;
            if (isChecked) {
                this.hideShippingAddress();
            }
            else {
                this.showShippingAddress();
            }
        }
    },
    _createAccountProcess: function (evt) {
        if (typeof(evt) === 'object') {
            var isChecked = false;
            if (evt.target)
                isChecked = evt.target.checked;
            else isChecked = evt.checked;
            if (isChecked) {
                this.showPasswordContainer();
            }
            else {
                this.hidePasswordContainer();
            }
        }
    },
    showPasswordContainer: function () {
        var passwordContainer = this.billingAddress.passwordContainer;
        passwordContainer.setStyle({
            display: ''
        });
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(passwordContainer);
        MagegiantOneStepCheckout.applyMorphEffect(passwordContainer, newHeight, 0.3, function () {
            passwordContainer.setStyle({'height': ''});
        });
    },
    hidePasswordContainer: function () {
        var passwordContainer = this.billingAddress.passwordContainer;
        MagegiantOneStepCheckout.applyMorphEffect(passwordContainer, 0, 0.5, function () {
            passwordContainer.setStyle({'display': 'none'});
        });
    },
    changeAddressFinish: function () {
        var params = {};
        var billing = '';
        var shipping = '';
        if (this.billingAddress.container) {
            billing = Form.serialize(this.billingAddress.container, true);

        }
        if (this.shippingAddress.container) {
            shipping = Form.serialize(this.shippingAddress.container, true);
        }
        if (billing) {
            params = Object.extend(params, billing);
        }
        if (shipping) {
            params = Object.extend(params, shipping);
        }
        var requestOptions = {
            method: 'post',
            parameters: params
        };
        MagegiantOneStepCheckout.Request(this.saveAddressUrl, requestOptions)

    },
    showShippingAddress: function () {
        var shippingContainer = this.shippingAddress.container;
        shippingContainer.setStyle({
            display: ''
        });
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(shippingContainer);
        MagegiantOneStepCheckout.applyMorphEffect(shippingContainer, newHeight, 0.3, function () {
            shippingContainer.setStyle({
                height: ''
            })
            MagegiantOneStepCheckout.updateNumbers();
        });

    },
    hideShippingAddress: function () {
        var shippingContainer = this.shippingAddress.container;
        MagegiantOneStepCheckout.applyMorphEffect(shippingContainer, 0, 0.5, function () {
            shippingContainer.setStyle({
                display: 'none'
            })
            MagegiantOneStepCheckout.updateNumbers();
        });

    },
    countrySelectChanged: function (targetAddress) {
        var regionData = targetAddress.countryRegionData[targetAddress.countrySelect.getValue()];
        if (regionData) {
            if (regionData.type === 'regionSelect') {
                targetAddress.regionSelect.setValue(regionData.value);
            }
            else {
                targetAddress.regionInput.setValue(regionData.value);
            }
        }
    },
    regionSelectChanged: function (targetAddress) {
        targetAddress.countryRegionData[targetAddress.countrySelect.getValue()] = {
            'type': 'regionSelect',
            'value': targetAddress.regionSelect.getValue()
        }
    },
    regionInputChanged: function (targetAddress) {
        targetAddress.countryRegionData[targetAddress.countrySelect.getValue()] = {
            'type': 'regionInput',
            'value': targetAddress.regionInput.getValue()
        }
    },
    triggerElementChanged: function (targetAddress) {
        var flag = targetAddress.triggerElements.all(function (elementId) {
            var element = $(elementId);
            if (element) {
                var cn = $w(element.className);
                return cn.all(function (name) {
                    var v = Validation.get(name);
                    try {
                        if (Validation.isVisible(element) && !v.test($F(element), element)) {
                            return false;
                        } else {
                            return true;
                        }
                    } catch (e) {
                        return true;
                    }
                });
            }
        }, this);
        if (flag) {
            this.changeAddressFinish();
        }
    },
    _processAddressSelect: function (targetAddress) {
        if (targetAddress.addressSelect) {
            if (targetAddress.addressSelect.value) {
                this.hideNewAddressContainer(targetAddress)
            }
            else {
                this.showNewAddressContainer(targetAddress);
            }
        }
    },
    showNewAddressContainer: function (targetAddress) {
        var newAddressContainer = targetAddress.newAddressContainer;
        newAddressContainer.setStyle({
            display: ''
        });
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(newAddressContainer);
        MagegiantOneStepCheckout.applyMorphEffect(newAddressContainer, newHeight, 0.3, function () {
            targetAddress.container.setStyle({
                height: ''
            })
        }.bind(this));
    },
    hideNewAddressContainer: function (targetAddress) {
        var newAddressContainer = targetAddress.newAddressContainer;
        MagegiantOneStepCheckout.applyMorphEffect(newAddressContainer, 0, 0.5, function () {
            newAddressContainer.setStyle({
                display: 'none'
            })
        }.bind(this));
    },
    saveFormData: function () {
        MagegiantOneStepCheckout.saveFormData(this.addressContainer);
    }

};
/*Auto detect address by stress Google Map Api*/
MagegiantOnestepcheckoutAddressDetect = Class.create();
MagegiantOnestepcheckoutAddressDetect.prototype = {
    initialize: function (config) {
        // init dom elements
        this.inputSelector = $(config.inputSelector);
        this.componentForm = config.componentForm;
        this.addressType = config.addressType;
        this.addressElementsIds = config.addressElementsIds;
        this.regionUpdater = config.regionUpdater;
        this.giantOSCAddress = config.giantOSCAddress;
        this.geolocation = $(config.geolocation);
        this.specificCountry = config.specificCountry;
        // init observer
        this.initGoogleAutoComplete();
    },
    initGoogleAutoComplete: function () {
        var me = this;
        if (this.inputSelector) {
            var options = {
                types: ['geocode']
            };
            if (this.specificCountry) {
                var restrictions = {
                    componentRestrictions: {country: this.specificCountry}
                }
                options = Object.extend(options, restrictions);
            }
            //Google Auto Complete
            this.googleAutoComplete = new google.maps.places.Autocomplete(this.inputSelector, options);
            google.maps.event.addListener(this.googleAutoComplete, 'place_changed', function () {
                me.googleResponse(me.googleAutoComplete.getPlace());
            });
        }
        // Geolocation
        if (this.geolocation) {
            this.geolocation.observe('click', function () {
                this.geoLocate();
            }.bind(this));
        }
    },
    googleResponse: function (place) {
        var street, city, region_id, region, country, postal_code, sublocality;

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (this.componentForm[addressType]) {
                if (addressType == 'street_number') {
                    if (street && place.address_components[i][this.componentForm['street_number']])
                        street += ', ' + place.address_components[i][this.componentForm['street_number']];
                    else
                        street = place.address_components[i][this.componentForm['street_number']];
                }
                if (addressType == 'route') {
                    if (street && place.address_components[i][this.componentForm['route']])
                        street += ' ' + place.address_components[i][this.componentForm['route']];
                    else
                        street = place.address_components[i][this.componentForm['route']];
                }
                if (addressType == 'neighborhood') {
                    if (street && place.address_components[i][this.componentForm['neighborhood']])
                        street += ', ' + place.address_components[i][this.componentForm['neighborhood']];
                    else
                        street = place.address_components[i][this.componentForm['neighborhood']];
                }
                if (addressType == 'administrative_area_level_2') {
                    if (street && place.address_components[i][this.componentForm['administrative_area_level_2']])
                        street += ', ' + place.address_components[i][this.componentForm['administrative_area_level_2']];
                    else
                        street = place.address_components[i][this.componentForm['administrative_area_level_2']];
                }
                if (addressType == 'locality')
                    city = place.address_components[i][this.componentForm['locality']];
                if (addressType == 'administrative_area_level_1') {
                    region_id = place.address_components[i]['short_name'];
                    region = place.address_components[i]['long_name'];
                }
                if (addressType == 'country')
                    country = place.address_components[i][this.componentForm['country']];
                if (addressType == 'postal_code')
                    postal_code = place.address_components[i][this.componentForm['postal_code']];
            }
        }
        this.responseComponents = {
            street1: street,
            city: city,
            region_id: region_id,
            region: region,
            country_id: country,
            postcode: postal_code,
        };
        this.fillAddressById();
    },
    fillAddressById: function () {
        var me = this;
        var billingAddress = this.giantOSCAddress.billingAddress;
        var shippingAddress = this.giantOSCAddress.shippingAddress;
        this.addressElementsIds.each(function (elementId) {
            var element = $(me.addressType + ":" + elementId);
            if (element && me.responseComponents[elementId]) {
                element.value = me.responseComponents[elementId];
            }
        });
        if (this.regionUpdater)
            this.regionUpdater.update();
        if (this.addressType == 'billing') {
            this.giantOSCAddress.triggerElementChanged(billingAddress);
        }
        else {
            this.giantOSCAddress.triggerElementChanged(shippingAddress);
        }
    },
    geoLocate: function () {
        if (navigator.geolocation) {
            var me = this;
            navigator.geolocation.getCurrentPosition(function (position) {
                me.getAddressBytLatitude(position.coords.latitude, position.coords.longitude);
            });
        }
    },
    getAddressBytLatitude: function (myLatitude, myLongitude) {
        var me = this;
        var geocoder = new google.maps.Geocoder();
        var location = new google.maps.LatLng(myLatitude, myLongitude);
        geocoder.geocode({'latLng': location}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                me.googleResponse(results[0]);
            } else {
                return false;
            }
        });
    }
};
/*Shipping Method Class*/

MagegiantOnestepcheckoutShippingMethod = Class.create();
MagegiantOnestepcheckoutShippingMethod.prototype = {
    initialize: function (config) {
        this.shipingMethodContainer = $$(config.shipingMethodContainer).first();
        this.shippingMethodElements = $$(config.shippingMethodElements);
        this.shippingMethodAdvice = $$(config.shippingMethodAdvice).first();
        this.saveShippingMethodUrl = config.saveShippingMethodUrl;
        window.shippingMethod = {};
        window.shippingMethod.validator = null;
        this.initObserver();
    },
    initObserver: function () {
        this.shippingMethodElements.each(function (element) {
            element.observe('click', function () {
                this.removeValidationAdvice();
                this.saveShippingMethod(element.value);
            }.bind(this));
        }.bind(this))
    },

    saveShippingMethod: function (methodCode) {
        if (this.currentMethod !== methodCode) {
            var params = Form.serialize(this.shipingMethodContainer, true);
            MagegiantOneStepCheckout.Request(this.saveShippingMethodUrl, {
                method: 'post',
                parameters: params
            });
            this.currentMethod = methodCode;
        }
    },
    removeValidationAdvice: function () {
        if (this.shippingMethodAdvice) {
            this.shippingMethodAdvice.update('').hide();
        }
    }
};

/*Payment Method Class*/
MagegiantOnestepcheckoutPaymentMethod = Class.create();
MagegiantOnestepcheckoutPaymentMethod.prototype = {
    initialize: function (config) {
        this.paymentMethodContainer = $$(config.paymentMethodContainer).first();
        this.paymentMethodWrapper = $$(config.paymentMethodWrapper).first();
        this.paymentMethodElements = $$(config.paymentMethodElements);
        this.paymentMethodAdvice = $$(config.paymentMethodAdvice).first();
        this.savePaymentMethodUrl = config.savePaymentMethodUrl;
        this.cvv = {
            container: $$(config.cvv.container).first(),
            showEl: $$(config.cvv.showEl).first(),
            hideEl: $$(config.cvv.hideEl).first()
        };
        this.initPayment();
        this.initObserver();
    },
    initPayment: function () {
        var me = this;
        this.paymentMethodElements.each(function (element) {
            var methodCode = element.value;
            var additionalInfoContainer = $('payment_form_' + methodCode);
            if (additionalInfoContainer) {
                additionalInfoContainer.setStyle({'overflow': 'hidden', 'display': 'none'})
            }
            if (element.checked) {
                this.changeVisible(methodCode, false);
                this.currentMethod = methodCode;
            } else {
                this.changeVisible(methodCode, true);
            }
        }.bind(this));
    },
    initObserver: function () {
        var self = this;
        this.paymentMethodElements.each(function (element) {
            element.observe('click', function () {
                this.removeValidationAdvice();
                this.switchMethod(element.value);
            }.bind(this));
            var block = 'payment_form_' + element.value;
            [block + '_before', block, block + '_after'].each(function (elementId) {
                var element = $(elementId);
                if (!element) {
                    return;
                }
                Form.getElements(element).each(function (formElement) {
                    formElement.observe('change', function (e) {
                        self.savePaymentMethod();
                        Validation.reset(formElement);
                    });
                }.bind(this));
            });

        }.bind(this));
        var cvvShowEl = this.cvv.showEl;
        var cvvHideEl = this.cvv.hideEl;
        if (cvvShowEl) {
            cvvShowEl.observe('click', function (evt) {
                this.toogleCvvTooltip(evt, true);
            }.bind(this));
        }
        if (cvvHideEl) {
            cvvHideEl.observe('click', function (evt) {
                this.toogleCvvTooltip(evt, false);
            }.bind(this))
        }
        if (!this.paymentMethodWrapper.appendQueueAfterFinish) {
            this.paymentMethodWrapper.appendQueueAfterFinish = function () {
                this.storeValues = {};
                Form.getElements(this.paymentMethodWrapper).each(function (el) {
                    var elementId = el.id;
                    if (elementId in this.storeValues) {
                        el.setValue(this.storeValues[elementId]);
                    }

                }.bind(this))
            }.bind(this);
        }
        if (!this.paymentMethodWrapper.removeQueueAfterFinish) {
            this.paymentMethodWrapper.removeQueueAfterFinish = function () {
                Form.getElements(this.paymentMethodWrapper).each(function (el) {
                    var elementId = el.id;
                    if (elementId in this.storeValues) {
                        el.setValue(this.storeValues[elementId]);
                    }

                }.bind(this));
                this.storeValues = {};
            }.bind(this);
        }

    },
    removeValidationAdvice: function () {
        if (this.paymentMethodAdvice) {
            this.paymentMethodAdvice.update('').hide();
        }
    },
    toogleCvvTooltip: function (evt, isShow) {
        if (this.cvv.container) {
            if (isShow) {
                this.cvv.container.setStyle({
                    display: '',
                    top: (Event.pointerY(evt)) + 40 + 'px'
                });
            }
            else {
                this.cvv.container.setStyle({
                    display: 'none'
                });
            }
        }
        evt.stop();
    },

    switchMethod: function (methodCode) {
        var hideOldForm = true;
        if (methodCode === 'customercredit') {
            hideOldForm = false;
            var element = $('p_method_customercredit');
            if (!element || !element.checked) {
                methodCode = '';
            }
        }

        if (hideOldForm && this.currentMethod && $('payment_form_' + this.currentMethod + '_preencrypt')) {
            this.changeVisible(this.currentMethod + '_preencrypt', true);
        }

        if (hideOldForm && this.currentMethod && $('payment_form_' + this.currentMethod)) {
            this.changeVisible(this.currentMethod, true);
        }

        if ($('payment_form_' + methodCode) || $('payment_form_' + methodCode + '_preencrypt')) {
            if ($('payment_form_' + methodCode)) {
                this.changeVisible(methodCode, false)
            } else {
                this.changeVisible(methodCode + '_preencrypt', false);
            }
        } else {
            //Event fix for payment methods without form like "Check / Money order"
            $(document.body).fire('payment-method:switched', {method_code: methodCode});
        }

        if (hideOldForm) {
            this.currentMethod = methodCode;
        }
        this.savePaymentMethod();
        if (typeof MultiFees !== 'undefined') {
            MultiFees.showPayment();
        }
    },
    changeVisible: function (methodCode, mode) {
        var block = 'payment_form_' + methodCode;
        [block + '_before', block, block + '_after'].each(function (el) {
            var element = $(el);
            if (element) {
                element.setStyle({
                    'overflow': 'hidden'
                });
                if (!mode) {
                    element.setStyle({
                        'display': '',
                        'height': '0px'
                    })
                    var newHeight = MagegiantOneStepCheckout.getHeightFromElement(element);
                    MagegiantOneStepCheckout.applyMorphEffect(element, newHeight, 0.3, function () {
                        element.setStyle({
                            'height': ''
                        })
                    })
                }
                else {
                    MagegiantOneStepCheckout.applyMorphEffect(element, 0, 0.5, function () {
                        element.setStyle({
                            'display': 'none'
                        })
                    })
                }
                element.select('input', 'select', 'textarea', 'button').each(function (field) {
                    field.disabled = mode;
                });
            }
        });
    },
    savePaymentMethod: function () {
        var isValid = true;
        var block = 'payment_form_' + this.currentMethod;
        [block + '_before', block, block + '_after'].each(function (el) {
            var element = $(el);
            if (!element) {
                return;
            }
            Form.getElements(element).each(function (vElm) {
                var cn = $w(vElm.className);
                isValid = isValid && cn.all(function (name) {
                    var v = Validation.get(name);
                    try {
                        if (Validation.isVisible(vElm) && !v.test($F(vElm), vElm)) {
                            return false;
                        } else {
                            return true;
                        }
                    } catch (e) {
                        return true;
                    }
                });
            })
        });
        if (!isValid) {
            return;
        }
        window.payment.currentMethod = this.currentMethod;
        //Save payment method request
        var params = Form.serialize(this.paymentMethodContainer, true);
        MagegiantOneStepCheckout.Request(this.savePaymentMethodUrl, {
            method: 'post',
            parameters: params
        });
    }
};

/* Review Cart Class */
MagegiantOnestepcheckoutReviewCart = Class.create();
MagegiantOnestepcheckoutReviewCart.prototype = {
    initialize: function (config) {
        this.removeProductLinks = $$(config.removeProductLinks);
        this.removeProductConfirmMessage = config.removeProductConfirmMessage;
        this.plusProductLinks = $$(config.plusProductLinks);
        this.minusProductLinks = $$(config.minusProductLinks);
        this.ajaxCartItemUrl = config.ajaxCartItemUrl;
        this.initObserver();
    },

    initObserver: function () {
        this.removeProductLinks.each(function (link) {
            link.observe('click', function (evt) {
                this.onClickRemoveProductLink(evt);
            }.bind(this))
        }.bind(this));
        this.plusProductLinks.each(function (link) {
            link.observe('click', function (evt) {
                this.onClickPlusProductLink(evt);
            }.bind(this))
        }.bind(this));
        this.minusProductLinks.each(function (link) {
            link.observe('click', function (evt) {
                this.onClickMinusProductLink(evt);
            }.bind(this))
        }.bind(this));

    },
    onClickMinusProductLink: function (evt) {
        var itemId = evt.target.id;
        this._processCartItem('minus', itemId);
        evt.stop;
    },
    onClickPlusProductLink: function (evt) {
        var itemId = evt.target.id;
        this._processCartItem('plus', itemId);
        evt.stop;
    },
    onClickRemoveProductLink: function (evt) {
        if (!confirm(this.removeProductConfirmMessage)) {
            return false;
        }
        var itemId = evt.target.id;
        this._processCartItem('remove', itemId);
        evt.stop();
    },
    _processCartItem: function (action, itemId) {
        var requestOptions = {
            method: 'post',
            parameters: {
                action: action,
                id: itemId
            },
            onComplete: function (transport) {
                try {
                    var response = transport.responseText.evalJSON();
                    if (!response.success && response.error) {
                        alert(response.error);
                    }
                } catch (e) {
                }
            }
        };
        MagegiantOneStepCheckout.Request(this.ajaxCartItemUrl, requestOptions);
    }

};
/* Review Coupon Class */
MagegiantOnestepcheckoutReviewCoupon = Class.create();
MagegiantOnestepcheckoutReviewCoupon.prototype = {
    initialize: function (config) {
        this.couponContainer = $$(config.couponContainer).first();
        this.couponInput = $(config.couponInput);
        this.applyCouponUrl = config.applyCouponUrl;
        this.isAppliedCoupon = config.isAppliedCoupon;
        this.showApplyButton = config.showApplyButton;
        this.applyCouponButton = $$(config.applyCouponButton).first();
        this.cancelCouponButton = $$(config.cancelCouponButton).first();
        this.messageContainer = $$(config.messageContainer).first();
        this.errorClass = config.errorClass;
        this.successClass = config.successClass;
        this.initObserver();
    },

    initObserver: function () {
        if (this.showApplyButton) {
            if (this.applyCouponButton) {
                this.applyCouponButton.observe('click', function () {
                    this.processCoupon();
                }.bind(this));
                this.cancelCouponButton.observe('click', function () {
                    this.processCoupon();
                }.bind(this));
            }
        }
        else {
            if (this.couponInput) {
                this.couponInput.observe('change', function () {
                    this.processCoupon();
                }.bind(this))
            }
        }
    },
    processCoupon: function () {
        var couponContainer = this.couponContainer;
        couponContainer.select('.validation-advice').each(function (adviceEl) {
            adviceEl.remove();
        });
        this._removeAllMessage();
        if (this.showApplyButton) {
            if (!this.isAppliedCoupon) {
                this.couponInput.addClassName('required-entry');
                var isValidated = Validation.validate(this.couponInput);
                this.couponInput.removeClassName('required-entry');
                if (!isValidated) {
                    return false;
                }
            }
            else {
                this.couponInput.setValue('');
            }
        }
        else {
            if (!this.couponInput.getValue() && !this.isAppliedCoupon) {
                return false;
            }
        }
        var messageContainer = this.messageContainer;
        var successClass = this.successClass;
        var errorClass = this.errorClass;
        var self = this;
        var requestOptions = {
            method: 'post',
            parameters: {
                coupon_code: this.couponInput.getValue()
            },
            onComplete: function (transport) {
                try {
                    var response = transport.responseText.evalJSON();
                    self.isAppliedCoupon = response.coupon_applied;
                    if (response.success === true) {
                        MagegiantOneStepCheckout.showMessage(messageContainer, response.messages, successClass);
                    }
                    else if (response.messages) {
                        MagegiantOneStepCheckout.showMessage(messageContainer, response.messages, errorClass);
                    }
                } catch (e) {
                }
                if (self.isAppliedCoupon) {
                    self.applyCouponButton.hide();
                    self.cancelCouponButton.show();
                }
                else {
                    self.applyCouponButton.show();
                    self.cancelCouponButton.hide();
                }
                var newHeight = MagegiantOneStepCheckout.getHeightFromElement(messageContainer);
                MagegiantOneStepCheckout.applyMorphEffect(messageContainer, newHeight, 0.3, function () {
                    messageContainer.setStyle({
                        'display': 'none'
                    })
                });
            }
        }
        MagegiantOneStepCheckout.Request(this.applyCouponUrl, requestOptions);
    },
    _removeAllMessage: function () {
        MagegiantOneStepCheckout.removeMessage(this.couponContainer, this.errorClass);
        MagegiantOneStepCheckout.removeMessage(this.couponContainer, this.successClass);
        MagegiantOneStepCheckout.applyMorphEffect(this.messageContainer, 0, 0.5, function () {
            this.messageContainer.setStyle({
                display: 'none'
            })
        }.bind(this))
    }
};
/* Review Comment Class*/
MagegiantOnestepcheckoutReviewComment = Class.create();
MagegiantOnestepcheckoutReviewComment.prototype = {
    initialize: function (config) {
        this.commentContainer = $$(config.commentContainer).first();
        this.newRowCount = config.newRowCount || 5;
        this.commentContainer.select('textarea').each(function (textarea) {
            textarea.setStyle({
                'overflow-y': 'hidden'
            });
            this.textAreaEffectObserver(textarea);
        }.bind(this));
        Form.getElements(this.commentContainer).each(function (element) {
            element.observe('change', function () {
                this.saveFormData();
            }.bind(this));
        }.bind(this));
    },

    saveFormData: function () {
        MagegiantOneStepCheckout.saveFormData(this.commentContainer);
    },
    textAreaEffectObserver: function (textarea) {
        var orgScrollHeight = textarea.scrollHeight;
        var orgRowCount = parseInt(textarea.getAttribute('rows'));
        var orgHeight = parseInt(textarea.getStyle('height'));
        textarea.observe('focus', function () {
            var currentRowCount = orgRowCount +
                (((textarea.scrollHeight - orgScrollHeight) * orgRowCount) / orgHeight);
            if (currentRowCount < this.newRowCount) {
                currentRowCount = this.newRowCount;
            } else {
                currentRowCount++; //add on empty line
            }
            var currentHeight = (orgHeight / orgRowCount) * currentRowCount;
            this.rowsAttributeEffect(textarea, currentRowCount, currentHeight, function () {
                textarea.setStyle({
                    'overflow-y': 'auto'
                });
            });
        }.bind(this));
        textarea.observe('blur', function () {
            var lengthOfValue = textarea.getValue().strip().length;
            if (lengthOfValue === 0) {
                this.scrollTextareaEffect(textarea, function () {
                    textarea.setStyle({
                        'overflow-y': 'hidden'
                    });
                    this.rowsAttributeEffect(textarea, orgRowCount, orgHeight);
                }.bind(this));
            } else {
                var newHeight = (orgHeight / orgRowCount) * this.newRowCount;
                this.scrollTextareaEffect(textarea, function () {
                    textarea.setStyle({
                        'overflow-y': 'hidden'
                    });
                    this.rowsAttributeEffect(textarea, this.newRowCount, newHeight);
                }.bind(this));
            }
        }.bind(this));
    },

    rowsAttributeEffect: function (textarea, newRows, newHeight, afterFinish) {
        if (textarea.effect) {
            textarea.effect.cancel();
        }
        var afterFinish = afterFinish || new Function();
        textarea.effect = new Effect.Morph(textarea, {
            style: {
                height: newHeight + "px"
            },
            duration: 0.5,
            afterFinish: function () {
                textarea.setAttribute('rows', newRows);
                delete textarea.effect;
                afterFinish();
            }
        });
    },

    scrollTextareaEffect: function (textarea, afterFinish) {
        if (textarea.effect) {
            textarea.effect.cancel();
        }
        var afterFinish = afterFinish || new Function();
        if (textarea.scrollTop === 0) {
            afterFinish();
            return;
        }
        new Effect.Tween(textarea, textarea.scrollTop, 0, {
            duration: 0.5,
            afterFinish: function () {
                afterFinish();
            }
        }, 'scrollTop');
    }
};

/*Gift Message Class*/
MagegiantOnestepcheckoutReviewGiftmessage = Class.create();
MagegiantOnestepcheckoutReviewGiftmessage.prototype = {
    initialize: function (config) {
        this.giftMessageContainer = $$(config.giftMessageContainer).first();
        this.giftMessageForm = $$(config.giftMessageForm).first();
        this.useGiftmessageCheckbox = $$(config.useGiftmessageCheckbox).first();
        this.isUsedGiftMessage = $$(config.isUsedGiftMessage).first();
        this.inputElementIds = config.inputElementIds;
        this.initGiftMessage();
        this.initObserver();
    },
    initGiftMessage: function () {
        if (this.useGiftmessageCheckbox) {
            this.processGiftMessage(this.useGiftmessageCheckbox);
        }
    },
    initObserver: function () {
        if (this.useGiftmessageCheckbox) {
            this.useGiftmessageCheckbox.observe('change', function (evt) {
                this.processGiftMessage(evt);
            }.bind(this));
        }
        this.inputElementIds.each(function (el) {
            var element = $(el);
            if (element) {
                element.observe('change', function () {
                    this.saveFormData();
                }.bind(this))
            }
        }.bind(this))
    },
    saveFormData: function () {
        MagegiantOneStepCheckout.saveFormData(this.giftMessageContainer);
    },
    processGiftMessage: function (evt) {
        if (typeof(evt) === 'object') {
            var element;
            if (evt.target) {
                element = evt.target;
            }
            else {
                element = evt;
            }
            if (element.checked) {
                this.isUsedGiftMessage.value = 1;
                this.showGiftMessage();
            }
            else {
                this.isUsedGiftMessage.value = 0;
                this.hideGiftMessage();
            }
            this.saveFormData();
        }
    },
    showGiftMessage: function () {
        var giftMessageForm = this.giftMessageForm;
        giftMessageForm.setStyle({'display': ''});
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.giftMessageForm);
        MagegiantOneStepCheckout.applyMorphEffect(this.giftMessageForm, newHeight, 0.3, function () {
            giftMessageForm.setStyle({'height': ''});
        })
    },
    hideGiftMessage: function () {
        var giftMessageForm = this.giftMessageForm;
        MagegiantOneStepCheckout.applyMorphEffect(this.giftMessageForm, 0, 0.5, function () {
            giftMessageForm.setStyle({'display': 'none'});
        });
        this.clearInput();
    },
    clearInput: function () {
        this.inputElementIds.each(function (el) {
            if ($(el)) {
                $(el).setValue('');
            }
        })
    }
};

/* Gift Wrap Class */
MagegiantOnestepcheckoutReviewGiftwrap = Class.create();
MagegiantOnestepcheckoutReviewGiftwrap.prototype = {
    initialize: function (config) {
        this.useGiftWrapCheckbox = $$(config.useGiftWrapCheckbox).first();
        this.addGiftWrapUrl = config.addGiftWrapUrl;
        this.initObserver();
    },
    initObserver: function () {
        if (this.useGiftWrapCheckbox) {
            this.useGiftWrapCheckbox.observe('change', this.applyGiftWrap.bind(this))
        }
    },
    applyGiftWrap: function () {
        var requestOptions = {
            method: 'post',
            parameters: {
                is_used_giftwrap: this.useGiftWrapCheckbox.getValue()
            }
        };
        MagegiantOneStepCheckout.Request(this.addGiftWrapUrl, requestOptions);
    }
};

/*Delivery Time Class*/
MagegiantOnestepcheckoutReviewDelivery = Class.create();
MagegiantOnestepcheckoutReviewDelivery.prototype = {
    initialize: function (config) {
        this.deliveryContainer = $$(config.deliveryContainer).first();
        this.deliveryWrapper = $$(config.deliveryWrapper).first();
        this.useDeliveryCheckbox = $$(config.useDeliveryCheckbox).first();
        this.isUsedDeliveryTime = $$(config.isUsedDeliveryTime).first();
        this.initDelivery();
        this.initObserve();
    },
    initDelivery: function () {
        this.handleDelivery();
    },
    initObserve: function () {
        this.useDeliveryCheckbox.observe('change', function () {
            this.handleDelivery();
            this.saveFormData();
        }.bind(this))
    },
    handleDelivery: function () {
        if (this.useDeliveryCheckbox.getValue()) {
            this.isUsedDeliveryTime.value = 1;
            this.showDelivery();
        }
        else {
            this.isUsedDeliveryTime.value = 0;
            this.hideDelivery();
        }
    },
    saveFormData: function () {
        MagegiantOneStepCheckout.saveFormData(this.deliveryContainer);
    },
    showDelivery: function () {
        var self = this;
        var newHeight = MagegiantOneStepCheckout.getHeightFromElement(this.deliveryWrapper);
        this.deliveryWrapper.setStyle({'display': ''});
        MagegiantOneStepCheckout.applyMorphEffect(this.deliveryWrapper, newHeight, 0.3, function () {
            self.deliveryWrapper.setStyle({'height': ''});
        });

    },
    hideDelivery: function () {
        var self = this;
        MagegiantOneStepCheckout.applyMorphEffect(self.deliveryWrapper, 0, 0.5, function () {
            self.deliveryWrapper.setStyle({'display': 'none'});
        });
    }
};

/* Term and Condition Class*/
MagegiantOneStepCheckoutReviewTerms = Class.create();
MagegiantOneStepCheckoutReviewTerms.prototype = {
    initialize: function (config) {
        this.termContainer = $$(config.termContainer).first();
        this.termItemElements = $$(config.termItemElements);
        this.linkFromItem = config.linkFromItem;
        this.checkboxFromItem = config.checkboxFromItem;
        this.checkedFromItem = config.checkedFromItem;
        this.acceptTermItem = config.acceptTermItem;
        this.descriptionContainerFromItem = config.descriptionContainerFromItem;
        this.isRequiredReadTerm = config.isRequiredReadTerm;
        this.readTermMessage = config.readTermMessage;
        this.errorClass = config.errorClass;
        this.popup = {};
        this.initObserver();
    },
    initObserver: function () {
        this.termItemElements.each(function (item) {
            var link = item.select(this.linkFromItem).first();
            var checkbox = item.select(this.checkboxFromItem).first();
            var isChecked = item.select(this.checkedFromItem).first();
            var accept = item.select(this.acceptTermItem).first();
            var description = item.select(this.descriptionContainerFromItem).first();
            if (!link || !description) {
                return;
            }
            var linkId = link.id;
            if (checkbox) {
                if (isChecked.value == 1) {
                    checkbox.checked = true;
                }
                else {
                    checkbox.checked = false;
                }
                checkbox.observe('click', function (evt) {
                    this._removeAllMessage();
                    if (this.isRequiredReadTerm && isChecked.value == 0) {
                        evt.target.checked = false;
                        MagegiantOneStepCheckout.showMessage(this.termContainer, this.readTermMessage, this.errorClass);
                    }
                    if (evt.target.checked) {
                        isChecked.value = 1;
                    }
                    else {
                        isChecked.value = 0;
                    }
                    this.saveFormData();
                }.bind(this));
            }
            this.popup[linkId] = new MagegiantOneStepCheckoutPopup({
                selector: '#' + linkId,
                tinyBoxConfig: {
                    html: description
                }
            });
            if (accept) {
                accept.observe('click', function () {
                    checkbox.checked = true;
                    isChecked.value = 1;
                    this._removeAllMessage();
                    this.popup[linkId].hidePopup();
                    this.saveFormData();
                }.bind(this));
            }
        }.bind(this));
    },
    _removeAllMessage: function () {
        this.termContainer.select('.validation-advice').each(function (adviceEl) {
            adviceEl.remove();
        });
        MagegiantOneStepCheckout.removeMessage(this.termContainer, this.errorClass);
    },
    saveFormData: function () {
        MagegiantOneStepCheckout.saveFormData(this.termContainer);
    }
};

MagegiantOneStepCheckoutForm = Class.create();
MagegiantOneStepCheckoutForm.prototype = {
    initialize: function (config) {
        this.checkoutForm = new VarienForm(config.checkoutForm);
        //Shipping Method
        this.shippingMethodWrapper = config.shippingMethodWrapper;
        this.shippingMethodInput = config.shippingMethodInput;
        this.shippingMethodAdvice = config.shippingMethodAdvice;
        this.shippingValidationMessage = config.shippingValidationMessage;
        //Payment Method
        this.paymentMethodWrapper = config.paymentMethodWrapper;
        this.paymentMethodInput = config.paymentMethodInput;
        this.paymentMethodAdvice = config.paymentMethodAdvice;
        this.paymentValidationMessage = config.paymentValidationMessage;
        //Place Order
        this.reviewCartContainer = $$(config.reviewCartContainer).first();
        this.placeOrderButton = $(config.placeOrderButton);
        this.placeOrderUrl = config.placeOrderUrl;
        this.successUrl = config.successUrl;
        this.placeOrderButton = $(config.placeOrderButton);
        this.showGrandTotalAmount = config.showGrandTotalAmount;
        this.grandTotalAmount = this.placeOrderButton.select(config.grandTotalAmount).first();
        this.grandTotalAmountProcess = this.placeOrderButton.select(config.grandTotalAmountProcess).first();
        this.pleaseWaitNotice = $$(config.pleaseWaitNotice).first();
        this.disabledClassName = config.disabledClassName;
        this.overlayClassName = config.overlayClassName;
        Event.fire(document, 'giant_checkout:form_init_before', {form: this});
        this.initObserver();
        Event.fire(document, 'giant_checkout:form_init_after', {form: this});
    },
    initObserver: function () {
        if (this.placeOrderButton) {
            this.placeOrderButton.observe('click', function () {
                this.placeOrderProcess();
            }.bind(this));
        }
    },
    placeOrderProcess: function () {
        if (this.validate()) {
            MagegiantOneStepCheckout.addOverlay(document.body, this.overlayClassName);
            this.showPleaseWait();
            this.disablePlaceOrderButton();
            this._placeOrderRequest();
        }
    },
    _placeOrderRequest: function () {
        var parames = Form.serialize(this.checkoutForm.form, true);
        var requestOption = {
            method: 'post',
            parameters: parames,
            onComplete: function (transport) {
                this.placeOrderComplete(transport);
            }.bind(this)
        }
        new Ajax.Request(this.placeOrderUrl, requestOption);
    },
    placeOrderComplete: function (transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            } catch (e) {
                response = {};
            }
            Event.fire(window, 'magegiant:one_step_checkout_place_order_complete', {object: this, res: response});
            if (response.isProcess)
                return;
            if (response.redirect) {
                setLocation(response.redirect);
                return;
            }
            if (response.success) {
                setLocation(this.successUrl);
            } else if ("is_hosted_pro" in response && response.is_hosted_pro) { //3D Secure
                this.popup.showPopupWithDescription(response.update_section.html);
                this.popup.buttons.close.enabled = false;
                var iframe = this.popup.contentContainer.select('#hss-iframe').first();
                this.popup.container.setStyle({
                    top: 10 + 'px'
                });
                this.popup.contentContainer.setStyle({
                    height: 'auto'
                });
                new MagegiantOneStepCheckoutPopup({
                    selector: '',
                    tinyBoxConfig: {
                        html: response.update_section.html
                    }
                });
                iframe.observe('load', function () {
                    $('hss-iframe').show();
                    $('iframe-warning').show();
                });
            }
            else if ("is_centinel" in response && response.is_centinel) {
                new MagegiantOneStepCheckoutPopup({
                    selector: '',
                    tinyBoxConfig: {
                        html: response.update_section.html
                    }
                });
                var iframe = this.popup.contentContainer.select('#hss-iframe').first();
                iframe.observe('load', function () {
                    $('centinel_authenticate_iframe').show();
                    $('iframe-warning').show();
                });
            }
            else {
                var msg = response.messages;
                if (typeof(msg) == 'object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                }
            }
            this.enablePlaceOrderButton();
            this.hidePleaseWait();
            MagegiantOneStepCheckout.removeOverlay(document.body, this.overlayClassName);
        }
    },
    showPleaseWait: function () {
        this.pleaseWaitNotice.show();
        new Effect.Morph(this.pleaseWaitNotice, {
            style: {
                'marginTop': '10px'
            },
            'duration': 0.2
        });
    },
    hidePleaseWait: function () {
        this.pleaseWaitNotice.hide();
    },
    disablePlaceOrderButton: function () {
        this.placeOrderButton.addClassName(this.disabledClassName);
        this.placeOrderButton.disabled = true;
    },

    enablePlaceOrderButton: function () {
        this.placeOrderButton.removeClassName(this.disabledClassName);
        this.placeOrderButton.disabled = false;
    },
    validate: function () {
        var result = this.checkoutForm.validator.validate();
        var formData = Form.serialize(this.checkoutForm.form, true);
        var isValidatedShipping = this.validateShippingMethod(formData);
        var isValidatedPayment = this.validatePaymentMethod(formData);
        var shippingMethodWrapper = $$(this.shippingMethodWrapper).first();
        var paymentMethodWrapper = $$(this.paymentMethodWrapper).first();
        if (result) {
            if (!isValidatedShipping) {
                shippingMethodWrapper.scrollTo();
            }
            else if (!isValidatedPayment) {
                paymentMethodWrapper.scrollTo();
            }
        }
        return (result && isValidatedShipping && isValidatedPayment);
    },
    validateShippingMethod: function (formData) {
        var isValidated = true;
        var shippingMethodAdvice = $$(this.shippingMethodAdvice).first();
        var shippingMethodWrapper = $$(this.shippingMethodWrapper).first();
        if (shippingMethodAdvice && shippingMethodWrapper) {
            if (!formData[this.shippingMethodInput]) {
                isValidated = false;
                shippingMethodAdvice.update(this.shippingValidationMessage).show();
                shippingMethodWrapper.addClassName('validation-failed');
            } else {
                isValidated = true;
                shippingMethodAdvice.update('').hide();
                shippingMethodWrapper.removeClassName('validation-failed');
            }
        }
        return isValidated;
    },
    validatePaymentMethod: function (formData) {
        var isValidated = true;
        var paymentMethodAdvice = $$(this.paymentMethodAdvice).first();
        var paymentMethodWrapper = $$(this.paymentMethodWrapper).first();
        if (paymentMethodAdvice && paymentMethodWrapper) {
            if (!formData[this.paymentMethodInput]) {
                isValidated = false;
                paymentMethodAdvice.update(this.paymentValidationMessage).show();
                paymentMethodWrapper.addClassName('validation-failed');
            } else {
                isValidated = true;
                paymentMethodAdvice.update('').hide();
                paymentMethodWrapper.removeClassName('validation-failed');
            }
        }
        return isValidated;
    }
};

MagegiantOneStepCheckoutCompareWishlist = Class.create();
MagegiantOneStepCheckoutCompareWishlist.prototype = {
    initialize: function (config) {
        this.addToContainer = $$(config.addToContainer).first();
        this.addToCompareLinks = $$(config.addToCompareLinks);
        this.addToWishlistLinks = $$(config.addToWishlistLinks);
        this.productImageClass = config.productImageClass;
        this.addToCompareUrl = config.addToCompareUrl;
        this.addToWishlistUrl = config.addToWishlistUrl;
        this.initObserver();
    },
    initObserver: function () {
        this.addToCompareLinks.each(function (el) {
            el.observe('click', function (evt) {
                this._processCompareWishlist(evt, 'compare');
            }.bind(this))
        }.bind(this));
        this.addToWishlistLinks.each(function (el) {
            el.observe('click', function (evt) {
                this._processCompareWishlist(evt, 'wishlist');
            }.bind(this))
        }.bind(this));
    },
    _processCompareWishlist: function (evt, mode) {
        if (mode == 'compare') {
            var productId = evt.target.id.split('product-compare-')[1];
            var url = this.addToCompareUrl;
        }
        else {
            var productId = evt.target.id.split('product-wishlist-')[1];
            var url = this.addToWishlistUrl;
        }
        var requestOption = {
            method: 'post',
            parameters: {
                product: productId
            },
            onComplete: function (transport) {
                try {
                    var response = transport.responseText.evalJSON();
                    if (response.success) {
                        evt.target.up().update(response.message);
                    }
                    else {
                        evt.target.up().update(response.error);
                    }
                } catch (e) {
                }
            }
        };
        MagegiantOneStepCheckout.Request(url, requestOption);
    }
}


