jQuery(function($) {
  'use strict';

  var selectors = {
    apiKeyInput: '#woocommerce_ezdefi_api_key',
    publicKeyInput: '#woocommerce_ezdefi_public_key'
  };

  var wc_ezdefi_admin = function() {
    this.$form = $('form#mainform');

    this.$form.find(selectors.apiKeyInput).attr('autocomplete', 'off');

    this.initValidation.call(this);

    var onChangeApiKey = this.onChangeApiKey.bind(this);
    var onChangePublicKey = this.onChangePublicKey.bind(this);

    $(document.body)
      .on('change', selectors.apiKeyInput, onChangeApiKey)
      .on('change', selectors.publicKeyInput, onChangePublicKey);
  };

  wc_ezdefi_admin.prototype.initValidation = function() {
    var self = this;

    this.$form.validate({
      ignore: [],
      errorElement: 'span',
      errorClass: 'error',
      errorPlacement: function(error, element) {
        error.appendTo(element.closest('td'));
      },
      highlight: function(element) {
        $(element)
          .closest('td')
          .addClass('form-invalid');
      },
      unhighlight: function(element) {
        $(element)
          .closest('td')
          .removeClass('form-invalid');
      },
      rules: {
        woocommerce_ezdefi_title: {
          required: true
        },
        woocommerce_ezdefi_description: {
          required: true
        },
        woocommerce_ezdefi_api_url: {
          required: true,
          url: true
        },
        woocommerce_ezdefi_api_key: {
          required: true
        },
        woocommerce_ezdefi_public_key: {
          required: true
        }
      }
    });
  };

  wc_ezdefi_admin.prototype.onChangeApiKey = function(e) {
    var self = this;
    var $input = $(e.target);
    $input.rules('add', {
      remote: {
        url: wc_ezdefi_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wc_ezdefi_check_api_key',
          api_url: function() {
            return self.$form.find('#woocommerce_ezdefi_api_url').val();
          },
          api_key: function() {
            return self.$form.find('#woocommerce_ezdefi_api_key').val();
          }
        },
        complete: function(data) {
          var response = data.responseText;
          var $inputWrapper = self.$form.find('#woocommerce_ezdefi_api_key').closest('td');
          if (response === 'true') {
            $inputWrapper.append('<span class="correct">Correct</span>');
            window.setTimeout(function() {
              $inputWrapper.find('.correct').remove();
            }, 1000);
          }
        }
      },
      messages: {
        remote: 'API Key is not correct. Please check again'
      }
    });
  };

  wc_ezdefi_admin.prototype.onChangePublicKey = function(e) {
    var self = this;
    var $input = $(e.target);
    $input.rules('add', {
      remote: {
        url: wc_ezdefi_data.ajax_url,
        type: 'POST',
        data: {
          action: 'wc_ezdefi_check_public_key',
          api_url: function() {
            return self.$form.find('#woocommerce_ezdefi_api_url').val();
          },
          api_key: function() {
            return self.$form.find('#woocommerce_ezdefi_api_key').val();
          },
          public_key: function() {
            return self.$form.find('#woocommerce_ezdefi_public_key').val();
          }
        },
        complete: function(data) {
          var response = data.responseText;
          var $inputWrapper = self.$form.find('#woocommerce_ezdefi_public_key').closest('td');
          if (response === 'true') {
            $inputWrapper.append('<span class="correct">Correct</span>');
            window.setTimeout(function() {
              $inputWrapper.find('.correct').remove();
            }, 1000);
          }
        }
      },
      messages: {
        remote: 'Website ID is not correct. Please check again'
      }
    });
  };

  new wc_ezdefi_admin();
});
