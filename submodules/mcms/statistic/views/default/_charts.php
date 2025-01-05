<?php

use admin\dashboard\widgets\statistic\assets\MorrisChartAsset;
use mcms\common\widget\alert\Alert;

/**
 * @var array $data
 * @var $this \yii\web\View
 * @var boolean $forceChangeGroup признак того, что группировка принудительно изменена
 */
MorrisChartAsset::register($this);

$js = <<<JS
var selectPicker = $('#chart-select');
var chartSelectData = $chartSelectData;
var columnsCookieKey = 'statistic_graph_columns';
var uncheckedColumnsCookieKey = 'statistic_graph_unchecked_columns';
var chartCookieData = Cookies.getJSON(columnsCookieKey);
var uncheckedCookieData = Cookies.getJSON(uncheckedColumnsCookieKey) || [];
var selectVals = [];
var cookieVals = [];
selectPicker.find('option').addClass('hidden');
for (var item in chartSelectData) {
  var value = chartSelectData[item];
  selectPicker.find('option[value="' + value + '"]').removeClass('hidden');
  if (chartCookieData.indexOf(value) !== -1) {
    selectVals.push(value);
  } else {
    if (uncheckedCookieData.indexOf(value) === -1) {
      cookieVals.push(value);
    }
  }
}
Cookies.set(columnsCookieKey, chartCookieData.concat(cookieVals), {expires: 1});
selectPicker.selectpicker('val', selectVals);
selectPicker.selectpicker('refresh');

JS;

if ($model->isGroupingBy('date_hour')) {
  $js .= <<< JS
  var period = {
      xLabels:'hour',
      xLabelFormat: function(d) {
        return d.getHours() + ':00';
      },
      dateFormat: function(d) {
        var date = new Date(d);
        var day = parseInt(date.getDate());
        if (day < 10) {
          day = '0' + day;
        }
        var month = parseInt(date.getMonth()) + 1;
        if (month < 10) {
          month = '0' + month;
        }
        var year = date.getFullYear();
        
        return day + '.' + month + '.' + year + ' ' + date.getHours() + ':00';
      }
    };
JS;
} else {
  $js .= <<< JS
  var period = {
      xLabels:'day',
      xLabelFormat: function(d) {
        return d.getDate()+'/'+(d.getMonth()+1);
      },
      dateFormat: function(d) {
        var date = new Date(d);
        var day = parseInt(date.getDate());
        if (day < 10) {
          day = '0' + day;
        }
        var month = parseInt(date.getMonth()) + 1;
        if (month < 10) {
          month = '0' + month;
        }
        var year = date.getFullYear();
        
        return day + '.' + month + '.' + year;
      }
    };
JS;
}
if ($forceChangeGroup) {
  $alert = Alert::warning(Yii::_t('statistic.statistic.big_span_date_hour_group'));
  $js .= <<< JS
  $('#statistic-group').val('date');
  $alert;
JS;
}

$js .= <<< JS
  var quantityData = $quantityData,
      quantityKeys = $quantityKeys,
      financeData = $financeData,
      financeKeys = $financeKeys;
  
  var getShowData = function(data, charts) {
    if (!charts) {
      if (charts = Cookies.getJSON(columnsCookieKey)) {
        return getShowData(data, charts);
      }
      return data;
    } else {
      Cookies.set(columnsCookieKey, charts, {expires: 1});
    }
    
    var tmpData = JSON.parse(JSON.stringify(data));
    for(var i in tmpData) {
      for(var key in data[i]) {
        if (key === 'period') continue;
        if (charts.indexOf(key) === -1) {
          delete tmpData[i][key];
        } else {
          tmpData[i][key] = data[i][key]
        }
      }
    }
    $('#chart-select').selectpicker('val', charts);
    return tmpData;
  };
  
  if ($('#quantity-graph').length && quantityData.length > 0 && quantityKeys.length > 0) {
    var dataQuantity = {
      element : 'quantity-graph',
      data : getShowData(quantityData),
      xkey : 'period',
      ykeys : $quantityKeys,
      labels : $quantityLabels,
      goals: [1.0, 10.0],
      continuousLine: true
    };
    var quantityChart = Morris.Line($.extend(dataQuantity, period));
  }
  
  if ($('#finance-graph').length && financeData.length > 0 && financeKeys.length > 0) {
    var dataFinance = {
      element : 'finance-graph',
      data : getShowData(financeData),
      xkey : 'period',
      ykeys : $financeKeys,
      labels : $financeLabels,
      goals: [1.0, 10.0],
      continuousLine: true
    };
    var financeChart = Morris.Line($.extend(dataFinance, period));
  }
JS;

$this->registerJs($js);
?>

<div class="chart-block">
  <h5><?= Yii::_t('statistic.statistic.quantity_charts') ?></h5>
  <div id="quantity-graph" class="chart-medium" style="height: 480px;"></div>
</div>
<div class="chart-block">
  <h5><?= Yii::_t('statistic.statistic.finance_charts') ?></h5>
  <div id="finance-graph" class="chart-medium" style="height: 480px;"></div>
</div>