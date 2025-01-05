<?php

namespace admin\modules\credits\commands;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
use mcms\common\mgmp\MgmpClient;

/**
 * Импортировать кредиты, транзакции и инвойсы
 * TRICKY если в интервале между импортом и экспортом запись (например транзакция) на мцмс и мп будет изменена будет
 * конфликт. Для решения конфликта будет выбрана более новая версия сущности. Для минимизации конфликтов нужна
 * максимальная большая частота синхронизации, в идеале real-time TRICKY Инвойсы не импортируются, они автоматически
 * создаются/обновляются при сохранении транзакции TRICKY Важен порядок импорта. Сначала должны импортироваться
 * кредиты, затем транзакции, иначе транзакции будет не к чему привязывать
 */
class CreditsImportController extends Controller
{
    /** @const string Ключ для сохранения даты синхронизации кредитов в кэш */
    const CREDITS_SYNC_DATE_KEY = 'credits_mgmp_sync_date';
    /** @const string Ключ для сохранения даты синхронизации транзакций в кэш */
    const CREDIT_TRANSACTIONS_SYNC_DATE_KEY = 'credit_transactions_mgmp_sync_date';
    /**
     * @var bool Статус синхронизации кредитов
     * true - синхронизация прошла успешно
     * false - часть кредитов не прошла синхронизацию, дата синхронизации не будет обновлена, что бы синхронизация прошла
     * заново
     */
    private $creditsSyncSuccess = false;
    /**
     * @var bool Статус синхронизации транзакций
     * true - синхронизация прошла успешно
     * false - часть транзакций не прошла синхронизацию, дата синхронизации не будет обновлена, что бы синхронизация
     *   прошла заново
     */
    private $creditTransactionsSyncSuccess = false;

    public function actionIndex()
    {
        $lastSyncDates = $this->getLastSyncDates();
        // TRICKY Дата синхронизации берется вначале, иначе данные созданные во время синхронизации никогда не будут синхронизованы
        $syncDate = time();

        $this->stdout('Credits sync from: ' . Yii::$app->formatter->asDatetime($lastSyncDates['credits']) . "\n");
        $this->stdout('Transactions sync from: ' . Yii::$app->formatter->asDatetime($lastSyncDates['transactions']) . "\n");

        // Получение данных из MGMP
        $data = $this->requestData($lastSyncDates);
        if (empty($data)) {
            $this->log('MGMP data invalid', __METHOD__);
            return;
        }

        $this->importCredits($data['credits']);
        $this->importTransactions($data['transactions']);

        if (!$this->creditsSyncSuccess) {
            $this->log('Some credits sync failed', __METHOD__);
        }
        if (!$this->creditTransactionsSyncSuccess) {
            $this->log('Some credit transactions sync failed', __METHOD__);
        }

        $this->updateSyncDates($syncDate);
    }

    /**
     * Импорт кредитов
     * @param array[] $credits
     */
    private function importCredits($credits)
    {
        $creditsProcessed = 0;
        $creditsSaved = 0;
        /** @var array $creditData */
        foreach ($credits as $creditData) {
            // Поиск существующего кредита
            $creditQuery = Credit::find();
            if (!empty($creditData['mcms_id'])) {
                $creditQuery->andWhere(['id' => $creditData['mcms_id']]);
            } elseif (!empty($creditData['mgmp_id'])) {
                $creditQuery->andWhere(['external_id' => $creditData['mgmp_id']]);
            } else {
                $this->log('Invalid credit data. Not found mgmp_id or mcms_id', __METHOD__);
                continue;
            }
            $credit = $creditQuery->one();
            if (!$credit) {
                $credit = new Credit;
            }

            // Проверка актуальности кредита
            if (!$credit->isNewRecord && $credit->updated_at >= $creditData['updated_at'] && $credit->external_id) {
                $creditsProcessed++;
                continue;
            }

            // Сохранение кредита
            $credit->external_id = $creditData['mgmp_id'];
            $credit->amount = $creditData['amount'];
            $credit->currency = $creditData['currency'];
            $credit->status = $creditData['status'];
            $credit->percent = $creditData['percent'];
            $credit->decline_reason = $creditData['decline_reason'];
            $credit->activated_at = $creditData['activated_at'];
            $credit->created_at = $creditData['created_at'];
            $credit->closed_at = $creditData['closed_at'];
            // Если запись новая, дата обновления актуализируется,
            // что бы сервис из которого импортирована эта запись в дальнейшем импортнул id (external_id)
            $credit->updated_at = $credit->isNewRecord ? time() : $creditData['updated_at'];

            if ($credit->save()) {
                $creditsProcessed++;
                $creditsSaved++;
            } else {
                $this->log('Cant ' . ($credit->isNewRecord ? 'create' : 'update') . ' credit external_id ' . $credit->external_id, __METHOD__);
            }
        }

        // Проверка все ли кредиты успешно синхронизированы
        $this->creditsSyncSuccess = count($credits) == $creditsProcessed;
        $this->stdout("Credits: processed $creditsProcessed ($creditsSaved saved, "
            . ($creditsProcessed - $creditsSaved) . " skipped)\n");
    }

