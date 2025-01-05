$(function () {
  initAwaitingCollapse = function () {
    var $balanceItems = $('.current-balances__item');
    var balancesCookieKey = 'reseller_wallet_balances';
    var balanceItemsOneLine = $balanceItems.length <= 4;
    var $balanceItemsFirst = $balanceItems.slice(0, 4);
    var $balanceItemsLast = $balanceItems.slice(4);
    var $toggleIcon = $('.current-balances__toggle__icon');

    function toggleBalancesBlock() {
      var toHide = $balanceItemsLast.is(':visible');
      Cookies.set(balancesCookieKey, toHide ? 0 : 1, {expires: 1});
      if (toHide) {
        $balanceItemsLast.slideUp();
      } else {
        $balanceItemsLast.slideDown();
      }

      if ($balanceItemsLast.length > 0) {
        $balanceItemsFirst.toggleClass('last');
        $toggleIcon.toggleClass('caret').toggleClass('caret-right');
      }
    }

    if (Cookies.get(balancesCookieKey) == 1) {
      $balanceItemsLast.show();
      if (!balanceItemsOneLine) $toggleIcon.addClass('caret');
    } else {
      $balanceItemsLast.hide();
      if (!balanceItemsOneLine) $toggleIcon.addClass('caret-right');
      if ($balanceItemsLast.length > 0) {
        $balanceItemsFirst.addClass('last');
      }
    }

    if (balanceItemsOneLine) {
      $toggleIcon.closest('.current-balances__toggle').removeClass('current-balances__toggle');
    }

    $('.current-balances__toggle').on('click', function () {
      toggleBalancesBlock();
    });
  }
});