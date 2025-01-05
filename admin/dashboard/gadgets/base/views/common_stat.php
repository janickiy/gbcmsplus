<?php
/**
 * @var $value
 * @var $values
 * @var $title
 * @var $id
 */

$values = '[' . implode(',', $values) . ']';

use admin\assets\SparklineAsset;
use admin\dashboard\gadgets\active_partners\GadgetActivePartners;
use admin\dashboard\gadgets\gross_revenue\GadgetGrossRevenue;
use admin\dashboard\gadgets\net_revenue\GadgetNetRevenue;

$activePartnersClassName = GadgetActivePartners::class;
$netRevenueClassName = GadgetNetRevenue::class;
$grossRevenueClassName = GadgetGrossRevenue::class;

SparklineAsset::register($this);
$type = 'bar';
if ($updatable) {
    $type = 'line';
    $this->registerJs(<<<JS
  
  var thousandSeparator = function(str) {
    var parts = (str + '').split('.'),
        main = parts[0],
        len = main.length,
        output = '',
        i = len - 1;
    
    while(i >= 0) {
        output = main.charAt(i) + output;
        if ((len - i) % 3 === 0 && i > 0) {
            output = ' ' + output;
        }
        --i;
    }

    if (parts.length > 1) {
        output += '.' + parts[1];
    }
    return output;
  };
  
  (function() {
    var success = function(data) {
      var sum = data[data.length - 1];
      if ('$className' != '$activePartnersClassName') {
        sum = 0;
        for(var i = 0; i < data.length; i++){
          sum += parseFloat(data[i]);
        }
        if ('$className' == '$netRevenueClassName' || '$className' == '$grossRevenueClassName') {
          sum = thousandSeparator(sum.toFixed(2));
        } else {
          sum = thousandSeparator(parseInt(sum));
        }
      }
      
      $('#$id .common_stats__item_value .gadget-value').html(sum);
      
      var sl = $('#$id .sparkline'),
          sparklineHeight = sl.data('sparkline-height') || '20px';
          sparklineWidth = sl.data('sparkline-width') || '50px';
          thisLineColor = sl.data('sparkline-line-color') || sl.css('color') || '#272727';
          thisLineWidth = sl.data('sparkline-line-width') || 1;
          thisFill = sl.data('fill-color') || 'transparent';
          thisSpotColor = sl.data('sparkline-spot-color') || 'transparent';
          thisMinSpotColor = sl.data('sparkline-minspot-color') || 'transparent';
          thisMaxSpotColor = sl.data('sparkline-maxspot-color') || 'transparent';
          thishighlightSpotColor = sl.data('sparkline-highlightspot-color') || 'black';
          thisHighlightLineColor = sl.data('sparkline-highlightline-color') || null;
          thisSpotRadius = sl.data('sparkline-spotradius') || 1.5;
          thisChartMinYRange = sl.data('sparkline-min-y') || 'undefined'; 
          thisChartMaxYRange = sl.data('sparkline-max-y') || 'undefined'; 
          thisChartMinXRange = sl.data('sparkline-min-x') || 'undefined'; 
          thisChartMaxXRange = sl.data('sparkline-max-x') || 'undefined'; 
          thisMinNormValue = sl.data('min-val') || 'undefined'; 
          thisMaxNormValue = sl.data('max-val') || 'undefined'; 
          thisNormColor =  sl.data('norm-color') || '#c0c0c0';
          thisDrawNormalOnTop = sl.data('draw-normal') || false;
          
      sl.sparkline(data, {
        type : '$type',
        width: sparklineWidth,
        lineColor: thisLineColor,
        fillColor: thisFill,
        spotColor: thisSpotColor,
        minSpotColor: thisMinSpotColor,
        maxSpotColor: thisMaxSpotColor,
        highlightLineColor: thisHighlightLineColor,
        highlightSpotColor: thishighlightSpotColor,
        numberFormatter: function(val) {
          return parseFloat(val).toLocaleString('ru-RU').replace(',', '.');
        }
      });
    };
    
  DashboardRequest.addGadget({
    name: '$name',
    events: ['dashboard:filter'],
    success: success
  });
  })();
JS
    );
}

?>

<div id="<?= $id ?>" class="common_stats__item">
    <div class="common_stats__item__inner">
        <div class="common_stats__item_value">
            <?= $value ?>
        </div>
        <div class="common_stats__item_chart">
            <div
                    class="sparkline"
                    data-sparkline="<?= $values ?>"
                    data-sparkline-type="<?= $type ?>"
                    data-sparkline-format-integer="true"
                    data-sparkline-currency-symbol="<?= $currencySymbol ?>"
                    data-sparkline-chart-range-min="0"
            >
            </div>
        </div>
        <div class="common_stats__item_label"><?= $title ?></div>
    </div>
</div>
