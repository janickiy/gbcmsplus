(function ($) {
  $.fn.personalProfitForm = function () {
    $(".personalprofit-user_id select").on('change', function() {
      $(this).closest('form').trigger('userSelected');
    });

    $('.personalProfitForm').on('userSelected', function() {
      $t = $(this)
      var userId = $t.find(".personalprofit-user_id").val();
      console.log(userId);
      if (!userId) return;
      var link = $(this).data('user-currency-link');
      $.get(link, {'userId' : userId}, function(result) {
        if (!result.success || !result.data) return;

        $t.find(".userPersonalProfitCurrency").html(result.data.name);
      });
    }).trigger('userSelected');
  };
})(window.jQuery);


