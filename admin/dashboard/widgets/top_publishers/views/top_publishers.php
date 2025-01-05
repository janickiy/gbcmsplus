<?php
/**
 * @var array $data
 */

use admin\assets\WidgetAsset;

$result = json_encode($data['result']);
$caption = $data['caption'];
$currencySymbol = $data['currencySymbol'];

WidgetAsset::register($this);
$js = <<< JS
  var publishersElement = document.getElementById('publishers');

	function generatePublishersConfig(data, caption, currencySymbol) {
		var labels = [];
		var values = [];
		var backgroundColors = [];
		if (data.length > 0) {
		  data.forEach(function (item) {
        labels.push(item.label);
        values.push(item.value);
        backgroundColors.push(item.color);
      });
		}

		return {
			maintainAspectRatio: true,
			caption: caption,
			currencySymbol: currencySymbol,
			labels: labels,
			datasets: [{
				data: values,
				backgroundColor: backgroundColors,
			}]
		}
	}

	var publishersConfig = {
		type: 'doughnut',
		data: generatePublishersConfig([]),
		options: {
			legend: legend_config(getMatchMedia(isSmallDesktop) ? 'bottom' : 'right'),
			tooltips: tooltip_config('nearest'),
		}
	};
	// Добавляем парамерт align для центрирования по вертикали
	publishersConfig.options.legend.align = 'center';

	var publishersChart = new Chart(publishersElement, publishersConfig);

	$(window).on('resize', function () {
		publishersChart.config.options.legend.position = getMatchMedia(isSmallDesktop) ? 'bottom' : 'right';
		publishersChart.config.options.legend.labels.fontSize = parseInt(fontSize);
		publishersChart.update();
	});
	
	DashboardRequest.addWidget({
    name: 'top_publishers',
    filterSelector: 'input[name="publisher-type"]:checked',
    events: ['dashboard:filter'],
    success: function(data) {
      publishersChart.data = generatePublishersConfig(data.result, data.caption, data.currencySymbol);
      publishersChart.update();
    }
  });
  
  publishersChart.data = generatePublishersConfig($result, '$caption', '$currencySymbol');
  publishersChart.update();
JS;
$this->registerJs($js, $this::POS_LOAD);
?>

<canvas id="publishers"></canvas>
