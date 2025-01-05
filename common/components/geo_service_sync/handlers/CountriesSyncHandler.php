<?php

namespace common\components\geo_service_sync\handlers;

use mcms\promo\models\Country;
use mcms\promo\models\Operator;
use mcms\promo\models\OperatorIp;
use rgk\geoservice_client\endpoints\Countries;
use rgk\geoservice_client\responses\Country as CountryDto;
use rgk\geoservice_client\responses\Currency;
use rgk\geoservice_client\responses\Ip;
use Yii;
use yii\helpers\Console;

/**
 * Class OperatorsSyncHandler
 * @package common\components\geo_service_sync\handlers
 */
class CountriesSyncHandler extends AbstractSyncHandler
{
    const DEFAULT_CURRENCY = 'eur';

    public function sync()
    {
        $this->log('Start ' . __CLASS__ . ' sync handler' . PHP_EOL);
        /** @var Countries $endpoint */
        $endpoint = $this->getEndpoint(Countries::class)
            ->with(Currency::class)
            ->with(Ip::class);

        /** @var CountryDto $dto */
        foreach ($endpoint->each() as $dto) {
            $this->log('  Processing countryId: ' . $dto->id . ' - ' . $dto->name . PHP_EOL);

            $model = Country::findOne($dto->id);
            if (!$model) {
                if (!$dto->isActive) {
                    $this->log('  Error: can not find country with id: ' . $dto->id . PHP_EOL, [Console::FG_RED]);
                    continue;
                }

                $model = $this->createModel($dto);
            } elseif (!$model->isActive()) {
                $this->log('  Error: CountryModel is not active ' . PHP_EOL, [Console::FG_RED]);
                continue;
            }

            if ($model->sync_updated_at > $dto->updatedAt) {
                $this->log('  Info: country dto has no difference ' . PHP_EOL);
                continue;
            }

            if ($dto->wifi_id) {
                $operatorModel = Operator::findOne($dto->wifi_id);
                if (!$operatorModel) {
                    $this->createOperator($dto);
                }

                if ($dto->ips) {
                    $this->saveIps($dto);
                }
            }

            $model->sync_updated_at = time();
            $model->save();

            $this->log('  Processing countryId: ' . $dto->id . ' - ' . $dto->name . ' - done' . PHP_EOL);
            $this->log(PHP_EOL);
        }

        $this->log('Done ' . __CLASS__ . ' sync handler' . PHP_EOL);
    }

    /**
     * @param CountryDto $dto
     * @return Country
     */
    protected function createModel($dto)
    {
        $model = new Country();
        $model->id = $dto->id;
        $model->name = $dto->name;
        $model->code = strtoupper($dto->code2l);
        $model->currency = self::DEFAULT_CURRENCY;
        $model->local_currency = $dto->currency ? $dto->currency->code3l : null;
        $model->status = Country::STATUS_ACTIVE;
        $model->sync_updated_at = time();
        $model->save();

        $this->log('  New Country created, id = ' . $model->id . ' - ' . $model->name . '' . PHP_EOL);

        return $model;
    }

    /**
     * @param CountryDto $dto
     * @return Operator
     */
    protected function createOperator($dto)
    {
        $model = new Operator();
        $model->id = $dto->wifi_id;
        $model->country_id = $dto->id;
        $model->name = "Wi-Fi ({$dto->name})";
        $model->is_3g = 0;
        $model->status = Operator::STATUS_ACTIVE;
        $model->sync_updated_at = time();
        $model->is_trial = 0;
        $model->save();

        $this->log('  New Operator created, id = ' . $model->id . ' - ' . $model->name . '' . PHP_EOL);

        return $model;
    }

    /**
     * @param CountryDto $dto
     */
    protected function saveIps($dto)
    {
        $actualIp = [];
        foreach ($dto->ips as $ip) {
            $ipModel = new OperatorIp([
                'operator_id' => $dto->wifi_id,
                'from_ip' => long2ip($ip->from),
                'mask' => $ip->mask,
                'to_ip' => long2ip($ip->to),
            ]);

            if (!$ipModel->validate()) {
                $this->log('  Validation Error: ' . implode(PHP_EOL, $ipModel->getErrorSummary(true)) . PHP_EOL, [Console::FG_RED]);
                continue;
            }

            $actualIp[] = [$dto->wifi_id, $ip->from, $ip->mask, $ip->to, 0];
        }

        if (!$actualIp) {
            OperatorIp::deleteAll(['operator_id' => $dto->wifi_id]);
            $this->log('  Actual ip is empty' . PHP_EOL, [Console::FG_YELLOW]);
            return;
        }

        $markedForDelete = OperatorIp::updateAll(['should_delete' => 1], ['operator_id' => $dto->wifi_id]);

        $db = Yii::$app->db;

        $sql = $db
            ->queryBuilder
            ->batchInsert(OperatorIp::tableName(), ['operator_id', 'from_ip', 'mask', 'to_ip', 'should_delete'], $actualIp);

        $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE should_delete=0')->execute();

        OperatorIp::deleteAll(['operator_id' => $dto->wifi_id, 'should_delete' => 1]);

        $this->log('  Updated to delete: ' . $markedForDelete . PHP_EOL);
        $this->log('  Created new ips: ' . count($actualIp) . PHP_EOL);
    }
}
