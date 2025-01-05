<?php
namespace mcms\pages\components\api;

use mcms\common\module\api\ApiResult;
use mcms\pages\models\Faq;
use mcms\pages\models\FaqCategory;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

class GetCachedVisibleFaqList extends ApiResult
{
  protected $cacheTags = [Faq::CACHE_KEY, FaqCategory::CACHE_KEY];
  protected $data;

  public function init($params = []){}

  public function getResult()
  {
    return Faq::getCachedVisibleFaqList($this->cacheTags);
  }

  public function invalidate()
  {
    TagDependency::invalidate(Yii::$app->getCache(), $this->cacheTags);
  }
}