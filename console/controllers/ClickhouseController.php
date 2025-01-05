<?php

namespace console\controllers;

use yii\console\Controller;
use yii\db\Query;

class ClickhouseController extends Controller
{

    public function actionImport()
    {
        $this->actionImportHits();
        $this->actionImportSubscriptions();
        $this->actionImportSoldSubscriptions();
        $this->actionImportRebills();
    }

    public function actionImportHits()
    {
        echo 'Processing hits' . PHP_EOL;
        $limit = 100000;
        $page = 0;

        $fp = fopen('./tmp/hits.data.csv', 'w');

        while (true) {
            echo 'Processing page: ' . $page . PHP_EOL;
            $data = (new Query())
                ->select([
                    'h.id',
                    'h.time',
                    'h.is_tb',
                    'h.is_unique',
                    'h.traffic_type',
                    'h.date',
                    'h.hour',
                    'h.source_id',
                    'h.landing_id',
                    'h.operator_id',
                    'h.platform_id',
                    'h.landing_pay_type_id',
                    'hp.label1',
                    'hp.subid1',
                    'hp.label2',
                    'hp.subid2',
                ])
                ->from(['h' => 'hits'])
                ->innerJoin(['hp' => 'hit_params'], 'h.id = hp.hit_id')
                ->limit($limit)
                ->offset($limit * $page)
                ->all();

            if (count($data) === 0) break;

            foreach ($data as $dataItem) {
                fputcsv($fp, $dataItem);
            }

            $page++;
        }

        fclose($fp);

        echo 'Done' . PHP_EOL;
    }

    public function actionImportSubscriptions()
    {
        echo 'Processing subscriptions' . PHP_EOL;
        $limit = 100000;
        $page = 0;

        $fp = fopen('./tmp/subscriptions.data.csv', 'w');

        while (true) {
            echo 'Processing page: ' . $page . PHP_EOL;
            $data = (new Query())
                ->select([
                    'id',
                    'hit_id',
                    'trans_id',
                    'time',
                    'date',
                    'hour',
                    'landing_id',
                    'source_id',
                    'operator_id',
                    'platform_id',
                    'landing_pay_type_id',
                    'phone',
                    'is_cpa',
                    'currency_id',
                    'provider_id',
                    'is_fake',
                ])
                ->from('subscriptions')
                ->offset($limit * $page)
                ->limit($limit)
                ->all();

            if (count($data) === 0) break;

            foreach ($data as $dataItem) {
                fputcsv($fp, $dataItem);
            }

            $page++;
        }

        fclose($fp);

        echo 'Done' . PHP_EOL;
    }

    public function actionImportRebills()
    {
        echo 'Processing rebills' . PHP_EOL;
        $limit = 100000;
        $page = 0;

        $fp = fopen('./tmp/rebills.data.csv', 'w');

        while (true) {
            echo 'Processing page: ' . $page . PHP_EOL;
            $data = (new Query())
                ->select([
                    'id',
                    'hit_id',
                    'trans_id',
                    'time',
                    'date',
                    'hour',
                    'default_profit',
                    'default_profit_currency',
                    'currency_id',
                    'real_profit_rub',
                    'real_profit_eur',
                    'real_profit_usd',
                    'reseller_profit_rub',
                    'reseller_profit_eur',
                    'reseller_profit_usd',
                    'profit_rub',
                    'profit_eur',
                    'profit_usd',
                    'landing_id',
                    'source_id',
                    'old_source_id',
                    'operator_id',
                    'platform_id',
                    'landing_pay_type_id',
                    'is_cpa',
                    'provider_id',
                ])
                ->from('subscription_rebills')
                ->limit($limit)
                ->offset($limit * $page)
                ->all();

            if (count($data) === 0) break;

            foreach ($data as $dataItem) {
                fputcsv($fp, $dataItem);
            }

            $page++;
        }

        fclose($fp);

        echo 'Done' . PHP_EOL;
    }

    public function actionImportSoldSubscriptions()
    {
        echo 'Processing sold subscriptions' . PHP_EOL;
        $limit = 100000;
        $page = 0;

        $fp = fopen('./tmp/sold_subscriptions.data.csv', 'w');

        while (true) {
            echo 'Processing page: ' . $page . PHP_EOL;
            $data = (new Query())
                ->select([
                    'id',
                    'hit_id',
                    'currency_id',
                    'real_price_rub',
                    'real_price_eur',
                    'real_price_usd',
                    'reseller_price_rub',
                    'reseller_price_eur',
                    'reseller_price_usd',
                    'price_rub',
                    'price_eur',
                    'price_usd',
                    'profit_rub',
                    'profit_eur',
                    'profit_usd',
                    'time',
                    'date',
                    'stream_id',
                    'source_id',
                    'user_id',
                    'to_stream_id',
                    'to_source_id',
                    'to_user_id',
                    'landing_id',
                    'operator_id',
                    'platform_id',
                    'landing_pay_type_id',
                    'provider_id',
                    'country_id',
                    'is_visible_to_partner',
                ])
                ->from('sold_subscriptions')
                ->limit($limit)
                ->offset($limit * $page)
                ->all();

            if (count($data) === 0) break;

            foreach ($data as $dataItem) {
                fputcsv($fp, $dataItem);
            }

            $page++;
        }

        fclose($fp);

        echo 'Done' . PHP_EOL;
    }
}