    /**
     * Импорт транзакций
     * @param $transactions
     */
    private function importTransactions($transactions)
    {
        $transactionsProcessed = 0;
        $transactionsSaved = 0;
        /** @var array $transactionData */
        foreach ($transactions as $transactionData) {
            // Поиск существующей транзакции
            $transactionQuery = CreditTransaction::find();
            if (!empty($transactionData['mcms_id'])) {
                $transactionQuery->andWhere(['id' => $transactionData['mcms_id']]);
            } elseif (!empty($transactionData['mgmp_id'])) {
                $transactionQuery->andWhere(['external_id' => $transactionData['mgmp_id']]);
            } else {
                $this->log('Invalid transaction data. Not found mgmp_id or mcms_id', __METHOD__);
                continue;
            }
            $transaction = $transactionQuery->one();
            if (!$transaction) {
                $transaction = new CreditTransaction;
            }

            // Проверка актуальности транзакции
            if (!$transaction->isNewRecord && $transaction->updated_at >= $transactionData['updated_at'] && $transaction->external_id) {
                $transactionsProcessed++;
                continue;
            }

            // Поиск кредита транзакции
            $creditQuery = Credit::find();
            if ($transactionData['mcms_credit_id']) {
                $creditQuery->andWhere(['id' => $transactionData['mcms_credit_id']]);
            } elseif ($transactionData['mgmp_credit_id']) {
                $creditQuery->andWhere(['external_id' => $transactionData['mgmp_credit_id']]);
            } else {
                throw new InvalidParamException('В транзакции должен быть один из параметров: mgmp_credit_id или mcms_credit_id');
            }
            $credit = $creditQuery->one();
            if (!$credit) {
                $this->log('Cant find credit with external_id #' . $transactionData['mgmp_credit_id']
                    . ' for transaction with external_id #' . $transactionData['mgmp_id'], __METHOD__);
                continue;
            }

            // Сохранение транзакции
            $transaction->external_id = $transactionData['mgmp_id'];
            $transaction->credit_id = $credit->id;
            $transaction->amount = $transactionData['amount'];
            $transaction->type = $transactionData['type'];
            $transaction->fee_date = $transactionData['fee_date'];
            $transaction->created_at = $transactionData['created_at'];
            // Если запись новая, дата обновления актуализируется,
            // что бы сервис из которого импортирована эта запись в дальнейшем импортнул id (external_id)
            $transaction->updated_at = $transaction->isNewRecord ? time() : $transactionData['updated_at'];

            if ($transaction->save()) {
                $transactionsProcessed++;
                $transactionsSaved++;
            } else {
                $this->log('Cannot ' . ($transaction->isNewRecord ? 'create' : 'update') . ' transaction with external_id #' . $transaction->external_id, __METHOD__);
            }
        }

        $this->creditTransactionsSyncSuccess = count($transactions) == $transactionsProcessed;
        $this->stdout("Transactions: processed $transactionsProcessed ($transactionsSaved saved, "
            . ($transactionsProcessed - $transactionsSaved) . " skipped)\n");
    }

    /**
     * Получение данных из MGMP
     * @param array $dates
     * @return array|null
     */
    private function requestData($dates)
    {
        $response = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_CREDITS, [
            'creditsDateFrom' => $dates['credits'],
            'transactionsDateFrom' => $dates['transactions'],
        ]);

        if (!$response->getIsOk()) {
            $this->log('Error! Response status: ' . $response->statusCode, __METHOD__);
            return null;
        }

        $data = $response->getData();
        if (!ArrayHelper::getValue($data, 'success')) {
            $this->log('MGMP Api returned success=false', __METHOD__);
            return null;
        }

        return ArrayHelper::getValue($data, 'data');
    }

    /**
     * Лог ошибки
     * @param $message
     * @param $method
     */
    private function log($message, $method)
    {
        $this->stderr($message . "\n");
        Yii::error($message, $method);
    }

    /**
     * Даты последней синхронизации кредитов и транзакций
     * @return array
     */
    private function getLastSyncDates()
    {
        return [
            'credits' => (int)Yii::$app->cache->get($this->getCreditsSyncDateKey()),
            'transactions' => (int)Yii::$app->cache->get($this->getCreditTransactionsSyncDateKey()),
        ];
    }

    /**
     * Обновить даты последней синхронизации
     * @param int $dateBeforeSync Дата до синхронизации
     */
    private function updateSyncDates($dateBeforeSync)
    {
        if ($this->creditsSyncSuccess) {
            Yii::$app->cache->set($this->getCreditsSyncDateKey(), $dateBeforeSync, 86400 * 30);
        }

        if ($this->creditTransactionsSyncSuccess) {
            Yii::$app->cache->set($this->getCreditTransactionsSyncDateKey(), $dateBeforeSync, 86400 * 30);
        }
    }

    /**
     * Ключ в кэшэ для сохранения даты синхронизации кредитов
     * @return string
     */
    private function getCreditsSyncDateKey()
    {
        return self::CREDITS_SYNC_DATE_KEY;
    }

    /**
     * Ключ в кэшэ для сохранения даты синхронизации кредитов
     * @return string
     */
    private function getCreditTransactionsSyncDateKey()
    {
        return self::CREDIT_TRANSACTIONS_SYNC_DATE_KEY;
    }
}
