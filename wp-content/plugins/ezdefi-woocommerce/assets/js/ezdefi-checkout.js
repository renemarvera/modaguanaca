jQuery(function($) {
  'use strict';

  var selectors = {
    container: '#wc-wzdefi-checkout',
    input: '#wc-ezdefi-coin',
    item: '.currency-item',
    itemWrap: '.currency-item__wrap'
  };

  var wc_ezdefi_checkout = function() {
    this.$container = $(selectors.container);

    var onSelectItem = this.onSelectItem.bind(this);

    $(document.body).on('click', selectors.itemWrap, onSelectItem);
  };

  wc_ezdefi_checkout.prototype.onSelectItem = function(e) {
    $(selectors.item).removeClass('selected');

    var target = $(e.target);
    var selected;

    if (target.is(selectors.itemWrap)) {
      selected = target.find(selectors.item);
    } else {
      selected = target.closest(selectors.itemWrap).find(selectors.item);
    }

    selected.addClass('selected');

    var coinId = selected.attr('data-id');

    if (!coinId || coinId.length === 0) {
      return false;
    }

    $(selectors.input).val(coinId);
  };

  new wc_ezdefi_checkout();
});
