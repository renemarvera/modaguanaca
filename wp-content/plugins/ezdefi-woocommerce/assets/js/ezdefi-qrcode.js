jQuery(function($) {
  const selectors = {
    container: '#wc_ezdefi_qrcode',
    changeBtn: '.changeBtn',
    select: '.currency-select',
    itemWrap: '.currency-item__wrap',
    item: '.currency-item',
    selected: '.selected-currency',
    paymentData: '#payment-data',
    submitBtn: '.submitBtn',
    ezdefiPayment: '.ezdefi-payment',
    tabs: '.ezdefi-payment-tabs',
    panel: '.ezdefi-payment-panel',
    ezdefiEnableBtn: '.ezdefiEnableBtn',
    loader: '.wc-ezdefi-loader',
    copy: '.copy-to-clipboard',
    qrcode: '.qrcode',
    changeQrcodeBtn: '.changeQrcodeBtn'
  };

  var wc_ezdefi_qrcode = function() {
    this.$container = $(selectors.container);
    this.$loader = this.$container.find(selectors.loader);
    this.$tabs = this.$container.find(selectors.tabs);
    this.$currencySelect = this.$container.find(selectors.select);
    this.paymentData = JSON.parse(this.$container.find(selectors.paymentData).text());
    this.xhrPool = [];
    this.checkOrderLoop;

    var init = this.init.bind(this);
    var onSelectItem = this.onSelectItem.bind(this);
    var onClickEzdefiLink = this.onClickEzdefiLink.bind(this);
    var onUseAltQrcode = this.onUseAltQrcode.bind(this);
    var onClickQrcode = this.onClickQrcode.bind(this);

    init();

    $(document.body)
      .on('click', selectors.item, onSelectItem)
      .on('click', selectors.ezdefiEnableBtn, onClickEzdefiLink)
      .on('click', selectors.qrcode, onClickQrcode)
      .on('click', selectors.changeQrcodeBtn, onUseAltQrcode);
  };

  wc_ezdefi_qrcode.prototype.init = function() {
    var self = this;

    self.$tabs.tabs({
      activate: function(event, ui) {
        if (!ui.newPanel || ui.newPanel.is(':empty')) {
          self.createEzdefiPayment.call(self, ui.newPanel);
        }
        window.history.replaceState(null, null, ui.newPanel.selector);
      }
    });

    this.createEzdefiPayment.call(this);

    this.initClipboard.call(this);
  };

  wc_ezdefi_qrcode.prototype.initClipboard = function() {
    new ClipboardJS(selectors.copy).on('success', function(e) {
      var trigger = $(e.trigger)[0];
      trigger.classList.add('copied');
      setTimeout(function() {
        trigger.classList.remove('copied');
      }, 2000);
    });
  };

  wc_ezdefi_qrcode.prototype.onClickQrcode = function(e) {
    var target = $(e.target);
    if (!target.hasClass('expired')) {
      return;
    } else {
      e.preventDefault();
      this.$currencySelect.find('.selected').click();
    }
  };

  wc_ezdefi_qrcode.prototype.createEzdefiPayment = function(panel = null) {
    var self = this;
    var active = panel ? panel : this.findActiveTab.call(this);
    var method = active.attr('id');
    var selectedCoin = this.$currencySelect.find('.selected');
    var coin_id = selectedCoin.attr('data-id');
    $.ajax({
      url: wc_ezdefi_data.ajax_url,
      method: 'post',
      data: {
        action: 'wc_ezdefi_create_payment',
        uoid: self.paymentData.uoid,
        coin_id: coin_id,
        method: method
      },
      beforeSend: function() {
        self.$tabs.hide();
        self.$currencySelect.hide();
        self.$loader.show();
      },
      success: function(response) {
        var html = response.success ? $(response.data) : response.data;
        active.html(html);
        self.setTimeRemaining.call(self, active);
        self.$loader.hide();
        self.$tabs.show();
        self.$currencySelect.show();
        self.checkOrderStatus.call(self);
      }
    });
  };

  wc_ezdefi_qrcode.prototype.onSelectItem = function(e) {
    var selected = $(e.currentTarget);
    this.$currencySelect.find(selectors.item).removeClass('selected');
    selected.addClass('selected');
    this.$tabs.find(selectors.panel).empty();
    this.createEzdefiPayment.call(this);
  };

  wc_ezdefi_qrcode.prototype.onClickEzdefiLink = function(e) {
    e.preventDefault();
    this.$tabs.tabs('option', 'active', 1);
  };

  wc_ezdefi_qrcode.prototype.onUseAltQrcode = function(e) {
    e.preventDefault();
    this.$tabs.find('#amount_id .qrcode img.main').toggle();
    this.$tabs.find('#amount_id .qrcode__info--main').toggle();
    this.$tabs.find('#amount_id .qrcode img.alt').toggle();
    this.$tabs.find('#amount_id .qrcode__info--alt').toggle();
  };

  wc_ezdefi_qrcode.prototype.checkOrderStatus = function() {
    var self = this;

    $.ajax({
      url: wc_ezdefi_data.ajax_url,
      method: 'post',
      data: {
        action: 'wc_ezdefi_check_order_status',
        order_id: self.paymentData.uoid
      }
    }).done(function(response) {
      if (response == wc_ezdefi_data.order_status ) {
        self.success();
      } else {
        var checkOrderStatus = self.checkOrderStatus.bind(self);
        setTimeout(checkOrderStatus, 600);
      }
    });
  };

  wc_ezdefi_qrcode.prototype.setTimeRemaining = function(panel) {
    var self = this;
    var timeLoop = setInterval(function() {
      var endTime = panel.find('.count-down').attr('data-endtime');
      var t = self.getTimeRemaining(endTime);
      var countDown = panel.find(selectors.ezdefiPayment).find('.count-down');

      if (t.total < 0) {
        clearInterval(timeLoop);
        countDown.text('0:0');
        self.timeout(panel);
      } else {
        countDown.text(t.text);
      }
    }, 1000);
  };

  wc_ezdefi_qrcode.prototype.getTimeRemaining = function(endTime) {
    var t = new Date(endTime).getTime() - new Date().getTime();
    var minutes = Math.floor(t / 60000);
    var seconds = ((t % 60000) / 1000).toFixed(0);
    return {
      total: t,
      text:
        seconds == 60 ? minutes + 1 + ':00' : minutes + ':' + (seconds < 10 ? '0' : '') + seconds
    };
  };

  wc_ezdefi_qrcode.prototype.success = function() {
    location.reload(true);
  };

  wc_ezdefi_qrcode.prototype.timeout = function(panel) {
    panel.find('.qrcode').addClass('expired');
  };

  wc_ezdefi_qrcode.prototype.findActiveTab = function() {
    return this.$tabs.find('div.ui-tabs-panel[aria-hidden="false"]');
  };

  new wc_ezdefi_qrcode();
});
