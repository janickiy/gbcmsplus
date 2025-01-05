<?php

namespace mcms\pages\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\pages\models\Category;
use mcms\pages\models\PageSearch;
use yii\base\Exception;
use yii\base\Widget;
use Yii;
use yii\caching\TagDependency;

class PagesWidget extends Widget
{

  public $options;

  public $view;
  public $viewBasePath;

  public $categoryCode;

  public $pageCode;

  public $filter = [];

  private $viewFile;

  private $data;

  private $category;

    /**
     * @var \mcms\pages\Module
     */
  private $pagesModule;

  const CACHE_PREFIX = 'pages_widget_';

  const CACHE_CATEGORY_PREFIX = self::CACHE_PREFIX . 'category';

  // Пока один тег для всего кэша, т.к. массивы со связями подхватывают много инфы
  const CACHE_TAG = 'pages_widgets';

  public function init()
  {
    parent::init();

    $this->view = $this->view ? : ArrayHelper::getValue($this->options, 'view');
    if (!$this->view) {
      throw new Exception('view param is required');
    }

    $this->categoryCode = $this->categoryCode ? : ArrayHelper::getValue($this->options, 'categoryCode');
    if (!$this->categoryCode) {
      throw new Exception('categoryCode param is required');
    }

    $this->pageCode = $this->pageCode ? : ArrayHelper::getValue($this->options, 'pageCode');

    $this->viewBasePath = $this->viewBasePath ? : ArrayHelper::getValue($this->options, 'viewBasePath');

    $this->filter = $this->filter ? : ArrayHelper::getValue($this->options, 'filter');

    $this->filter['category']['code'] = $this->categoryCode;

    $this->filter['is_disabled'] = isset($this->filter['is_disabled']) ? $this->filter['is_disabled'] : 0;

    if ($this->pageCode) {
      $this->filter['code'] = $this->pageCode;
    }

    $this->viewFile = $this->viewBasePath ? $this->viewBasePath . $this->view : $this->view;

    $this->data = $this->getData();

    $this->category = $this->getCategory();

    $this->pagesModule = Yii::$app->getModule('pages');
  }


  public function run()
  {

    if (!$this->category) {
      echo '<span style="background-color: red; color: white">error: CATEGORY NOT FOUND</span>'; return null;
    }

    if ($this->pageCode && empty($this->data)) {
      echo '<span style="background-color: red; color: white">error: PAGE NOT FOUND</span>'; return null;
    }

    return $this->render($this->viewFile, [
      'data' => $this->data,
      'category' => $this->category,
      'pagesModule' => $this->pagesModule,
    ]);
  }

  private function getData()
  {
    $cached = Yii::$app->cache->get($this->getDataCacheKey());

    if ($cached) return $cached;


    $data = (new PageSearch())->search($this->filter, true)
      ->query
      ->joinWith(['category', 'props', 'props.categoryProp', 'props.entity'])
      ->orderBy(['sort' => SORT_ASC])
      ->all();

    Yii::$app->cache->set($this->getDataCacheKey(), $data, 3600, $this->getTagDependency());

    return $data;
  }

  private function getDataCacheKey()
  {
    return self::CACHE_PREFIX . serialize($this->filter);
  }


  private function getCategory()
  {
    $cached = Yii::$app->cache->get($this->getCategoryCacheKey());

    if ($cached) return $cached;

    $data = Category::find()
      ->where([Category::tableName() . '.code' => $this->categoryCode])
      ->joinWith(['props', 'props.propEntities'])
      ->one();;

    Yii::$app->cache->set($this->getCategoryCacheKey(), $data, 3600, $this->getTagDependency());

    return $data;
  }

  private function getCategoryCacheKey()
  {
    return self::CACHE_CATEGORY_PREFIX . $this->categoryCode;
  }

  private function getTagDependency()
  {
    return new TagDependency(['tags' => [self::CACHE_TAG]]);
  }
}