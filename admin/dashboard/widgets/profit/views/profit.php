<?php
/**
 * @var array $data
 */
use admin\assets\WidgetAsset;

$result = json_encode($data['result']);
$currencySymbol = $data['result']['currencySymbol'];
$isDataEmpty = json_encode($data['result']['isDataEmpty']);

WidgetAsset::register($this);
$js = <<< JS
  var CURRENCY_SYMBOL = '$currencySymbol';
  
  var profitElement = document.getElementById('profit');
  var profitChartOptions = {
    maintainAspectRatio: false,
    legend : legend_config('bottom'),
    tooltips: tooltip_config('index'),
    scales: {
      xAxes: [{
        stacked: true,
        ticks: {
          fontSize: fontSize
        }
      }],
      yAxes: [{
        stacked: true,
        ticks: {
          beginAtZero: true,
          suggestedMax : 2,
          callback: function(tick) {
            return tick.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ') + ' ' + CURRENCY_SYMBOL;
          },
          fontSize: fontSize
        },
      }]
    }
  };
  profitChartOptions.legend.onClick = function(e, legendItem) {
    defaultLegendClickHandler.call(this, e, legendItem);
    
    var ci = this.chart;
    var datasetHiddenVal = [];
    ci.data.datasets.forEach(function(dataset, index) {
      var meta = ci.getDatasetMeta(index);
      datasetHiddenVal.push(meta.hidden);
    });
    var isAllLinesHidden = datasetHiddenVal.every(function(item) {
      return item;
    });
    if (isAllLinesHidden && ci.config.options.scales.yAxes[0]) {
      ci.config.options.scales.yAxes[0].ticks.maxTicksLimit = 1;
      ci.config.options.scales.yAxes[0].ticks.suggestedMax = 0;
    } else {
      if (!ci.data.isDataEmpty && ci.config.options.scales.yAxes[0]) {
        ci.config.options.scales.yAxes[0].ticks.maxTicksLimit = undefined;
        ci.config.options.scales.yAxes[0].ticks.suggestedMax = 2;
      }
    }
    ci.update();
  };
  if ($isDataEmpty) {
    profitChartOptions.scales.yAxes[0].ticks.maxTicksLimit = 1;
    profitChartOptions.scales.yAxes[0].ticks.suggestedMax = 0;
  }
	var profitChart = new Chart(profitElement, {
		type: 'bar',
		data: [],
		options: profitChartOptions
	});

	$(window).on('resize', function () {
		profitChart.config.options.legend.labels.fontSize = parseInt(fontSize);
		profitChart.update();
	});
	
	DashboardRequest.addWidget({
    name: 'profit',
    events: ['dashboard:filter', 'dashboard:forecast'],
    success: function(data) {
      profitChart.data = data.result;
      if (data.result.isDataEmpty) {
        profitChart.options.scales.yAxes[0].ticks.maxTicksLimit = 1;
        profitChart.options.scales.yAxes[0].ticks.suggestedMax = 0;
      } else {
        profitChart.options.scales.yAxes[0].ticks.maxTicksLimit = undefined;
        profitChart.options.scales.yAxes[0].ticks.suggestedMax = 2;
      }
      profitChart.update();
    }
  });
  
  profitChart.data = $result;
  profitChart.update();
JS;
$this->registerJs($js, $this::POS_LOAD);
?>

<canvas id="profit"></canvas>
