<?php

namespace admin\dashboard\widgets\top_lp;

use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\traits\Translate;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;

class TopLpWidget extends BaseWidget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.top_lp-';
    const CACHE_KEY = 'DashboardPotLpWidget';

    public $padding = false;
    public $wrapperId = 'lp-widget';


    public static function getInstance($params = [])
    {
        return (new static([
            'userId' => Yii::$app->user->id,
            'countries' => ArrayHelper::getValue($params, 'countries'),
            'period' => ArrayHelper::getValue($params, 'period'),
        ]));
    }

    public function getFrontData()
    {
        $tableData = [];
        foreach ($this->getDataFromCache() as $item) {
            $tableData[] = [
                'lp' => $item['label'],
                'clicks' => $item['clicks'],
                'cr' => sprintf('1:%g', $item['ratio']),
                /*
                 * если ratio = 0 то в сортировке ленд будет на первом месте, а так быть не должно
                 * строка ratio не выводится (используется только для сортировки), поэтому ее значение не важно
                 */
                'ratio' => $item['ratio'] ?: 99999999,
            ];
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $tableData,
            'sort' => [
                'attributes' => [
                    'ratio'
                ],
                'defaultOrder' => [
                    'ratio' => SORT_ASC
                ]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        return $this->render('top_lp', [
            'dataProvider' => $dataProvider,
            'wrapperId' => $this->wrapperId,
            'url' => $this->getUrl(),
        ]);
    }

    public function getData()
    {
        return array_map(function ($item) {
            $url = Yii::$app->getModule('promo')->api('landingById', [
                'landingId' => ArrayHelper::getValue($item, 'landing_id')
            ])->getUrlParam();

            $link = Html::a(
                sprintf('#%s. %s', ArrayHelper::getValue($item, 'landing_id'), ArrayHelper::getValue($item, 'name')),
                $url,
                ['data-pjax' => 0, 'target' => '_blank'],
                [],
                false
            );

            return [
                'clicks' => ArrayHelper::getValue($item, 'clicks'),
                'ratio' => ArrayHelper::getValue($item, 'ratio'),
                'label' => $link,
            ];
        }, $this->getApi()->getLandings());
    }

    protected function getContent()
    {
        return $this->getFrontData();
    }

    public function getTitle()
    {
        return static::translate('title');
    }

    public function getBlockClass()
    {
        return 'performing';
    }

    public function getPermission()
    {
        return 'AppBackendWidgetTopLp';
    }

    public function getUrl()
    {
        return Url::to(['/widget/top-lp/']);
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . '-' . $this->userId . '-' .
            implode('-', [
                str_replace(' ', '', $this->period),
                implode('-', (array)$this->countries),
            ]);
    }
}