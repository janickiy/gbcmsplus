<?php
return [
  'reseller_profit' => 'Reseller settlements',
  'reseller_settlement_statistic' => 'Statistics of settlements reseller',
  'reseller' => 'Reseller',
  'partners' => 'Partners',
  'hold_title' => 'Hold (press to display the plan of unhold)',
  'by_days' => 'By days',
  'by_weeks' => 'By weeks',
  'by_months' => 'By months',
  'apply_filter' => 'Apply filter',
  'total_turnover' => 'Reseller TURN',
  'paid' => 'Paid by RGK',
  'awaiting' => 'Sent to RGK',
  'debt' => 'Profit (hold)',
  'total_debt' => 'Balance (hold)',
  'unhold_plan' => 'Unhold plan',
  'total_in_hold' => 'Total in hold',
  'total' => 'Total',
  'awaiting_payments' => 'Sent to RGK',
  'unholded' => 'Unholded',
  'penalties' => 'Penalties',
  'compensations' => 'Compensations',
  'credits' => 'Credits',
  'credit_charges' => 'Credits charges',
  'day' => 'Day',
  'week' => 'Week',
  'month' => 'Month',
  'attribute-week_start_date' => 'Week start date',
  'attribute-profit_rub' => 'Profit RUB',
  'attribute-profit_eur' => 'Profit EUR',
  'attribute-profit_usd' => 'Profit USD',
  'attribute-created_at' => 'Date create',
  'attribute-updated_at' => 'Date update',
  'attribute-currency' => 'Currency',
  'attribute-profit' => 'Profit',
  'hold_rules' => 'Hold rules',
  'hold_rules_country_id' => 'Country ID',
  'hold_rules_country_name' => 'Country name',
  'hold_stack_size' => 'Hold stack size',
  'hold_stack_size_description_1' => '{value, plural, =0{# days} =1{# day} one{# day} few{# days} many{# days} other{# days}}',
  'hold_stack_size_description_2' => '{value, plural, =0{# weeks} =1{# week} one{# week} few{# weeks} many{# weeks} other{# weeks}}',
  'hold_stack_size_description_3' => '{value, plural, =0{# months} =1{# month} one{# month} few{# months} many{# months} other{# months}}',
  'min_hold' => 'Minimum hold',
  'min_hold_description_1' => '{value, plural, =0{# days} =1{# day} one{# day} few{# days} many{# days} other{# days}}',
  'min_hold_description_2' => '{value, plural, =0{# weeks} =1{# week} one{# week} few{# weeks} many{# weeks} other{# weeks}}',
  'min_hold_description_3' => '{value, plural, =0{# months} =1{# month} one{# month} few{# months} many{# months} other{# months}}',
  'at_day_1' => 'Unhold at every {value, plural, =1{1st} =2{2nd} =3{3rd} other{#th}} day of the week',
  'at_day_2' => 'Unhold at every {value, plural, =1{1st} =2{2nd} =3{3rd} other{#th}} day of the month',
  'hold_rules_hint' => '<p>
      Each country may have it\'s own hold rules.
      That is, the profit which is received today, reseller can withdraw only after a certain time.
    </p>
    <h5>Case #1.
      Brasil profits unholding by stack size 1 week, holds it for 2 week minimum and unholds at every
      Thursday.
    </h5>
    <ul><li><strong>Hold Stack Size:</strong> 1 week</li>
      <li><strong>Hold minimum:</strong> 2 week</li>
      <li><strong>Unhold at:</strong> 4th day of the week</li>
    </ul><h5>Case #2.
      Kazakhstan profits unholding by stack size 1 month, and unholds at every
      5th day of the month.
    </h5>
    <ul><li><strong>Hold Stack Size:</strong> 1 month</li>
      <li><strong>Hold minimum:</strong> 0</li>
      <li><strong>Unhold at:</strong> 5th day of the month</li>
    </ul>',
  'unavailable' => 'Sorry. Service is unavailable. Please contact administrator.',
  'convert_increase' => 'Convert increase',
  'convert_decrease' => 'Convert decrease',
];