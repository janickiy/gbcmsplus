$(function ()
{
  var $referralGridForm = $('#referral-grid-form'),
      pjaxSelector = '#referrals-grid';

  $referralGridForm.on('change', 'select', function(e) {
    $referralGridForm.submit();
  });

  var dp_mobile_start = $('#m_referralincomesearch-date_from');
  var dp_mobile_end = $('#m_referralincomesearch-date_to');

  setDpDate('referralincomesearch-date_from', true);
  setDpDate('referralincomesearch-date_to', true);

  $(document).on('change.dp', '#dp_mobile input', function(e) {
    setDpDate(e.target.id, false);
  });

  $(".change_date-period input").change(function () {
    var start = $(this).data('start');
    var end = $(this).data('end');

    if(start !== undefined && end !== undefined) {
      $('.dp_container').hide();
      $("#referralincomesearch-date_from").kvDatepicker("setDate", start + ""),
        $("#referralincomesearch-date_to").kvDatepicker("setDate", end + "");
    } else {
      $('.dp_container').show();
    }
  });




  $referralGridForm.on('submit', function(e) {
    $.pjax.submit(e, pjaxSelector);
  });

  new Clipboard('.copy-button');
});