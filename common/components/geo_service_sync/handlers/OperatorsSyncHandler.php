<?php

namespace common\components\geo_service_sync\handlers;

use mcms\promo\models\Operator;
use mcms\promo\models\OperatorIp;
use mcms\promo\models\OperatorIpv6;
use rgk\geoservice_client\endpoints\Operators;
use rgk\geoservice_client\responses\Ip;
use rgk\geoservice_client\responses\Ipv6;
use rgk\geoservice_client\responses\Operator as OperatorDto;
use Yii;
use yii\helpers\Console;

/**
 * Class OperatorsSyncHandler
 * @package common\components\geo_service_sync\handlers
 */
class OperatorsSyncHandler extends AbstractSyncHandler
{
    public function sync()
    {
        $this->log('Start ' . __CLASS__ . ' sync handler' . PHP_EOL);
        /** @var Operators $endpoint */
        $endpoint = $this->getEndpoint(Operators::class)
            ->with(Ip::class)
            ->with(Ipv6::class);

        /** @var OperatorDto $dto */
        foreach ($endpoint->each() as $dto) {
            $this->log('  Processing operatorId: ' . $dto->id . ' - ' . $dto->name . PHP_EOL);

            $model = Operator::findOne($dto->id);
            if (!$model) {
                if (!$dto->isActive) {
                    $this->log('  Error: external operator is not active, id: ' . $dto->id . PHP_EOL, [Console::FG_RED]);
                    continue;
                }

                $model = $this->createModel($dto);
            } elseif (!$model->isActive() || !$model->is_3g) {
                $this->log('  Error: OperatorModel is not active ' . PHP_EOL, [Console::FG_RED]);
                continue;
            }

            if ($model->sync_updated_at > $dto->updatedAt) {
                $this->log('  Info: operator dto has no difference ' . PHP_EOL);
                continue;
            }

            $this->saveIps($dto);
            $this->saveIpv6s($dto);

            $model->sync_updated_at = time();
            $model->save();

            $this->log('  Processing operatorId: ' . $dto->id . ' - ' . $dto->name . ' - done' . PHP_EOL);
            $this->log(PHP_EOL);
        }

        $this->log('Done ' . __CLASS__ . ' sync handler' . PHP_EOL);
    }

    /**
     * @param OperatorDto $dto
     * @return Operator
     */
    protected function createModel($dto)
    {
        $model = new Operator();
        $model->id = $dto->id;
        $model->country_id = $dto->countryId;
        $model->name = $dto->name;
        $model->is_3g = 1;
        $model->status = Operator::STATUS_ACTIVE;
        $model->sync_updated_at = time();
        $model->is_trial = 0;
        $model->save();

        $this->log('  New Operator created, id = ' . $dto->id . ' - ' . $dto->name . '' . PHP_EOL);

        return $model;
    }

    /**
     * @param OperatorDto $dto
     */
    protected function saveIps($dto)
    {
        $actualIp = [];
        foreach ($dto->ips as $ip) {
            $ipModel = new OperatorIp([
                'operator_id' => $dto->id,
                'from_ip' => long2ip($ip->from),
                'mask' => $ip->mask,
                'to_ip' => long2ip($ip->to),
            ]);

            if (!$ipModel->validate()) {
                $this->log('  Validation Error: ' . implode(PHP_EOL, $ipModel->getErrorSummary(true)) . PHP_EOL, [Console::FG_RED]);
                continue;
            }

            $actualIp[] = [$dto->id, $ip->from, $ip->mask, $ip->to, 0];
        }

        if (!$actualIp) {
            OperatorIp::deleteAll(['operator_id' => $dto->id]);
            $this->log('  Actual ip is empty' . PHP_EOL, [Console::FG_YELLOW]);
            return;
        }

        $markedForDelete = OperatorIp::updateAll(['should_delete' => 1], ['operator_id' => $dto->id]);

        $db = Yii::$app->db;

        $sql = $db
            ->queryBuilder
            ->batchInsert(OperatorIp::tableName(), ['operator_id', 'from_ip', 'mask', 'to_ip', 'should_delete'], $actualIp);

        $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE should_delete=0')->execute();

        OperatorIp::deleteAll(['operator_id' => $dto->id, 'should_delete' => 1]);

        $this->log('  Updated to delete: ' . $markedForDelete . PHP_EOL);
        $this->log('  Created new ips: ' . count($actualIp) . PHP_EOL);
    }

    /**
     * @param OperatorDto $dto
     */
    protected function saveIpv6s($dto)
    {
        $actualIps = [];
        foreach ($dto->ipv6s as $ipv6) {
            $ipModel = new OperatorIpv6([
                'operator_id' => $dto->id,
                'ip' => $ipv6->ip,
                'mask' => $ipv6->mask,
            ]);

            if (!$ipModel->validate()) {
                $this->log('  Validation Error: ' . implode(PHP_EOL, $ipModel->getErrorSummary(true)) . PHP_EOL, [Console::FG_RED]);
                continue;
            }

            $actualIps[] = [
                'operator_id' => $dto->id,
                'ip' => $ipv6->ip,
                'mask' => $ipv6->mask,
            ];
        }

        $condition = $actualIps;
        array_unshift($condition, 'or');

        // получаем id уже имеющихся ip адресов
        $operatorIpv6Ids = OperatorIpv6::find()
            ->select('id')
            ->andWhere($condition)
            ->column();

        // удаляем сами модели, которые не вошли в имеющиеся
        Yii::$app->db->createCommand()->delete(OperatorIpv6::tableName(), [
            'and',
            ['not in', 'id', $operatorIpv6Ids],
            ['operator_id' => $dto->id],
        ])->execute();

        if (!$actualIps) {
            $this->log('Actual ipv6 is empty' . PHP_EOL, [Console::FG_YELLOW]);

            return;
        }

        $db = Yii::$app->db;
        // добавляем оставшиеся
        $batchInsertSql = Yii::$app->db
            ->queryBuilder
            ->batchInsert(
                OperatorIpv6::tableName(),
                ['operator_id', 'ip', 'mask'],
                $actualIps
            );
        $batchInsertSql .= ' ON DUPLICATE KEY UPDATE id = id'; // костыль для замены insert ignore

        $db->createCommand($batchInsertSql)->execute();
    }
}
