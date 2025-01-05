var formId = '#sourceForm';
var btnDataStepAttr = 'step';
var stepInputName = 'stepNumber';
var tabs = '.steps .nav-tabs';
var $codeInstallTab = $(tabs + ' a[href=#code-install]');
var $adsTypeTab = $(tabs + ' a[href=#ads-type]');
var $sourceIdInput = $('input#sourceId');

var $form = $(formId);

$(formId + ' button[data-' + btnDataStepAttr + ']').click(function(){
  var stepNumber = $(this).data(btnDataStepAttr);
  $form.find('input[name=' + stepInputName + ']').val(stepNumber);
});

$form.on('beforeSubmit', function () {
  var stepNumber = $form.find('input[name=' + stepInputName + ']').val();

  if (stepNumber == 1) {
    var data = $form.serializeArray();
    data.push({name: 'submit', value: true});
    $.ajax({
      url: $form.attr('action'),
      type: "POST",
      dataType: "json",
      data: data
    })
      .done(function (res) {
        $sourceIdInput.val(res.id);
        $('.hash').text(res.hash);
        $codeInstallTab.tab('show');
      });

  } else if (stepNumber == 2) {
    $adsTypeTab.tab('show');
  } else if (stepNumber == 3) {
    var data = $form.serializeArray();
    data.push({name: 'submit', value: true});
    $.ajax({
      url: $form.attr('action'),
      type: "POST",
      dataType: "json",
      data: data
    });
  }
  return false;
});