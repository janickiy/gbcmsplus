<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\api\SourceList;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Response;

/**
 * Хэндлеры аякс-запросов для фильтров
 * Class StatFiltersController
 * @package mcms\statistic\controllers
 */
class StatFiltersController extends AdminBaseController
{
  const SOURCES_LIMIT = 10;
  const STREAMS_LIMIT = 10;
  const USERS_LIMIT = 10;

  /**
   * @param string $q
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function actionUsers($q = '')
  {
    /** @var \mcms\user\Module $usersModule */
    $usersModule = Yii::$app->getModule('users');
    /** @var ActiveDataProvider $users */
    $users = $usersModule->api('user', [
      'conditions' => [
        'queryName' => $q,
      ],
    ])->setResultTypeDataProvider()->getResult();

    // отключаем пагинацию чтобы не дергать запрос на COUNT
    $users->setPagination(false);
    /** @var ActiveQuery $query */
    $query = $users->query;
    // указываем лимит чтобы вс не дергать
    $query->limit(self::USERS_LIMIT);
    
    StatFilter::filterUsers($query);

    $users->setSort(['defaultOrder' => ['id' => SORT_ASC]]);

    return $this->returnSelect2Result(array_map(function ($item) {
      return [
        'text' => strtr(UserSelect2::USER_ROW_FORMAT, [
          ':id:' => ArrayHelper::getValue($item, 'id'),
          ':username:' => ArrayHelper::getValue($item, 'username'),
          ':email:' => ArrayHelper::getValue($item, 'email')
        ]),
        'id' => ArrayHelper::getValue($item, 'id'),
      ];
    }, $users->getModels()));
  }

  /**
   * @param $results
   * @return array
   */
  protected function returnSelect2Result($results)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ['results' => $results];
  }

  /**
   * @param string $q
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function actionStreams($q = '')
  {
    /** @var \mcms\promo\Module $module */
    $module = Yii::$app->getModule('promo');
    /** @var ActiveDataProvider $dataProvider */
    $dataProvider = $module->api('streams', [
      'conditions' => [
        'queryName' => $q
      ],
      'statFilters' => true
    ])->setResultTypeDataProvider()->getResult();

    $dataProvider->setSort(['defaultOrder' => ['id' => SORT_ASC]]);
    // указываем лимит чтобы вс не дергать
    $dataProvider->query->limit(self::STREAMS_LIMIT);

    return $this->returnSelect2Result(array_map(function ($item) {
      /** @var Stream $item */
      return [
        'id' => $item->id,
        'text' => $item->getStringInfo()
      ];
    }, $dataProvider->getModels()));
  }


  /**
   * @param string $q
   * @param null $sourceType
   * @return array
   */
  public function actionSources($q = '', $sourceType = null)
  {
    $conditions = ['queryName' => $q];

    /** @var \mcms\promo\Module $module */
    $module = Yii::$app->getModule('promo');

    if ($sourceType) {
      $conditions['source_type'] = $sourceType;
    }

    /** @var ActiveDataProvider $dataProvider */
    $dataProvider = $module->api('sources', [
      'conditions' => $conditions,
      'statFilters' => true,
    ])->setResultTypeDataProvider()->getResult();

    $dataProvider->setSort(['defaultOrder' => ['id' => SORT_ASC]]);
    // указываем лимит чтобы вс не дергать
    $dataProvider->query->limit(self::SOURCES_LIMIT);

    return $this->returnSelect2Result(array_map(function ($item) {
      /** @var Source $item */
      return [
        'id' => $item->id,
        'text' => $item->getStringInfo()
      ];
    }, $dataProvider->getModels()));
  }
}