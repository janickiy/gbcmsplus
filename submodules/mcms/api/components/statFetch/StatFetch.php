<?php

namespace mcms\api\components\statFetch;

use mcms\statistic\components\newStat\mysql\Fetch;
use mcms\statistic\components\newStat\mysql\query\Onetime;
use mcms\statistic\components\newStat\mysql\query\Subscriptions;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * для комплекс-фильтров.
 * Создает временную таблицу, чтобы потом её приджойнить к ActiveQuery для получения нужных свойств и сумм.
 */
class StatFetch extends Fetch
{

    const TOTAL_REVENUE = 'totalRevenue';
    const CPA_REVENUE = 'cpaRevenue';
    const REVSHARE_REVENUE = 'revshareRevenue';
    const OTP_REVENUE = 'otpRevenue';

    public $customFields = [];

    private $_tmpTableName = '';

    /**
     * @inheritdoc
     */
    protected function getQueryClasses()
    {
        $classes = [];

        if (array_intersect([self::TOTAL_REVENUE, self::CPA_REVENUE, self::REVSHARE_REVENUE], $this->customFields)) {
            $classes[] = Subscriptions::class;
        }

        if (array_intersect([self::TOTAL_REVENUE, self::OTP_REVENUE], $this->customFields)) {
            $classes[] = Onetime::class;
        }

        return $classes;
    }

    /**
     * подготовим временную таблицу, чтобы потом приджойнить
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function prepareTmpTable()
    {
        $needAttributes = $this->getNeededRowDtoAttributes();

        $this->createTmpTable();

        $this->handleQueries();

        foreach ($this->queries as $statQuery) {
            $usedSelect = [];
            $groupSelect = null;

            // удаляем лишние селекты, которые нам не нужны
            foreach ($statQuery->select as $rowDtoAttr => $expr) {
                if (in_array($rowDtoAttr, $this->getFormModel()->groups, true)) {
                    $groupSelect = [$rowDtoAttr => $expr];
                }

                if (in_array($rowDtoAttr, $needAttributes, true)) {
                    $usedSelect[] = $rowDtoAttr;
                    continue;
                }
                unset($statQuery->select[$rowDtoAttr]);
            }

            $statQuery->addSelect($groupSelect); // возвращаем группировочный селект
            $statQuery->limit(1000); // на всякий случай, врядли понадобятся суммы больше

            $usedSelectStr = implode(',', ArrayHelper::merge($usedSelect, ['id']));

            $onDuplicateKey = implode(",\n", array_map(function ($selectField) {
                return " $selectField=VALUES($selectField)";
            }, $usedSelect));

            Yii::$app->db->createCommand("INSERT INTO {$this->getTmpTableName()} ($usedSelectStr)
          {$statQuery->createCommand()->rawSql}
        ON DUPLICATE KEY UPDATE
          $onDuplicateKey")->execute();
        }
    }


    /**
     * Получить название временной таблицы
     * @return string
     * @throws \yii\base\Exception
     */
    public function getTmpTableName()
    {
        if ($this->_tmpTableName) {
            return $this->_tmpTableName;
        }

        return $this->_tmpTableName = 'tmp_cf_' . strtolower(substr(md5(Yii::$app->security->generateRandomString()), 0, 5));
    }

    /**
     * Словарик для кастом-полей из статы.
     * @param string $customField название кастом-поля
     * @return string[] название аттрибута для @see RowDataDto
     */
    public static function getCustomFieldRowDtoAttributes($customField)
    {
        $customFieldStatReference = [
            self::TOTAL_REVENUE => ['otp_reseller_profit_eur', 'buyout_reseller_profit_eur', 'revshare_reseller_profit_eur'],
            self::CPA_REVENUE => ['buyout_reseller_profit_eur'],
            self::REVSHARE_REVENUE => ['revshare_reseller_profit_eur'],
            self::OTP_REVENUE => ['otp_reseller_profit_eur'],
        ];

        return ArrayHelper::getValue($customFieldStatReference, $customField);
    }

    /**
     * Только нужные аттрибуты RowDto для полей
     * @return array
     */
    public function getNeededRowDtoAttributes()
    {
        $needAttributes = [];

        foreach ($this->customFields as $customField) {
            $attrs = static::getCustomFieldRowDtoAttributes($customField);
            $needAttributes = ArrayHelper::merge($needAttributes, $attrs);
        }

        return $needAttributes;
    }

    private function createTmpTable()
    {
        $columnDefinitions = implode(",\n", array_map(function ($field) {
            return "$field DECIMAL(12, 5) UNSIGNED DEFAULT 0 NOT NULL";
        }, $this->getNeededRowDtoAttributes()));

        Yii::$app->db->createCommand("CREATE TEMPORARY TABLE {$this->getTmpTableName()} (
      id MEDIUMINT(5) UNSIGNED DEFAULT 0 NOT NULL,
      $columnDefinitions,
      CONSTRAINT PRIMARY KEY (id)
  )")->execute();
    }
}
