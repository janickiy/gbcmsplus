<?php
return [
  'main' => 'Payout & Commission',
  'attribute-id' => 'ID',
  'attribute-user_id' => 'User',
  'attribute-operator_id' => 'Operator',
  'attribute-landing_id' => 'Landing',
  'attribute-provider_id' => 'Provider',
  'attribute-country_id' => 'Country',
  'attribute-rebill_percent' => 'Rebill percent',
  'attribute-buyout_percent' => 'Buyout percent',
  'attribute-landing-category' => 'Landing Category',
  'attribute-cpa_profit' => 'CPA profit (in partners currency)',
  'attribute-cpa_profit_rub' => 'CPA profit (rub)',
  'attribute-cpa_profit_eur' => 'CPA profit (eur)',
  'attribute-cpa_profit_usd' => 'CPA profit (usd)',
  'user_id_required_error' => 'if buyout profit is specified you must choose user',
  'modal-head' => 'Profit percents settings',
  'conditions-hint' => 'Specify conditions of appliance these profit percents',
  'create' => 'Add condition',
  'replacements-id' => 'Personal profit percent ID',
  'replacements-user' => 'Personal profit percent user',
  'replacements-operator' => 'Personal profit percent operator',
  'replacements-landing' => 'Personal profit percent landing',
  'replacements-createdBy' => 'Personal profit percent created by',
  'replacements-rebillPercent' => 'Rebill personal percent',
  'replacements-buyoutPercent' => 'Buyout personal percent',
  'replacements-cpaProfitRub' => 'CPA profit (rub)',
  'replacements-cpaProfitEur' => 'CPA profit (eur)',
  'replacements-cpaProfitUsd' => 'CPA profit (usd)',
  'unique_validate_fail' => 'This combination of conditions is already exists.
    Existing combination must be edited instead of creating new.',
  'at_least_one_required_validate_fail' => 'At least one field is arbitrary',
  'deny_edit_own' => 'You can not change this attribute by yourself',
  'partner_program' => 'Pay terms group',
  'actualize-courses' => 'Calculate the new rates',
  'actualize-courses-confirm' => 'Recalculate the "CPA profit" fields at the new exchange rate?',
];