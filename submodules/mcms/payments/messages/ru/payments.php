<?php
return [
  'list' => 'Список выплат',
  'all' => 'Все',
  'awaiting' => 'В ожидании',
  'archive' => 'Архив',
  'create' => 'Добавить выплату',
  'update' => 'Обновить выплату',
  'view' => 'Просмотр выплаты',
  'info' => 'Информация о выплате',
  'generate-payments' => 'Сгенерировать выплаты',
  'payments-created' => 'Выплаты добавлены',
  'generate-nothing' => 'Невозможность создать выплаты по заданным параметрам',
  'wallet-balances' => 'Баланс кошельков',
  'attribute-created_at' => 'Дата создания',
  'attribute-count' => 'Количество',
  'attribute-error' => 'Ошибка',
  'attribute-dateFrom' => 'Дата от',
  'attribute-dateTo' => 'Дата до',
  'attribute-dateRange' => 'Период даты',
  'attribute-isHold' => 'Холд',
  'attribute-userId' => 'Пользователь',
  'attribute-landingIds' => 'Лендинги',
  'auto-payout' => 'Автовыплаты',
  'mass-payout' => 'Массовые выплаты',
  'payout-on' => 'Выплатить на {0}',
  'payout' => 'Выплатить',
  'message-success-payout' => 'Успешно выплачено: {0}',
  'message-fail-payout' => 'Ошибка выплаты: {0}',
  'message-no-payments' => 'Нет доступных выплат',
  'attribute-user_settings' => 'Настройки пользователя',
  'attribute-payment' => 'Выплата',
  'attribute-payment-error' => 'Ошибка',
  'attribute-amount' => 'Сумма',
  'attribute-actual-amount' => 'Фактическая сумма',
  'error-user-not-payable' => 'Пользователю не доступны выплаты',
  'error-wallet-type-not-same' => 'Тип кошелька в выплате и в настройках пользователя не совпадают',
  'error-invalid-dateRange' => 'Неверный период',
  'error-not-available' => 'Недоступно к выплате',
  'description-early-payment' => 'Досрочная выплата. Комиссия {percent}%',
  'description-merchant-payment' => 'Выплата id#{id} на счет пользователя {userId} за {period}. Партнерская программа {projectName}. Коментарий: "{autoPayComment}"',
  'confirm-auto-payout' => 'Выплатить автоматически на {0}?',
  'error-user-wallet-not-defined' => 'Кошелек/счет пользователя не указан. Добавление выплаты не возможно.',
  'recipient' => 'Получатель',
  'sender' => 'Отправитель',
  'description' => 'Описание',
  'description-hint' => 'Невидимый для партнера. Не будет отправлен в API платежной системы при автовыплате.',
  'summary-to-payment' => 'К выплате',
  'payment_modificator' => 'за выплату на данный тип кошелька применяется модификатор: {percent}%',
  'modify_description_course' => 'конвертация {amount} {currency} по курсу {course}',
  'locked' => 'Выплату нельзя редактировать',
  'payments-summ' => 'Сумма выплат',
  'balances-summ' => 'Сумма балансов',
  'convert_confirm_text' => 'Валюты баланса и кошелька отличаются.<br>Сумма выплаты будет сконвертирована в',
  'payment_info_error' => 'Не удалось получить информацию о выплате. Попробуйте обновите страницу',
  'card_invalid' => 'Неверный номер карты',
  'processing_commission' => 'Комиссия за процессинг: {percent}%',
  'paysystem_commission' => 'Комиссия платежной системы: {percent}%',
  'individual_percent' => 'Индивидуальный процент: {percent}%',
  'course' => 'Курс',
  'real_course' => 'Чистый',
  'partner_course' => 'Партнерский',
  'proceed' => 'Обработать'
];