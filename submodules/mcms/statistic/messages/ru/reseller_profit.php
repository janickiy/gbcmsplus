<?php
return [
  'reseller_profit' => 'Взаиморасчеты реселлера',
  'reseller_settlement_statistic' => 'Статистика взаиморасчетов реселлера',
  'reseller' => 'Реселлер',
  'partners' => 'Партнеры',
  'hold_title' => 'Холд (нажмите для отображения плана расхолда)',
  'by_days' => 'По дням',
  'by_weeks' => 'По неделям',
  'by_months' => 'По месяцам',
  'apply_filter' => 'Применить фильтр',
  'total_turnover' => 'Реселлер TURN',
  'paid' => 'Выплачено через RGK',
  'awaiting' => 'Отправлено в RGK',
  'debt' => 'Доход реселлера (холд)',
  'total_debt' => 'Баланс реселлера (холд)',
  'unhold_plan' => 'План расхолда средств',
  'total_in_hold' => 'Всего в холде',
  'total' => 'Итого',
  'awaiting_payments' => 'Отправлено в RGK',
  'unholded' => 'Расхолдилось',
  'penalties' => 'Штрафы',
  'compensations' => 'Компенсации',
  'credits' => 'Кредиты',
  'credit_charges' => 'Списания за кредиты',
  'day' => 'День',
  'week' => 'Неделя',
  'month' => 'Месяц',
  'attribute-week_start_date' => 'Дата начала недели',
  'attribute-profit_rub' => 'Профит RUB',
  'attribute-profit_eur' => 'Профит EUR',
  'attribute-profit_usd' => 'Профит USD',
  'attribute-created_at' => 'Дата создания',
  'attribute-updated_at' => 'Дата изменения',
  'attribute-currency' => 'Валюта',
  'attribute-profit' => 'Профит',
  'hold_rules' => 'Правила холда',
  'hold_rules_country_id' => 'ID страны',
  'hold_rules_country_name' => 'Наименование страны',
  'hold_stack_size' => 'Размер расхолда',
  'hold_stack_size_description_1' => '{value, plural, =0{# дней} =1{# день} one{# день} few{# дня} many{# дней} other{# дня}}',
  'hold_stack_size_description_2' => '{value, plural, =0{# недель} =1{# неделя} one{# неделя} few{# недели} many{# недель} other{# недель}}',
  'hold_stack_size_description_3' => '{value, plural, =0{# месяцев} =1{# месяц} one{# месяц} few{# месяца} many{# месяцев} other{# месяцев}}',
  'min_hold' => 'Минимум в холде',
  'min_hold_description_1' => '{value, plural, =0{# дней} =1{# день} one{# день} few{# дня} many{# дней} other{# дня}}',
  'min_hold_description_2' => '{value, plural, =0{# недель} =1{# неделя} one{# неделя} few{# недели} many{# недель} other{# недель}}',
  'min_hold_description_3' => '{value, plural, =0{# месяцев} =1{# месяц} one{# месяц} few{# месяца} many{# месяцев} other{# месяцев}}',
  'at_day_1' => 'Расхолд каждый {value}й день недели',
  'at_day_2' => 'Расхолд каждый {value}й день месяца',
  'hold_rules_hint' => '<p>
      У каждой страны заданы свои правила холда.
      Это значит, что профит, который получен сегодня, будет переведен на баланс только спустя некоторое время.
    </p>
    <h5>Пример #1.
      По Бразилии каждый четверг расхолдится "пачка" размером в 1 неделю, которая продержалась в холде минимум 2 недели.
    </h5>
    <ul><li><strong>Размер расхолда:</strong> 1 нед.</li>
      <li><strong>Минимум в холде:</strong> 2 нед.</li>
      <li><strong>Каждый 4й день недели</strong></li>
    </ul><h5>Пример #2.
      По Казахстану каждый 5й день месяца расхолдится "пачка" размером в 1 месяц.
    </h5>
    <ul><li><strong>Размер расхолда:</strong> 1 мес.</li>
      <li><strong>Каждый 5й день месяца</strong></li>
    </ul>',
  'unavailable' => 'Извините, сервис недоступен. Пожалуйста свяжитесь с администратором.',
  'convert_increase' => 'Начисление за конвертацию',
  'convert_decrease' => 'Списание за конвертацию',
];