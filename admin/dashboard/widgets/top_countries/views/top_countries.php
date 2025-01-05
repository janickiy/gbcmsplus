<?php
/**
 * @var array $data
 */

use admin\assets\WidgetAsset;

$result = json_encode($data['result']);
$tooltip = $data['tooltip'];
$isDataEmpty = json_encode($data['isDataEmpty']);
$currencySymbol = $data['currencySymbol'];

WidgetAsset::register($this);
$js = <<< JS
  function generateTopCountriesConfig(data, tooltip, currencySymbol) {
		var labels = [];
		var dataSets = [];
		
		if (data.length > 0)
    {
      data.forEach(function (obj) {
        dataSets.push(obj.value);
        labels.push(obj.label);
      });
    }

		return {
			labels: labels,
			currencySymbol: currencySymbol,
			datasets: [
				{
					label: tooltip,
					backgroundColor: "#E90D46",
					borderColor: "#E90D46",
					pointBackgroundColor: "#E90D46",
					pointBorderColor: "#fff",
					pointHoverBackgroundColor: "#fff",
					pointHoverBorderColor: "rgba(179,181,198,1)",
					data: dataSets
				}
			]
		}
	}

	var topCountriesChartOptions = {
    maintainAspectRatio: false,
    legend : {
      display : false
    },
    tooltips: tooltip_config('nearest'),
    scale: {
      display: $isDataEmpty ? false : true,
      ticks: {
        beginAtZero: true
      }
    }
  };
  if ($result.length === 1) {
    topCountriesChartOptions.scale.ticks.maxTicksLimit = 1;
  }

	var countriesElement = document.getElementById('top_countries');
	var topCountriesChart = new Chart(countriesElement, {
		type: 'radar',
		fill : false,
		data: generateTopCountriesConfig([]),
		options: topCountriesChartOptions
	});

	$(window).on('resize', function (event) {
		topCountriesChart.config.options.legend.labels.fontSize = parseInt(fontSize);
		topCountriesChart.update();
	});

	DashboardRequest.addWidget({
    name: 'top_countries',
    filterSelector: 'input[name="countries-type"]:checked',
    events: ['dashboard:filter'],
    success: function(data) {
      topCountriesChart.data = generateTopCountriesConfig(data.result, data.tooltip, data.currencySymbol);
      topCountriesChart.options.scale.display = !data.isDataEmpty;
      if (data.result.length === 1) {
        topCountriesChart.options.scale.ticks.maxTicksLimit = 1;
      }
      (data.result.length > 1) ? $('.top_countries .statbox__body').show() : $('.top_countries .statbox__body').hide();
      
      topCountriesChart.update();
      topCountriesChart.resize();
    }
  });
  
  if ($hideWidget) {
    $('.top_countries .statbox__body').hide();
  }
  
  topCountriesChart.data = generateTopCountriesConfig($result, '$tooltip', '$currencySymbol');
  topCountriesChart.update();
JS;
$this->registerJs($js, $this::POS_LOAD);
?>

<canvas id="top_countries"></canvas>
