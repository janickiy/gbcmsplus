<?php

use admin\assets\WidgetAsset;

$result = json_encode($data['result']);
$isDataEmpty = json_encode($data['isDataEmpty']);
$timezoneSecondsOffset = $data['timezoneSecondsOffset'];

WidgetAsset::register($this);
$js = <<< JS
  var subscriptionElement = document.getElementById('subscriptions');
	var gradient = subscriptionElement.getContext("2d").createLinearGradient(0, 0, 0, 400);
		gradient.addColorStop(0, 'rgba(247,134,138,1)');
		gradient.addColorStop(1, 'rgba(255,217,152,1)');

	function generateSubscribtionsConfig (data, timezoneSecondsOffset) {
		var datasets = [];
		var length = data.length;

		if (length > 0) {
		  data.forEach(function (obj, index) {
		    //Преобразование в JS дату
		    obj.data.forEach(function(point) {
		      var date = new Date();
		      var serverOffset = timezoneSecondsOffset*1000;
          var userOffset = date.getTimezoneOffset()*60000;
          var serverTime = new Date(point.x * 1000 + serverOffset + userOffset);
		      point.x = new Date(serverTime);
		    });
        var dataset = {};
        if (length === 1 || index !== length - 1) {
          if(index == 0) {
            var borderColor = '#000000';
          } else {
            var borderColor = '#EA0F46';
          }
          dataset = {
            label: obj.label,
            data: obj.data,
            borderColor : borderColor,
            borderWidth : 1,
            pointRadius : 3,
            pointStyle : "rect",
            backgroundColor : borderColor,
            fill : false,
            hidden: false,
            yAxisID: length === 1 ? "y-axis-1" : "y-axis-2"
           }
        } else {
          dataset = {
            label: obj.label,
            data: obj.data,
            borderColor : "#F67387",
            borderWidth : 1,
            radius : 0,
            backgroundColor: gradient,
            yAxisID: "y-axis-1"
           }
        }
        datasets.push(dataset);
      });
		}

		return datasets;
	}

	function getSubscribtionsChartOptions(data) {
    var yAxes = [{
        id: "y-axis-1",
        position: "left",
        display: true,
        ticks : {
          min : 0,
          beginAtZero: true,
          suggestedMax : 0,
          fontSize: fontSize,
          callback: function(tick) {
            return (tick < 1) ? Number(tick).toFixed(1) : tick.toFixed(1).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ');
          }
        },
        minSize : {
          height: 100
        }
      },
    ];
    if (data.length > 1) {
      yAxes.push({
        id: "y-axis-2",
        position: "right",
        display: true,
        ticks : {
          min : 0,
          beginAtZero: true,
          suggestedMax : 0,
          fontSize: fontSize,
          callback: function(tick) {
            return (tick < 1) ? Number(tick).toFixed(1) : tick.toFixed(1).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ');
          }
        }
      });
    }
	  var subscribtionsChartOptions = {
      stacked: true,
      legend: legend_config('bottom'),
      tooltips: tooltip_config('index'),
      defaultFontSize : 10,
      maintainAspectRatio: false,
  
      scales: {
        xAxes: [{
          type: 'time',
          ticks : {
            fontSize: fontSize
          },
          time: {
            format: "day",
            round: 'DD.YY',
            unit: "day",
            tooltipFormat: 'DD.MM.YYYY',
            displayFormats: {
               'day': 'DD.MM'
            },
          }
        }],
        yAxes: yAxes
      }
    };
    return subscribtionsChartOptions;
	}

  var subscribtionsChartOptions = getSubscribtionsChartOptions($result);
	
	var curRound = subscribtionsChartOptions.scales.xAxes[0].time.round;
  subscribtionsChartOptions.legend.onClick = function(e, legendItem) {
    defaultLegendClickHandler.call(this, e, legendItem);
    
    var ci = this.chart;
    var datasetHiddenVal = [];
    this.chart.data.datasets.forEach(function(dataset, index) {
      var meta = ci.getDatasetMeta(index);
      datasetHiddenVal.push(meta.hidden);
    });
    var isAllLinesHidden = datasetHiddenVal.every(function(item) {
      return item;
    });
    if (isAllLinesHidden) {
      if (ci.config.options.scales.xAxes[0]) {
        ci.config.options.scales.xAxes[0].time.round = null;
        ci.config.options.scales.xAxes[0].time.min = new Date();
        ci.config.options.scales.xAxes[0].time.max = new Date();
        ci.config.options.scales.yAxes[0].ticks.maxTicksLimit = 1;
      }
      if (ci.config.options.scales.xAxes[1]) {
        ci.config.options.scales.yAxes[1].ticks.maxTicksLimit = 1;
      }
    } else {
      if (ci.config.options.scales.xAxes[0]) {
        ci.config.options.scales.xAxes[0].time.round = curRound;
        ci.config.options.scales.xAxes[0].time.min = undefined;
        ci.config.options.scales.xAxes[0].time.max = undefined;
        ci.config.options.scales.yAxes[0].ticks.maxTicksLimit = undefined;
      }
      if (ci.config.options.scales.xAxes[1]) {
        ci.config.options.scales.yAxes[1].ticks.maxTicksLimit = undefined;
      }
    }
    ci.update();
  };
  if ($isDataEmpty) {
    if (subscribtionsChartOptions.scales.yAxes[0]) {
      subscribtionsChartOptions.scales.yAxes[0].ticks.maxTicksLimit = 1;
    }
    if (subscribtionsChartOptions.scales.yAxes[1]) {
      subscribtionsChartOptions.scales.yAxes[1].ticks.maxTicksLimit = 1;      
    }
  }
	var subscribtionsChart = new Chart(subscriptionElement, {
		type: 'line',
		data: {
			   datasets: generateSubscribtionsConfig([])
		},
		options: subscribtionsChartOptions
	});
	
	$(window).on('resize', function () {
		subscribtionsChart.config.options.scales.xAxes[0].ticks.fontSize = fontSize;
		subscribtionsChart.config.options.scales.yAxes[0].ticks.fontSize = fontSize;
		if (subscribtionsChart.config.options.scales.yAxes[1]) {
		  subscribtionsChart.config.options.scales.yAxes[1].ticks.fontSize = fontSize;
    }
		subscribtionsChart.config.options.legend.labels.fontSize = parseInt(fontSize) + 2;
		subscribtionsChart.resize();
	});

  DashboardRequest.addWidget({
    name: 'clicks_subscriptions',
    events: ['dashboard:filter', 'dashboard:forecast'],
    success: function(data) {
      subscribtionsChart.data.datasets = generateSubscribtionsConfig(data.result, data.timezoneSecondsOffset);
      if (data.isDataEmpty) {
        subscribtionsChart.options.scales.yAxes[0].ticks.maxTicksLimit = 1;
        if (subscribtionsChart.options.scales.yAxes[1]) {
          subscribtionsChart.options.scales.yAxes[1].ticks.maxTicksLimit = 1;
        }
      } else {
        subscribtionsChart.options.scales.yAxes[0].ticks.maxTicksLimit = undefined;
        if (subscribtionsChart.options.scales.yAxes[1]) {
          subscribtionsChart.options.scales.yAxes[1].ticks.maxTicksLimit = undefined;
        }
      }
      subscribtionsChart.update();
    }
  });
  
  subscribtionsChart.data.datasets = generateSubscribtionsConfig($result, $timezoneSecondsOffset);
  subscribtionsChart.update();
JS;

$this->registerJs($js, $this::POS_LOAD);
?>

<canvas id="subscriptions"></canvas>
