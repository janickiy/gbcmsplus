$(function() {
  /*
   *
   * Общее
   *
   */
  var $stepsWrap = $('.steps_wrap'),
    $landingModal = $('#linkLandingModal'),
    $requestModal = $('#linkRequestModal'),
    promoStep = new Steps(
      [
        {
          ButtonText: $stepsWrap.data('next'),
          beforeAction: function() {
            return true;
          },
          afterAction: function(indx) {
            resetModals();
          }
        },
        {
          ButtonText: $stepsWrap.data('next'),
          beforeAction: function() {
            return true;
          },
          afterAction: function(indx) {
            resetModals();
          }
        },
        {
          ButtonText: $stepsWrap.data('done'),
          beforeAction: function() {
            return false;
          },
          afterAction: function(indx) {
            $('.pre__list').scrollator({zIndex: '99', customClass: 'pre'});
            resetModals();
          }
        }
      ]
      ).init();
  window.promoStep = promoStep;

  function resetModals() {
    $landingModal.modal('hide');
    $requestModal.modal('hide');
  }
});

