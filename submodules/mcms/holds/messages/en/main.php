<?php
return [
  'module_name' => 'Holds',
  'hold_rules' => 'Hold rules',
  'tabs_hold_rules' => 'Holds',
  'create' => 'Create rules group',
  'update' => 'Update rules group "{rule_name}"',
  'params' => 'Group',
  'add-partner' => 'Add partner',
  'add-rule' => 'Add rule',
  'update-rule' => 'Update rule',
  'partners' => 'Partners',
  'rules' => 'Rules',
  'rule_already_exists' => 'Rule already exists',
  'hold_program_name' => 'Name',
  'hold_program_description' => 'Description',
  'hold_program_is_default' => 'Is default',
  'rule-country_id' => 'Country',
  'rule-unhold_range' => 'Unhold range',
  'rule-unhold_range_type' => 'Unhold range type',
  'rule-min_hold_range' => 'Min hold range',
  'rule-min_hold_range_type' => 'Min hold range type',
  'rule-at_day' => 'At day',
  'rule-at_day_type' => 'At day type',
  'rule-key_date' => 'Key date',
  'hold_stack_size' => 'Hold stack size',
  'min_hold' => 'Minimum hold',
  'day_of_week' => 'day of the week',
  'day_of_month' => 'day of the month',
  'days' => 'day(s)',
  'weeks' => 'week(s)',
  'months' => 'month(s)',
  'at' => 'At',
  'optional' => 'optional',
  'add-rule-help-label' => 'Help',
  'add-rule-help' => '<p>
          Each country may have own hold rules. That is, the profit which is received by the partner today, he can withdraw only after a certain time.
        </p>
        <p>
          To hold a certain profit, one of the following rules will be applied
        </p>
        <ul>
          <li><i>High priority:</i> Country is specified</li>
          <li><i>Low priority:</i> Country is not specified (Countries = All)</li>
        </ul>
        <h3>Examples</h3>
        <h5>Case #1.
          Brasil profits unholding by stack size 1 week, holds it for 2 week minimum and unholds at every Thursday.
        </h5>
        <ul>
          <li><strong>Country:</strong> (BR) Brasil</li>
          <li><strong>Hold Stack Size:</strong> 1 week</li>
          <li><strong>Hold minimum:</strong> 2 week</li>
          <li><strong>Unhold at:</strong> 4th day of the week</li>
        </ul>
        <h5>Case #2.
          Kazakhstan profits unholding by stack size 1 month, and unholds at every 5th day of the month.
        </h5>
        <ul>
          <li><strong>Country:</strong> (KZ) Kazakhstan</li>
          <li><strong>Hold Stack Size:</strong> 1 month</li>
          <li><strong>Hold minimum:</strong> 0 days</li>
          <li><strong>Unhold at:</strong> 5th day of the month</li>
        </ul>',
  'is_default-hint' => 'Apply for partners without a selected group',
  'key_date-week_error' => 'Key date should be Monday',
  'key_date-month_error' => 'Key date should be first day of month',
];